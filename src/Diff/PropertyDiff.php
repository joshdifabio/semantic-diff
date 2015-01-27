<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class PropertyDiff extends AbstractDiff
{
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
        
        if ($base->name !== $head->name) {
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
        
        if ($base->default !== $head->default) {
            if ($head->isPrivate()) {
                return Status::INTERNAL_CHANGES;
            }
            
            return Status::API_CHANGES;
        }
        
        return Status::NO_CHANGES;
    }
}
