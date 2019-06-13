<?php

namespace Lazy\Db\Query;

/**
 * The query where like
 * clause criteria helper class.
 */
abstract class Criteria
{
    const CONTAINS    = 0;
    const ENDS_WITH   = 1;
    const STARTS_WITH = 2;
}
