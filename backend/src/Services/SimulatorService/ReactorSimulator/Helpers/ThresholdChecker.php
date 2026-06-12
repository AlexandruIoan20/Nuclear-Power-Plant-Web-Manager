<?php

require_once __DIR__ . '/../../../../Entities/ReactorSensor.php';

class ThresholdChecker {

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
