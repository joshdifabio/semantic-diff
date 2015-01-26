<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;
use PhpParser\Node\Param;
use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class ParameterDiff implements Diff
{
    private $base;
    private $head;
    
    public function __construct(Param $base = null, Param $head = null)
    {
        if (!$base && !$head) {
            throw new \LogicException('At least one param node must be provided');
        }
        
        $this->base = $base;
        $this->head = $head;
    }
    
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
            return Status::INTERNAL_CHANGES;
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
            return Status::INTERNAL_CHANGES;
        }
        
        if ($head->default) {
            if (!$base->default) {
                return Status::API_ADDITIONS;
            }
            
            return (new ExpressionDiff($base->default, $head->default))
                ->getStatus() ? Status::API_CHANGES : Status::NO_CHANGES;
        }
        
        return Status::NO_CHANGES;
    }
}
