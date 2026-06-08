<?php 

class ControlRod {
    private string $id;
    private string $reactorId;
    private string $rodGroup;
    private int $rodNumber;
    private string $material;
    private ?float $positionMm;
    private ?float $positionPercent;
    private bool $isInserted;
    private string $status;
    private ?string $lastInspection;
    private ?string $createdAt;

    public function __construct(
        string $reactorId,
        string $rodGroup,
        int $rodNumber,
        ?string $id = null,
        string $material = 'Ag-In-Cd',
        ?float $positionMm = null,
        ?float $positionPercent = null,
        bool $isInserted = true,
        string $status = 'OPERATIONAL',
        ?string $lastInspection = null,
        ?string $createdAt = null
    ) {
        $this->id = $id ?? bin2hex(random_bytes(16));
        $this->reactorId = $reactorId;
        $this->rodGroup = $rodGroup;
        $this->rodNumber = $rodNumber;
        $this->material = $material;
        $this->positionMm = $positionMm;
        $this->positionPercent = $positionPercent;
        $this->isInserted = $isInserted;
        $this->status = $status;
        $this->lastInspection = $lastInspection;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getReactorId(): string { return $this->reactorId; }
    public function setReactorId(string $reactorId): void { $this->reactorId = $reactorId; }

    public function getRodGroup(): string { return $this->rodGroup; }
    public function setRodGroup(string $rodGroup): void { $this->rodGroup = $rodGroup; }

    public function getRodNumber(): int { return $this->rodNumber; }
    public function setRodNumber(int $rodNumber): void { $this->rodNumber = $rodNumber; }

    public function getMaterial(): string { return $this->material; }
    public function setMaterial(string $material): void { $this->material = $material; }

    public function getPositionMm(): ?float { return $this->positionMm; }
    public function setPositionMm(?float $positionMm): void { $this->positionMm = $positionMm; }

    public function getPositionPercent(): ?float { return $this->positionPercent; }
    public function setPositionPercent(?float $positionPercent): void { $this->positionPercent = $positionPercent; }

    public function getIsInserted(): bool { return $this->isInserted; }
    public function setIsInserted(bool $isInserted): void { $this->isInserted = $isInserted; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getLastInspection(): ?string { return $this->lastInspection; }
    public function setLastInspection(?string $lastInspection): void { $this->lastInspection = $lastInspection; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }
}
