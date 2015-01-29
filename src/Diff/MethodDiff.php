<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class MethodDiff extends AbstractDiff
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
        
        if (
            $base->isStatic() != $head->isStatic()
            || !$base->isAbstract() && $head->isAbstract()
            || !$base->isFinal() && $head->isFinal()
            || $base->byRef != $head->byRef
        ) {
            if ($head->isPrivate()) {
                return Status::INTERNAL_CHANGES;
            }
            
            return Status::INCOMPATIBLE_API;
        }
        
        $status = $this->factory->createDiff($this->base->params ?: [], $this->head->params ?: [])
            ->getStatus();
        
        if ($status == Status::NO_CHANGES) {
            $status = $this->factory->createDiff($this->base->stmts ?: [], $this->head->stmts ?: [])
                ->getStatus();
        }
        
        return $status;
    }
}
