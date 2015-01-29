<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;
use SemanticDiff\Status;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
class NullDiff implements Diff
{
    public function getStatus()
    {
        return Status::NO_CHANGES;
    }
}
