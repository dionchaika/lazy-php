<?php

namespace Lazy\Db\Query;

/**
 * The criteria helper class.
 */
abstract class Criteria
{
    const CONTAINS                  = 0;
    const ENDS_WITH                 = 1;
    const STARTS_WITH               = 2;
    const IN_THE_RANGE              = 3;
    const NOT_IN_THE_RANGE          = 4;
    const ENDS_WITH_THE_RANGE       = 5;
    const STARTS_WITH_THE_RANGE     = 6;
    const NOT_ENDS_WITH_THE_RANGE   = 7;
    const NOT_STARTS_WITH_THE_RANGE = 8;
}
