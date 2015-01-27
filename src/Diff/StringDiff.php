<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class StringDiff extends AbstractDiff
{
    public function getStatus()
    {
        return ($this->head == $this->base ? Status::NO_CHANGES : Status::INTERNAL_CHANGES);
    }
}
