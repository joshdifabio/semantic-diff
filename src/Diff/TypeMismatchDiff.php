<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;
use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class TypeMismatchDiff implements Diff
{
    private $base;
    private $head;
    
    public function __construct($base, $head)
    {
        $this->base = $base;
        $this->head = $head;
    }
    
    public function getStatus()
    {
        return Status::INTERNAL_CHANGES;
    }
}
