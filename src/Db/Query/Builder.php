<?php

namespace Lazy\Db\Query;

use Lazy\Db\Query\Compilers\Compiler;

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
     * @var \Lazy\Db\Query\CompilerInterface
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

    /**
     * The builder constructor.
     *
     * @param  string  $table
     * @param  \Lazy\Db\Query\CompilerInterface|null  $compiler
     */
    public function __construct(string $table, ?CompilerInterface $compiler = null)
    {
        $this->table = $table;
        $this->compiler = $compiler ?? new Compiler;
    }
}
