<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class ParameterDiff extends AbstractDiff
{
    public function getStatus()
    {
        $base = $this->base;
        $head = $this->head;
        
        if (!$base) {
            if ($head->default) {
                return Status::API_ADDITIONS;
            }
            
            return Status::INCOMPATIBLE_API;
        }
        
        if (!$head) {
            return Status::API_CHANGES;
        }
        
        if (
            $base->default && !$head->default
            || $base->variadic != $head->variadic
            || $base->byRef != $head->byRef
            || (string)$head->type !== (string)$base->type
        ) {
            return Status::INCOMPATIBLE_API;
        }
        
        if ($base->name !== $head->name) {
            $status = Status::INTERNAL_CHANGES;
        } else {
            $status = Status::NO_CHANGES;
        }
        
        if ($head->default) {
            if (!$base->default) {
                return max($status, Status::API_ADDITIONS);
            }
            
            $status = max(
                $status,
                $this->factory->createDiff($base->default, $head->default)
                    ->getStatus() ? Status::API_CHANGES : Status::NO_CHANGES
            );
        }
        
        return $status;
    }
}
