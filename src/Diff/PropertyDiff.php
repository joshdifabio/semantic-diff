<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;
use SemanticDiff\Status;
use SemanticDiff\Property;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class PropertyDiff implements Diff
{
    private $base;
    private $head;
    
    public function __construct(Property $base = null, Property $head = null)
    {
        if (!$base && !$head) {
            throw new \LogicException('At least one property node must be provided');
        }
        
        $this->base = $base;
        $this->head = $head;
    }
    
    public function getStatus()
    {
        $base = $this->base;
        $head = $this->head;
        
        if (!$base) {
            if ($head->isPrivate()) {
                return Status::INTERNAL_CHANGES;
            }
            
            return Status::API_ADDITIONS;
        }
        
        if (!$head) {
            if ($base->isPrivate()) {
                return Status::INTERNAL_CHANGES;
            }
            
            return Status::INCOMPATIBLE_API;
        }
        
        if ($base->isPrivate() && !$head->isPrivate()) {
            return Status::INTERNAL_CHANGES;
        }
        
        if ($base->getName() !== $head->getName()) {
            return Status::INCOMPATIBLE_API;
        }
        
        if ($base->isPublic()) {
            if (!$head->isPublic()) {
                return Status::INCOMPATIBLE_API;
            }
        } elseif ($base->isProtected() && $head->isPrivate()) {
            return Status::INCOMPATIBLE_API;
        }
        
        if ($base->isStatic() != $head->isStatic()) {
            if ($head->isPrivate()) {
                return Status::INTERNAL_CHANGES;
            }
            
            return Status::INCOMPATIBLE_API;
        }
        
        if ($base->getDefaultValue() !== $head->getDefaultValue()) {
            return Status::INTERNAL_CHANGES;
        }
        
        return Status::NO_CHANGES;
    }
}
