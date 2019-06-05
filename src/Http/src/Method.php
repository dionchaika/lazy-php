<?php

namespace Lazy\Http;

/**
 * The HTTP request
 * method helper class.
 *
 * @see https://tools.ietf.org/html/rfc7230
 */
abstract class Method
{
    const GET     = 'GET';
    const PUT     = 'PUT';
    const HEAD    = 'HEAD';
    const POST    = 'POST';
    const PATCH   = 'PATCH';
    const TRACE   = 'TRACE';
    const DELETE  = 'DELETE';
    const OPTIONS = 'OPTIONS';
    const CONNECT = 'CONNECT';
}
