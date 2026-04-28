<?php

class TechnicalPlantData {
    private string $id;
    private string $powerPlantId;
    private ?int $numberOfReactors;
    private ?float $estimatedEfficiency;
    private ?float $operationalRiskLevel;
    private array $safetySystems;
    private array $reactorConfigurations = [];

    public function __construct(
        string $powerPlantId,
        ?string $id = null,
        ?int $numberOfReactors = null,
        ?float $estimatedEfficiency = null,
        ?float $operationalRiskLevel = null,
        array $safetySystems = []
    ) {
        $this->id = $id ?? bin2hex(random_bytes(16)); 
        $this->powerPlantId = $powerPlantId;
        $this->numberOfReactors = $numberOfReactors;
        $this->estimatedEfficiency = $estimatedEfficiency;
        $this->operationalRiskLevel = $operationalRiskLevel;
        $this->safetySystems = $safetySystems;
    }

    public function addReactorConfiguration($schema): void {
        foreach ($this->reactorConfigurations as $existing) {
            if ($existing->getId() === $schema->getId()) return;
        }
        $this->reactorConfigurations[] = $schema;
    }

    public function removeReactorConfiguration(string $schemaId): void {
        $this->reactorConfigurations = array_filter(
            $this->reactorConfigurations,
            fn($schema) => $schema->getId() !== $schemaId
        );
    }

    public function clearReactorConfiguration(): void { 
        $this->reactorConfigurations = [];
    }

    public function getReactorConfigurations(): array {
        return $this->reactorConfigurations;
    }

    public function getId(): string {
        return $this->id;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }

    public function getPowerPlantId(): string {
        return $this->powerPlantId;
    }

    public function setPowerPlantId(string $powerPlantId): void {
        $this->powerPlantId = $powerPlantId;
    }

    public function getNumberOfReactors(): ?int {
        return $this->numberOfReactors;
    }

    public function setNumberOfReactors(?int $numberOfReactors): void {
        $this->numberOfReactors = $numberOfReactors;
    }

    public function getEstimatedEfficiency(): ?float {
        return $this->estimatedEfficiency;
    }

    public function setEstimatedEfficiency(?float $estimatedEfficiency): void {
        $this->estimatedEfficiency = $estimatedEfficiency;
    }

    public function getOperationalRiskLevel(): ?float {
        return $this->operationalRiskLevel;
    }

    public function setOperationalRiskLevel(?float $operationalRiskLevel): void {
        $this->operationalRiskLevel = $operationalRiskLevel;
    }

    public function getSafetySystems(): array {
        return $this->safetySystems;
    }

    public function setSafetySystems(array $safetySystems): void {
        $this->safetySystems = $safetySystems;
    }
}