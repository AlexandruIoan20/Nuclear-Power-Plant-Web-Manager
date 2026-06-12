<?php

require_once __DIR__ . '/BaseDTO.php';

class GetPlantDTO extends BaseDTO {
    public function __construct(
        public readonly PlantDTO $details,
        public readonly ?BasicPlantDataDTO $basic,
        public readonly ?GeologicalPlantDataDTO $geological,
        public readonly ?TechnicalPlantDataDTO $technical,
    ) {}

    public static function fromServiceArray(array $data): self {
        return new self(
            details: PlantDTO::fromEntity($data['details']),
            basic: isset($data['basic']) ? BasicPlantDataDTO::fromEntity($data['basic']) : null,
            geological: isset($data['geological']) ? GeologicalPlantDataDTO::fromEntity($data['geological']) : null,
            technical:  isset($data['technical'])  ? TechnicalPlantDataDTO::fromEntity($data['technical'], $data['technical']->getReactorConfigurations()) : null,
        );
    }
}