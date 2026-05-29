<?php 

require_once __DIR__ . '/FeasibilityChecker.php'; 

abstract class AbstractFeasibilityChecker implements FeasibilityChecker { 
    private ?FeasibilityChecker $nextChecker = null; 

    public function setNext(FeasibilityChecker $feasibilityChecker): FeasibilityChecker { 
        $this->nextChecker = $feasibilityChecker; 
        return $feasibilityChecker; 
    } 

    public function check(array $plantData): array { 
        if($this->nextChecker) { 
            return $this->nextChecker->check($plantData); 
        }
        
        return [ 'status' => 'APPROVED', 'message' => 'Studiu de fezabilitate acceptat' ]; 
    }
}