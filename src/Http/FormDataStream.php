<?php

namespace Lazy\Http;

use Slim\Http\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * The PSR-7 multipart/form-data stream wrapper class.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 * @see https://tools.ietf.org/html/rfc2046#section-5.1
 */
class FormDataStream extends Stream implements StreamInterface
{
    /**
     * The multipart/form-data boundary charset.
     */
    const BOUNDARY_CHARSET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Generate a multipart/form-data boundary.
     *
     * @param  int  $length
     * @param  string  $prefix
     * @return string
     */
    protected function generateBoundary($length = 16, $prefix = '----TheLazyPHPFormBoundary')
    {
        $randMin = 0;
        $randMax = strlen(static::BOUNDARY_CHARSET) - 1;

        $boundary = '';

        for ($i = 0; $i < $length; $i++) {
            $boundary .= static::BOUNDARY_CHARSET[rand($randMin, $randMax)];
        }

        return $prefix.$boundary;
    }
}
