<?php 

require_once __DIR__ . '/../../Repositories/SensorRepository.php'; 
require_once __DIR__ . '/../../Repositories/MeasurementsRepository.php'; 
require_once __DIR__ . '/../../Repositories/ReactorRepository.php'; 

require_once __DIR__ . '/Observers/ObserverInterface.php'; 
require_once __DIR__ . '/ReactorSimulator/AbstractReactorSimulator.php'; 
require_once __DIR__ . '/ReactorSimulator/PwrSimulator.php'; 
require_once __DIR__ . '/ReactorSimulator/BwrSimulator.php'; 
require_once __DIR__ . '/ReactorSimulator/PhwrSimulator.php'; 
require_once __DIR__ . '/ReactorSimulator/FbrSimulator.php'; 
 
require_once __DIR__ . '/../../Entities/ReactorOperationalStatus.php'; 
require_once __DIR__ . '/../../Entities/ReactorType.php'; 

/**
 * Clasa de tip Service ce este orchestrator al scriptului de generare de valori pentru senzori 
 */
class SimulatorService { 
    private SensorRepository $sensorRepository; 
    private MeasurementsRepository $measurementsRepository; 
    private ReactorRepository $reactorRepository; 

    private array $observers = []; 
    private array $simulatorCache = []; 
    private bool $running = true; 
    private int $tickInterval; 


    public function __construct(SensorRepository $sensorRepository, MeasurementsRepository $measurementsRepository, ReactorRepository $reactorRepository, int $tickInterval = 1) { 
        $this->sensorRepository = $sensorRepository; 
        $this->measurementsRepository = $measurementsRepository; 
        $this->reactorRepository = $reactorRepository; 
        $this->tickInterval = $tickInterval;
    }

    /**
     * Functie pentru initializarea observerelor 
     * 
     * @param ObserverInterface $observer observerul ce trebuie adaugat
     *  
     * @return void
     */
    public function attachObserver(ObserverInterface $observer): void { 
        $this->observers[] = $observer; 
    }

    /**
     * Functie de setup a scriptului 
     * 
     * Pentru fiecare reactor care are un status activ se va apela continuu functia tick care genereaza masuratori
     * Atunci cand scriptul este inchis se va face un soft close, setand variabila running la false 
     * 
     * @return void 
     */
    public function run(): void { 
        if(function_exists('pcntl_signal')) { 
            pcntl_signal(SIGINT, fn() => $this->running = false); 
            pcntl_signal(SIGTERM, fn() => $this->running = false); 
        }

        while($this->running) { 
            $reactors = $this->reactorRepository->findAllFromApprovedPlants(); 
             
            foreach($reactors as $reactor) { 
                if(!$this->shouldSimulate($reactor)) continue; 

                $simulator = $this->getSimulator($reactor); 
                $simulator->tick($reactor->getId()); 
            }

            if(function_exists('pcntl_signal_dispatch')) pcntl_signal_dispatch(); 
            sleep($this->tickInterval); 
        }
    }

    /**
     * Functie ce verifica daca reactorul are status activ pentru a genera valori 
     * 
     * @param Reactor $reactor reactorul curent 
     * 
     * @return bool Trebuie sau nu sa genereze valori 
     */
    private function shouldSimulate(Reactor $reactor): bool { 
        return match ($reactor->getOperationalStatus()) { 
            ReactorOperationalStatus::SHUTDOWN, 
            ReactorOperationalStatus::COLD_STANDBY, 
            ReactorOperationalStatus::PLANNED_OUTAGE, 
            ReactorOperationalStatus::EMERGENCY_SHUTDOWN => false, 
            default => true 
        }; 
    }

    /**
     *  Functie ce preia strategia de simulare de valori in functie de tipul reactorului 
     * 
     * Se face initialzarea completa al simulatorului reactorului 
     * 
     * @param Reactor $reactor Reactorulu pentru care trebuie gasita strategia de simulare 
     * 
     * @return AbstractReactorSimulator Simulatorul initializat
     */
    private function getSimulator(Reactor $reactor): AbstractReactorSimulator { 
        $reactorId = $reactor->getId(); 

        if(isset($this->simulatorCache[$reactorId])) return $this->simulatorCache[$reactorId]; 

        $simulator = match ($reactor->getReactorType()) { 
            ReactorType::PWR => new PwrSimulator($this->sensorRepository, $this->measurementsRepository, $this->reactorRepository), 
            ReactorType::BWR => new BwrSimulator($this->sensorRepository, $this->measurementsRepository, $this->reactorRepository), 
            ReactorType::PHWR => new PhwrSimulator($this->sensorRepository, $this->measurementsRepository, $this->reactorRepository), 
            ReactorType::FBR => new FbrSimulator($this->sensorRepository, $this->measurementsRepository, $this->reactorRepository)
        }; 

        foreach($this->observers as $observer) $simulator->attachObserver($observer); 
        return $simulator; 
    }

}