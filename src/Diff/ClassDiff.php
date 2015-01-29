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
            || (!$this->base->isAbstract() && $this->head->isAbstract())
            || (!$this->base->isFinal() && $this->head->isFinal())
            || ($this->base->extends && (string)$this->base->extends !== (string)$this->head->extends)
            || count($this->base->implements) > count($this->head->implements)
            || array_diff($this->toStrings($this->base->implements), $this->toStrings($this->head->implements))
        ) {
            return Status::INCOMPATIBLE_API;
        }
        
        if (
            ($this->base->isAbstract() && !$this->head->isAbstract())
            || (!$this->base->extends && $this->head->extends)
            || ($this->base->isFinal() && !$this->head->isFinal())
            || array_diff($this->toStrings($this->head->implements), $this->toStrings($this->base->implements))
        ) {
            $status = Status::API_CHANGES;
        } else {
            $status = Status::NO_CHANGES;
        }
        
        $composite = $this->factory->createDiff($this->base->stmts ?: [], $this->head->stmts ?: []);
        
        return max($status, $composite->getStatus());
    }
    
    private function toStrings(array $stringables)
    {
        $strings = [];
        
        foreach ($stringables as $stringable) {
            $strings[] = (string)$stringable;
        }
        
        return $strings;
    }
}
