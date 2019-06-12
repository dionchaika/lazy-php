<?php

namespace Lazy\Db\Query;

class Builder
{
    /**
     * The builder table.
     *
     * @var string
     */
    protected $table;

    /**
     * The builder compiler.
     *
     * @var Lazy\Db\Query\CompilerInterface
     */
    protected $compiler;

    /**
     * The query parts.
     *
     * @var mixed[]
     */
    protected $parts = [

        'select'   => [],
        'distinct' => false,
        'from'     => null

    ];
}
