<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;
use SemanticDiff\Status;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String;
use PhpParser\Node\Expr\ConstFetch;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class ExpressionDiff implements Diff
{
    private $base;
    private $head;
    
    public function __construct(Expr $base = null, Expr $head = null)
    {
        if (!$base && !$head) {
            throw new \LogicException('At least one expr node must be provided');
        }
        
        $this->base = $base;
        $this->head = $head;
    }
    
    public function getStatus()
    {
        $base = $this->base;
        $head = $this->head;
        
        if (get_class($base) !== get_class($head)) {
            return Status::INTERNAL_CHANGES;
        }
        
        if ($base instanceof String) {
            if ($base->value !== $head->value) {
                return Status::INTERNAL_CHANGES;
            }
            
            return Status::NO_CHANGES;
        }
        
        if ($base instanceof ConstFetch) {
            if ((string)$base->name !== (string)$head->name) {
                return Status::INTERNAL_CHANGES;
            }
            
            return Status::NO_CHANGES;
        }
        
        echo sprintf("%s not fully implemented (expr compare)", __METHOD__);
        
        return Status::NO_CHANGES;
    }
}
