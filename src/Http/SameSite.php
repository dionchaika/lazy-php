<?php

namespace Lazy\Http;

/**
 * The HTTP cookie
 * SameSite attribute helper class.
 *
 * @see https://tools.ietf.org/html/rfc6265
 * @see https://tools.ietf.org/html/draft-west-first-party-cookies-07
 */
abstract class SameSite
{
    const LAX    = 'Lax';
    const STRICT = 'Strict';
}
