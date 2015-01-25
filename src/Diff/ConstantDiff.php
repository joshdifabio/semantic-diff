<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;
use PhpParser\Node\Const_;
use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class ConstantDiff implements Diff
{
    private $base;
    private $head;
    
    public function __construct(Const_ $base = null, Const_ $head = null)
    {
        if (!$base && !$head) {
            throw new \LogicException('At least one const node must be provided');
        }
        
        $this->base = $base;
        $this->head = $head;
    }
    
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
        
        return (new ExpressionDiff($base->value, $head->value))
            ->getStatus() ? Status::API_CHANGES : Status::NO_CHANGES;
    }
}
