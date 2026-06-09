<?php

class FeasibilityReportDTO implements JsonSerializable {
    public function __construct(
        public readonly ?string $reportId,
        public readonly string  $status,
        public readonly ?float  $nsviScore,
        public readonly array   $deficiencies,
        public readonly array   $errors,
        public readonly ?string $message,
        public readonly ?string $createdAt,
    ) {}

    public static function fromResult(array $result): self
    {
        return new self(
            reportId: null,
            status: $result['status'],
            nsviScore: $result['nsvi_score'] ?? null,
            deficiencies: $result['deficiencies'] ?? [],
            errors: $result['errors'] ?? [],
            message: $result['message'] ?? null,
            createdAt: null,
        );
    }

    public static function fromDatabase(array $row): self
    {
        return new self(
            reportId: $row['report_id'],
            status: $row['status'],
            nsviScore: $row['nsvi_score'],
            deficiencies: $row['deficiencies'] ?? [],
            errors: $row['errors'] ?? [],
            message: null,
            createdAt: $row['created_at'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'reportId' => $this->reportId,
            'status' => $this->status,
            'nsviScore' => $this->nsviScore,
            'deficiencies' => $this->deficiencies,
            'errors' => $this->errors,
            'createdAt' => $this->createdAt,
        ];
    }
}