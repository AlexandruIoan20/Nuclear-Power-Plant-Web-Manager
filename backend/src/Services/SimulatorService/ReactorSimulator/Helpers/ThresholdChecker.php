<?php

require_once __DIR__ . '/../../../../Entities/ReactorSensor.php';

class ThresholdChecker {

    public function check(float $value, ReactorSensor $sensor): ?string {
        if (
            ($sensor->getScramHigh() !== null && $value > $sensor->getScramHigh()) ||
            ($sensor->getScramLow() !== null && $value < $sensor->getScramLow())
        ) {
            return 'EMERGENCY';
        }

        if (
            ($sensor->getAlarmHigh() !== null && $value > $sensor->getAlarmHigh()) ||
            ($sensor->getAlarmLow() !== null && $value < $sensor->getAlarmLow())
        ) {
            return 'ALERT';
        }

        if (
            ($sensor->getAlertHigh() !== null && $value > $sensor->getAlertHigh()) ||
            ($sensor->getAlertLow() !== null && $value < $sensor->getAlertLow())
        ) {
            return 'WARNING';
        }

        return null;
    }

    public function checkAll(array $newValues, array $sensors): array {
        $violations = [];

        foreach ($sensors as $sensor) {
            $value = $newValues[$sensor->getId()] ?? null;
            if ($value === null) continue;

            $severity = $this->check($value, $sensor);
            if ($severity !== null) {
                $violations[$sensor->getId()] = [
                    'severity' => $severity,
                    'value' => $value,
                    'sensor' => $sensor,
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
