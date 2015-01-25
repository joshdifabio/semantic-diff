<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;
use PhpParser\Node\Stmt\ClassMethod;
use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class MethodDiff implements Diff
{
    private $base;
    private $head;
    private $parameterDiffs;
    
    public function __construct(ClassMethod $base = null, ClassMethod $head = null)
    {
        if (!$base && !$head) {
            throw new \LogicException('At least one method node must be provided');
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
        
        $status = Status::NO_CHANGES;
        
        foreach ($this->getParameterDiffs() as $diff) {
            if ($diff->getStatus() === Status::INCOMPATIBLE_API) {
                return Status::INCOMPATIBLE_API;
            }
            
            $status = max($status, $diff->getStatus());
        }
        
        return $status;
    }
    
    public function getParameterDiffs()
    {
        if (is_null($this->parameterDiffs)) {
            $params = [];

            foreach ([$this->base->params, $this->head->params] as $nodeSetKey => $paramSet) {
                foreach ($paramSet as $paramIndex => $param) {
                    $params[$paramIndex][$nodeSetKey] = $param;
                }
            }

            $diffs = [];
            
            foreach ($params as $beforeAndAfter) {
                $diffs[] = new ParameterDiff(
                    isset($beforeAndAfter[0]) ? $beforeAndAfter[0] : null,
                    isset($beforeAndAfter[1]) ? $beforeAndAfter[1] : null
                );
            }
            
            $this->parameterDiffs = $diffs;
        }
        
        return $this->parameterDiffs;
    }
}
