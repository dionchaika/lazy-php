<?php

namespace Lazy\Db\Query\Compilers;

use Lazy\Db\Query\Criteria;
use Lazy\Db\Query\CompilerInterface;

/**
 * The base SQL compiler class.
 */
class Compiler implements CompilerInterface
{
    /**
     * {@inheritDoc}
     */
    public function compileVal($val): string
    {
        if (null === $val) {
            return 'null';
        }

        if (true === $val) {
            return 'true';
        }

        if (false === $val) {
            return 'false';
        }

        if (is_numeric($val)) {
            return (string)$val;
        }

        return '\''.addcslashes($val, '\'').'\'';
    }

    /**
     * {@inheritDoc}
     */
    public function compileWildcard($val, ?int $criteria = null): string
    {
        if (null !== $criteria) {
            switch ($criteria) {
                case Criteria::CONTAINS:
                    $val = '%'.addcslashes($val, '%').'%';
                    break;
                case Criteria::ENDS_WITH:
                    $val = '%'.addcslashes($val, '%');
                    break;
                case Criteria::STARTS_WITH:
                    $val = addcslashes($val, '%').'%';
                    break;
                case Criteria::IN_THE_RANGE:
                    $val = '['.addcslashes($val, '[]^').']';
                    break;
                case Criteria::NOT_IN_THE_RANGE:
                    $val = '[^'.addcslashes($val, '[]^').']';
                    break;
                case Criteria::ENDS_WITH_THE_RANGE:
                    $val = '%['.addcslashes($val, '[]^').']';
                    break;
                case Criteria::STARTS_WITH_THE_RANGE:
                    $val = '['.addcslashes($val, '[]^').']%';
                    break;
                case Criteria::NOT_ENDS_WITH_THE_RANGE:
                    $val = '%[^'.addcslashes($val, '[]^').']';
                    break;
                case Criteria::NOT_STARTS_WITH_THE_RANGE:
                    $val = '[^'.addcslashes($val, '[]^').']%';
                    break;
            }
        }

        return $this->compileVal($val);
    }
}
