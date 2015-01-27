<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class ClassDiff extends AbstractDiff
{
    public function getStatus()
    {
        if (!$this->base) {
            return Status::API_ADDITIONS;
        }
        
        if (
            !$this->head
            || $this->base->extends !== $this->head->extends
            || count($this->base->implements) != count($this->head->implements)
            || array_diff($this->base->implements, $this->head->implements)
            || !$this->base->isAbstract() && $this->head->isAbstract()
            || !$this->base->isFinal() && $this->head->isFinal()
        ) {
            return Status::INCOMPATIBLE_API;
        }
        
        if (
            array_diff($this->head->implements, $this->base->implements)
            || $this->base->isAbstract() && !$this->head->isAbstract()
            || $this->base->isFinal() && !$this->head->isFinal()
        ) {
            $status = Status::API_CHANGES;
        } else {
            $status = Status::NO_CHANGES;
        }
        
        $composite = $this->factory->createDiff($this->base->stmts, $this->head->stmts);
        
        return max($status, $composite->getStatus());
    }
}
