<?php

require_once __DIR__ . '/../../../../Entities/ReactorSensor.php';

/**
 * Clasa helper responsabila cu verificarea depasirii pragurilor de siguranta ale senzorilor.
 */
class ThresholdChecker {
    /**
     * Verifica o valoare individuala in raport cu pragurile unui senzor.
     *
     * Determina daca valoarea depaseste pragurile de tip:
     * - SCRAM (nivel de urgenta maxima)
     * - ALARM (nivel critic)
     * - ALERT (nivel de avertizare)
     * 
     * @param float $value valoarea masurata a senzorului
     * @param ReactorSensor $sensor senzorul care contine pragurile de siguranta
     * @return array|null informatii despre incalcare sau null daca totul este in limite
     */
    public function check(float $value, ReactorSensor $sensor): ?array {
        if (
            ($sensor->getScramHigh() !== null && $value > $sensor->getScramHigh()) ||
            ($sensor->getScramLow() !== null && $value < $sensor->getScramLow())
        ) {
            $threshold = $value > $sensor->getScramHigh() ? $sensor->getScramHigh() : $sensor->getScramLow();
            return [
                'severity' => 'EMERGENCY',
                'threshold' => $threshold,
            ];
        }

        if (
            ($sensor->getAlarmHigh() !== null && $value > $sensor->getAlarmHigh()) ||
            ($sensor->getAlarmLow() !== null && $value < $sensor->getAlarmLow())
        ) {
            $threshold = $value > $sensor->getAlarmHigh() ? $sensor->getAlarmHigh() : $sensor->getAlarmLow();
            return [
                'severity' => 'ALERT',
                'threshold' => $threshold,
            ];
        }

        if (
            ($sensor->getAlertHigh() !== null && $value > $sensor->getAlertHigh()) ||
            ($sensor->getAlertLow() !== null && $value < $sensor->getAlertLow())
        ) {
            $threshold = $value > $sensor->getAlertHigh() ? $sensor->getAlertHigh() : $sensor->getAlertLow();
            return [
                'severity' => 'WARNING',
                'threshold' => $threshold,
            ];
        }

        return null;
    }

    public function checkAll(array $newValues, array $sensors): array {
        $violations = [];

        foreach ($sensors as $sensor) {
            $value = $newValues[$sensor->getId()] ?? null;
            if ($value === null) continue;

            $result = $this->check($value, $sensor);
            if ($result !== null) {
                $violations[$sensor->getId()] = [
                    'severity' => $result['severity'],
                    'value' => $value,
                    'sensor' => $sensor,
                    'threshold' => $result['threshold'],
                ];
            }
        }

        return $violations;
    }

    public function hasEmergency(array $violations): bool {
        foreach ($violations as $v) {
            if ($v['severity'] === 'EMERGENCY') return true;
        }
        return false;
    }
}
