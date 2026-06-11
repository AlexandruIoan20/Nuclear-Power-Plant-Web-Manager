<?php

require_once __DIR__ . '/../Generators/SensorGeneratorFactory.php';
require_once __DIR__ . '/../../../Repositories/SensorRepository.php';
require_once __DIR__ . '/../../../Repositories/MeasurementsRepository.php';
require_once __DIR__ . '/../../../Repositories/ReactorRepository.php';
require_once __DIR__ . '/../../../Entities/ReactorOperationalStatus.php';
require_once __DIR__ . '/Helpers/ThresholdChecker.php';
require_once __DIR__ . '/Helpers/MeasurementBuilder.php';
require_once __DIR__ . '/Helpers/ReactorWearCalculator.php';
require_once __DIR__ . '/../Observers/SubjectTrait.php';
require_once __DIR__ . '/../Observers/ViolationEvent.php';

abstract class AbstractReactorSimulator {
    use SubjectTrait;

    protected SensorRepository $sensorRepository;
    protected MeasurementsRepository $measurementsRepository;
    protected ReactorRepository $reactorRepository;
    protected SensorGeneratorFactory $generatorFactory;

    protected ThresholdChecker $thresholdChecker;
    protected MeasurementBuilder $measurementBuilder;
    protected ReactorWearCalculator $wearCalculator;

    public function __construct(SensorRepository $sensorRepository, MeasurementsRepository $measurementsRepository, ReactorRepository $reactorRepository) {
        $this->sensorRepository = $sensorRepository;
        $this->measurementsRepository = $measurementsRepository;
        $this->reactorRepository = $reactorRepository;
        $this->generatorFactory = new SensorGeneratorFactory();

        $this->thresholdChecker = new ThresholdChecker();
        $this->measurementBuilder = new MeasurementBuilder();
        $this->wearCalculator = new ReactorWearCalculator();
    }

    final public function tick(string $reactorId): void {
        $reactor = $this->reactorRepository->findById($reactorId);
        if (!$reactor) return;

        $sensors = $this->sensorRepository->findByReactorId($reactorId);
        if (empty($sensors)) return;

        $newValues = $this->generateValues($sensors);
        $this->applyPhysicalCorrelation($newValues, $sensors, $reactor);

        $violations = $this->thresholdChecker->checkAll($newValues, $sensors);

        foreach ($violations as $violation) {
            $event = new ViolationEvent(
                $violation['severity'],
                $violation['value'],
                $violation['sensor'],
                $reactorId
            );
            $this->notifyObservers($event);
        }

        $measurement = $this->measurementBuilder->build($newValues, $sensors, $reactor);

        if ($this->thresholdChecker->hasEmergency($violations)) {
            $reactor->setOperationalStatus(ReactorOperationalStatus::EMERGENCY_SHUTDOWN);
            $this->reactorRepository->update($reactor);
        }

        $this->persistValues($newValues, $sensors);
        $this->measurementsRepository->save($measurement);
        $this->wearCalculator->applyWear($reactor, $measurement->getPowerPercent() ?? 0, $this->reactorRepository);

        $ts = $measurement->getTimestamp() ?? date('Y-m-d H:i:s');
        $power = $measurement->getPowerPercent() !== null ? sprintf('%.1f', $measurement->getPowerPercent()) : 'N/A';
        $tOut = $measurement->getTempCoolantOut() !== null ? sprintf('%.1f', $measurement->getTempCoolantOut()) : 'N/A';
        $pres = $measurement->getPressure() !== null ? sprintf('%.2f', $measurement->getPressure()) : 'N/A';
        $flux = $measurement->getNeutronFlux() !== null ? sprintf('%.1e', $measurement->getNeutronFlux()) : 'N/A';

        echo "[" . $ts . "] " . $reactor->getReactorCode()
            . " | P=" . $power . "% | Tout=" . $tOut . "°C | P=" . $pres . " MPa | Φ=" . $flux
            . " | wear=" . sprintf('%.4f', $reactor->getWearIndex())
            . PHP_EOL;
    }

    abstract protected function applyPhysicalCorrelation(array &$newValues, array $sensors, Reactor $reactor): void;

    private function generateValues(array $sensors): array {
        $newValues = [];

        foreach ($sensors as $sensor) {
            $currentValue = $sensor->getCurrentValue();

            if ($currentValue === null) {
                $min = $sensor->getNormalMin() ?? 0;
                $max = $sensor->getNormalMax() ?? 100;
                $currentValue = ($min + $max) / 2;
            }

            $strategy = $this->generatorFactory->getStrategy($sensor->getSensorType());
            $newValues[$sensor->getId()] = $strategy->generate($currentValue, $sensor);
        }

        return $newValues;
    }

    private function persistValues(array $newValues, array $sensors): void {
        foreach ($sensors as $sensor) {
            $value = $newValues[$sensor->getId()] ?? null;
            if ($value !== null) {
                $this->sensorRepository->updateCurrentValue($sensor->getId(), $value);
            }
        }
    }

    final protected function buildIndex(array $newValues, array $sensors): array {
        $index = [];
        foreach ($sensors as $sensor) {
            $index[$sensor->getSensorCode()] = [
                'sensor' => $sensor,
                'value'  => $newValues[$sensor->getId()] ?? $sensor->getCurrentValue() ?? 0,
            ];
            $field = $sensor->getMeasurementField();
            if ($field !== null) {
                $index[$field] =& $index[$sensor->getSensorCode()];
            }
        }
        return $index;
    }
}
