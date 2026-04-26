<?php 

class TechnicalPlantRepository { 
    private PDO $pdo; 

    public function __construct(PDO $pdo) { 
        $this->pdo = $pdo; 
    }
}