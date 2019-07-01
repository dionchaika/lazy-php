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
     * @param  \Psr\Http\Message\StreamInterface|string|resource|null  $body
     * @param  string  $protocolVersion
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct($code = StatusCode::OK,
                                $reasonPhrase = '',
                                $headers = [],
                                $body = null,
                                $protocolVersion = '1.1')
    {
        $this->statusCode = $this->filterStatusCode($code);

        if ($reasonPhrase) {
            $this->reasonPhrase = $reasonPhrase;
        } else {
            $this->setReasonPhraseFromStatusCode($this->statusCode);
        }

        $this->setHeaders($headers);

        if (! $body) {
            $this->body = new Stream;
        } else if (is_string($body) || is_resource($body)) {
            $this->body = new Stream($body);
        }

        $this->protocolVersion = $protocolVersion;
    }

    /**
     * Create a new response from string.
     *
     * @param  string  $response
     * @return self
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function fromString($response)
    {
        return parse_response($response);
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

        if ($reasonPhrase) {
            $new->reasonPhrase = $reasonPhrase;
        } else {
            $new->setReasonPhraseFromStatusCode($new->statusCode);
        }

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
        return $this->withAddedHeader('Set-Cookie', (string) $cookie);
    }

    /**
     * Check is the response an informational response.
     *
     * @return bool
     */
    public function isInformational()
    {
        return 100 <= $this->statusCode
            && 199 >= $this->statusCode;
    }

    /**
     * Check is the response a successful response.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return 200 <= $this->statusCode
            && 299 >= $this->statusCode;
    }

    /**
     * Check is the response a redirect response.
     *
     * @return bool
     */
    public function isRedirect()
    {
        return 300 <= $this->statusCode
            && 399 >= $this->statusCode
            && $this->hasHeader('Location');
    }

    /**
     * Check is the response a client error response.
     *
     * @return bool
     */
    public function isClientError()
    {
        return 400 <= $this->statusCode
            && 499 >= $this->statusCode;
    }

    /**
     * Check is the response a server error response.
     *
     * @return bool
     */
    public function isServerError()
    {
        return 500 <= $this->statusCode
            && 599 >= $this->statusCode;
    }

    /**
     * Stringify the response.
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
     * Set the response reason phrase from the status code.
     *
     * @param  int  $statusCode
     * @return void
     */
    protected function setReasonPhraseFromStatusCode($statusCode)
    {
        $this->reasonPhrase = isset(static::REASON_PHRASES[$statusCode])
            ? static::REASON_PHRASES[$statusCode] : '';
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
