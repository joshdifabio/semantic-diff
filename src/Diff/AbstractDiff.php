<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
abstract class AbstractDiff implements Diff
{
    protected $factory;
    protected $base;
    protected $head;
    
    public function __construct(Factory $factory, $base = null, $head = null)
    {
        if (!$base && !$head) {
            throw new \LogicException('At least one value must be provided');
        }
        
        $this->factory = $factory;
        $this->base = $base;
        $this->head = $head;
    }
}
