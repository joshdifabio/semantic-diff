<?php
namespace SemanticDiff\Diff;

use PhpParser\Node;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class Factory
{
    public function createDiff($base = null, $head = null)
    {
        if (is_null($base)) {
            if (is_null($head)) {
                return new NullDiff;
            }
            
            $type = $this->getType($head);
        } else {
            if (!is_null($head) && $this->getType($base) !== $this->getType($head)) {
                return new TypeMismatchDiff($base, $head);
            }
            
            $type = $this->getType($base);
        }
        
        switch ($type) {
            case 'Stmt_Class':
                return new ClassDiff($this, $base, $head);
                
            case 'Stmt_ClassMethod':
                return new MethodDiff($this, $base, $head);
                
            case 'Const':
                return new ConstantDiff($this, $base, $head);
                
            case 'Param':
                return new ParameterDiff($this, $base, $head);
                
            case 'Stmt_Property':
                return new PropertyDiff($this, $base, $head);
                
            case 'array':
                return new CompositeDiff($this, $base ?: [], $head ?: []);
                
            case 'scalar':
                return new StringDiff($this, (string)$base, (string)$head);
                
            default:
                return new GenericDiff($this, $base, $head);
        }
    }
    
    private function getType($value)
    {
        if ($value instanceof Node) {
            return $value->getType();
        }
        
        if (is_array($value)) {
            return 'array';
        }
        
        if (is_scalar($value)) {
            return 'scalar';
        }
        
        return null;
    }
}
