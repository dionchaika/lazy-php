<?php

namespace Lazy\Http;

use Throwable;
use Lazy\Cookie\Cookie;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * The PSR-7 HTTP response message implementation class.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 * @see https://tools.ietf.org/html/rfc7230
 */
class Response extends Message implements ResponseInterface
{
    /**
     * The reason phrases.
     */
    const REASON_PHRASES = [

        //
        // Informational
        //
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',

        //
        // Successful
        //
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        //
        // Redirection
        //
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        //
        // Client Error
        //
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        //
        // Server Error
        //
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'

    ];

    /**
     * The response status code.
     *
     * @var int
     */
    protected $statusCode = StatusCode::OK;

    /**
     * The response reason phrase.
     *
     * @var string
     */
    protected $reasonPhrase = self::REASON_PHRASES[StatusCode::OK];

    /**
     * The response constructor.
     *
     * @param  int  $code
     * @param  string  $reasonPhrase
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($code = StatusCode::OK, $reasonPhrase = '', $headers = [], $protocolVersion = '1.1')
    {
        $this->statusCode = $this->filterStatusCode($code);

        if ('' === $reasonPhrase && isset(static::REASON_PHRASES[$this->statusCode])) {
            $reasonPhrase = static::REASON_PHRASES[$this->statusCode];
        }

        $this->reasonPhrase = $reasonPhrase;

        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        $this->protocolVersion = $protocolVersion;
    }

    /**
     * Get the response status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return an instance
     * with the specified response status.
     *
     * @param  int  $code
     * @param  string  $reasonPhrase
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $new = clone $this;

        $new->statusCode = $new->filterStatusCode($code);

        if ('' === $reasonPhrase && isset(static::REASON_PHRASES[$this->statusCode])) {
            $reasonPhrase = static::REASON_PHRASES[$this->statusCode];
        }

        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    /**
     * Get the response reason phrase.
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * Return an instance
     * with the specified response cookie.
     *
     * @param  \Lazy\Cookie\Cookie  $cookie
     * @return static
     */
    public function withCookie(Cookie $cookie)
    {
        return $this->withAddedHeader('Set-Cookie', $cookie);
    }

    /**
     * Get the string
     * representation of the response.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return to_string($this);
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }

    /**
     * Filter a response status code.
     *
     * @param  int  $code
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function filterStatusCode($code)
    {
        if (306 === $code) {
            throw new InvalidArgumentException('Invalid status code! Status code 306 is unused.');
        }

        if (100 > $code || 599 < $code) {
            throw new InvalidArgumentException('Invalid status code! Status code must be between 100 and 599.');
        }

        return $code;
    }
}
