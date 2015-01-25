<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;
use SemanticDiff\Status;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassConst as ClassConstNode;
use PhpParser\Node\Const_ as ConstNode;
use PhpParser\Node\Stmt\Property as PropertyNode;
use PhpParser\Node\Stmt\ClassMethod as ClassMethodNode;
use SemanticDiff\Property;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class ClassDiff implements Diff
{
    private $base;
    private $head;
    private $constantDiffs;
    private $propertyDiffs;
    private $methodDiffs;
    
    public function __construct(ClassNode $base = null, ClassNode $head = null)
    {
        if (!$base && !$head) {
            throw new \LogicException('At least one class node must be provided');
        }
        
        $this->base = $base;
        $this->head = $head;
    }
    
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
        
        foreach ($this->getAllDiffs() as $diff) {
            if ($diff->getStatus() === Status::INCOMPATIBLE_API) {
                return Status::INCOMPATIBLE_API;
            }
            
            $status = max($status, $diff->getStatus());
        }
        
        return $status;
    }
    
    /**
     * @return \Iterator
     */
    public function getAllDiffs()
    {
        foreach ($this->getConstantDiffs() as $diff) {
            yield $diff;
        }
        
        foreach ($this->getPropertyDiffs() as $diff) {
            yield $diff;
        }
        
        foreach ($this->getMethodDiffs() as $diff) {
            yield $diff;
        }
    }
    
    /**
     * @return array
     */
    public function getConstantDiffs()
    {
        if (is_null($this->constantDiffs)) {
            $this->constantDiffs = $this->createDiffs(
                'Stmt_ClassConst',
                function (ClassConstNode $classConstNode) {
                    foreach ($classConstNode->consts as $constNode) {
                        yield $constNode->name => $constNode;
                    }
                },
                function (ConstNode $base = null, ConstNode $workingCopy = null) {
                    return new ConstantDiff($base, $workingCopy);
                }
            );
        }
        
        return $this->constantDiffs;
    }
    
    /**
     * @return array
     */
    public function getPropertyDiffs()
    {
        if (is_null($this->propertyDiffs)) {
            $this->propertyDiffs = $this->createDiffs(
                'Stmt_Property',
                function (PropertyNode $propertyNode) {
                    foreach ($propertyNode->props as $propertyPropertyNode) {
                        yield $propertyPropertyNode->name => new Property(
                            $propertyNode->type,
                            $propertyPropertyNode->name,
                            $propertyPropertyNode->default
                        );
                    }
                },
                function (Property $base = null, Property $workingCopy = null) {
                    return new PropertyDiff($base, $workingCopy);
                }
            );
        }
        
        return $this->propertyDiffs;
    }
    
    /**
     * @return array
     */
    public function getMethodDiffs()
    {
        if (is_null($this->methodDiffs)) {
            $this->methodDiffs = $this->createDiffs(
                'Stmt_ClassMethod',
                function (ClassMethodNode $node) {
                    yield $node->name => $node;
                },
                function (ClassMethodNode $base = null, ClassMethodNode $workingCopy = null) {
                    return new MethodDiff($base, $workingCopy);
                }
            );
        }
        
        return $this->methodDiffs;
    }
    
    private function createDiffs($type, callable $nameValueGenerator, callable $diffFactory)
    {
        $values = [];
        
        foreach ([$this->base->stmts, $this->head->stmts] as $nodeSetKey => $nodeSet) {
            foreach ($nodeSet as $node) {
                if ($type !== $node->getType()) {
                    continue;
                }
                
                foreach ($nameValueGenerator($node) as $name => $value) {
                    $values[$name][$nodeSetKey] = $value;
                }
            }
        }
        
        $diffs = [];
        
        foreach ($values as $beforeAndAfter) {
            $diffs[] = $diffFactory(
                isset($beforeAndAfter[0]) ? $beforeAndAfter[0] : null,
                isset($beforeAndAfter[1]) ? $beforeAndAfter[1] : null
            );
        }
        
        return $diffs;
    }
}
