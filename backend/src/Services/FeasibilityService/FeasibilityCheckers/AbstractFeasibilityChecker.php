<?php 

require_once __DIR__ . '/FeasibilityChecker.php'; 

/** 
 *  Clasa abstracta pentru reprezentarea unui Checker din Chain of Responsibility 
 * 
 *  Importanta patternului: Dupa ce datele au fost introduse, trec prin mai multe stagii ale verificarii. 
 *  Fiecare stagiu verifica anumite parti ale datelor si trimite in caz de succes urmatorului stagiu (checker)
 */
abstract class AbstractFeasibilityChecker implements FeasibilityChecker { 
    /**
     * @var ?FeasibilityChecker $nextChecker fiecare checker din lant trebuie sa stie cine este urmatorul.
     * in afara de ultimul checker ce va avea $nextChecker = null
     */
    private ?FeasibilityChecker $nextChecker = null; 

    /**
     *  Functie ajutatoare pentru a scrie logica de legare a checkerelor in lant 
     * 
     *  @param FeasibilityChecker $feasibilityChecker urmatorul checker 
     *  @return FeasibilityChecker 
     */
    public function setNext(FeasibilityChecker $feasibilityChecker): FeasibilityChecker { 
        $this->nextChecker = $feasibilityChecker; 
        return $feasibilityChecker; 
    } 

    /** 
     * Functie ce face verificarea recursiva a checkerelor 
     * 
     * @param array $plantData datele centralei pentru care se face raportul 
     * 
     * @return array Apelarea verificarii urmatorului checker | Mesajul de confirmare la finalul studiului 
     */
    public function check(array $plantData): array { 
        if($this->nextChecker) { 
            return $this->nextChecker->check($plantData); 
        }
        
        return [ 'status' => 'APPROVED', 'message' => 'Studiu de fezabilitate acceptat' ]; 
    }
}