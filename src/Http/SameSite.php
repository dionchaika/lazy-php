<?php

namespace Lazy\Http;

/**
 * The HTTP cookie
 * SameSite attribute helper class.
 *
 * @see https://tools.ietf.org/html/rfc6265
 */
abstract class SameSite
{
    const LAX    = 'Lax';
    const STRICT = 'Strict';
}
