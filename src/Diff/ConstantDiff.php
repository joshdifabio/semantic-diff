<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class ConstantDiff extends AbstractDiff
{
    public function getStatus()
    {
        $base = $this->base;
        $head = $this->head;
        
        if (!$base) {
            return Status::API_ADDITIONS;
        }
        
        if (!$head || $base->name !== $head->name) {
            return Status::INCOMPATIBLE_API;
        }
        
        return $this->factory->createDiff($base->value, $head->value)
            ->getStatus() ? Status::API_CHANGES : Status::NO_CHANGES;
    }
}
