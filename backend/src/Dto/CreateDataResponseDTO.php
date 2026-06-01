<?php 

class CreateDataResponseDTO { 
    public readonly string $dataId; 

    public function __construct(string $dataId) { 
        $this->dataId = $dataId; 
    }
}