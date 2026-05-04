<?php

class ReactorSchema {
    public function __construct(
        private string $id,
        private ReactorType $type,
        private CoolingType $cooling
    ) {}

    public function getId(): string { return $this->id; }
    public function getType(): ReactorType { return $this->type; }
    public function getCooling(): CoolingType { return $this->cooling; }
}