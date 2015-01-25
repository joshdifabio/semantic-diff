<?php
namespace SemanticDiff;

/**
 * @author Joshua Di Fabio <joshdifabio@gmail.com>
 */
abstract class Status
{
    const NO_CHANGES        = 0;
    const API_ADDITIONS     = 1;
    const INTERNAL_CHANGES  = 2;
    const API_CHANGES       = 3;
    const INCOMPATIBLE_API  = 4;
}
