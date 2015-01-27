<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class GenericDiff extends AbstractDiff
{
    private $status;
    
    public function getStatus()
    {
        if (is_null($this->status)) {
            $status = Status::NO_CHANGES;

            $anyNode = $this->base ?: $this->head;
            $subNodeNames = $anyNode->getSubNodeNames();
            foreach ($subNodeNames as $subNodeName) {
                $baseValue = $this->base ? $this->base->$subNodeName : null;
                $headValue = $this->head ? $this->head->$subNodeName : null;
                
                if (is_array($baseValue) || is_array($headValue)) {
                    $_status = $this->factory->createDiff($baseValue, $headValue)
                        ->getStatus();
                } elseif (is_object($baseValue) || is_object($headValue)) {
                    $_status = $this->factory->createDiff($baseValue, $headValue)
                        ->getStatus();
                } else {
                    $_status = ($baseValue === $headValue ? Status::NO_CHANGES : Status::INTERNAL_CHANGES);
                }
                
                $status = max($status, $_status);
            }
            
            $this->status = $status;
        }
        
        return $this->status;
    }
}
