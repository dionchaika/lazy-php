<?php

namespace Lazy\Http;

use Throwable;
use RuntimeException;
use SimpleXMLElement;
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
        StatusCode::CONTINUE                        => 'Continue',
        StatusCode::SWITCHING_PROTOCOLS             => 'Switching Protocols',
        StatusCode::PROCESSING                      => 'Processing',
        StatusCode::EARLY_HINTS                     => 'Early Hints',

        //
        // Successful
        //
        StatusCode::OK                              => 'OK',
        StatusCode::CREATED                         => 'Created',
        StatusCode::ACCEPTED                        => 'Accepted',
        StatusCode::NON_AUTHORITATIVE_INFORMATION   => 'Non-Authoritative Information',
        StatusCode::NO_CONTENT                      => 'No Content',
        StatusCode::RESET_CONTENT                   => 'Reset Content',
        StatusCode::PARTIAL_CONTENT                 => 'Partial Content',
        StatusCode::MULTI_STATUS                    => 'Multi-Status',
        StatusCode::ALREADY_REPORTED                => 'Already Reported',
        StatusCode::IM_USED                         => 'IM Used',

        //
        // Redirection
        //
        StatusCode::MULTIPLE_CHOICES                => 'Multiple Choices',
        StatusCode::MOVED_PERMANENTLY               => 'Moved Permanently',
        StatusCode::FOUND                           => 'Found',
        StatusCode::SEE_OTHER                       => 'See Other',
        StatusCode::NOT_MODIFIED                    => 'Not Modified',
        StatusCode::USE_PROXY                       => 'Use Proxy',
        StatusCode::TEMPORARY_REDIRECT              => 'Temporary Redirect',
        StatusCode::PERMANENT_REDIRECT              => 'Permanent Redirect',

        //
        // Client Error
        //
        StatusCode::BAD_REQUEST                     => 'Bad Request',
        StatusCode::UNAUTHORIZED                    => 'Unauthorized',
        StatusCode::PAYMENT_REQUIRED                => 'Payment Required',
        StatusCode::FORBIDDEN                       => 'Forbidden',
        StatusCode::NOT_FOUND                       => 'Not Found',
        StatusCode::METHOD_NOT_ALLOWED              => 'Method Not Allowed',
        StatusCode::NOT_ACCEPTABLE                  => 'Not Acceptable',
        StatusCode::PROXY_AUTHENTICATION_REQUIRED   => 'Proxy Authentication Required',
        StatusCode::REQUEST_TIMEOUT                 => 'Request Timeout',
        StatusCode::CONFLICT                        => 'Conflict',
        StatusCode::GONE                            => 'Gone',
        StatusCode::LENGTH_REQUIRED                 => 'Length Required',
        StatusCode::PRECONDITION_FAILED             => 'Precondition Failed',
        StatusCode::PAYLOAD_TOO_LARGE               => 'Payload Too Large',
        StatusCode::URI_TOO_LONG                    => 'URI Too Long',
        StatusCode::UNSUPPORTED_MEDIA_TYPE          => 'Unsupported Media Type',
        StatusCode::RANGE_NOT_SATISFIABLE           => 'Range Not Satisfiable',
        StatusCode::EXPECTATION_FAILED              => 'Expectation Failed',
        StatusCode::MISDIRECTED_REQUEST             => 'Misdirected Request',
        StatusCode::UNPROCESSABLE_ENTITY            => 'Unprocessable Entity',
        StatusCode::LOCKED                          => 'Locked',
        StatusCode::FAILED_DEPENDENCY               => 'Failed Dependency',
        StatusCode::TOO_EARLY                       => 'Too Early',
        StatusCode::UPGRADE_REQUIRED                => 'Upgrade Required',
        StatusCode::PRECONDITION_REQUIRED           => 'Precondition Required',
        StatusCode::TOO_MANY_REQUESTS               => 'Too Many Requests',
        StatusCode::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        StatusCode::UNAVAILABLE_FOR_LEGAL_REASONS   => 'Unavailable For Legal Reasons',

        //
        // Server Error
        //
        StatusCode::INTERNAL_SERVER_ERROR           => 'Internal Server Error',
        StatusCode::NOT_IMPLEMENTED                 => 'Not Implemented',
        StatusCode::BAD_GATEWAY                     => 'Bad Gateway',
        StatusCode::SERVICE_UNAVAILABLE             => 'Service Unavailable',
        StatusCode::GATEWAY_TIMEOUT                 => 'Gateway Timeout',
        StatusCode::HTTP_VERSION_NOT_SUPPORTED      => 'HTTP Version Not Supported',
        StatusCode::VARIANT_ALSO_NEGOTIATES         => 'Variant Also Negotiates',
        StatusCode::INSUFFICIENT_STORAGE            => 'Insufficient Storage',
        StatusCode::LOOP_DETECTED                   => 'Loop Detected',
        StatusCode::NOT_EXTENDED                    => 'Not Extended',
        StatusCode::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required'

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
     * @param  \Psr\Http\Message\StreamInterface|callable|resource|object|array|int|float|bool|string|null  $body
     * @param  string  $protocolVersion
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($code = StatusCode::OK,
                                $reasonPhrase = '',
                                array $headers = [],
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

        $this->body = create_stream($body);
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
     * @param  \Lazy\Http\Cookie  $cookie
     * @return static
     */
    public function withCookie(Cookie $cookie)
    {
        return $this->withAddedHeader('Set-Cookie', (string) $cookie);
    }

    /**
     * Return a redirect response instance.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $location
     * @param  int  $code
     * @param  string  $reasonPhrase
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withRedirect($location, $code = 302, $reasonPhrase = '')
    {
        $new = clone $this;

        return $new
            ->withStatus($code, $reasonPhrase)
            ->withHeader('Location', (string) $location);
    }

    /**
     * Return an instance
     * with the HTML response body.
     *
     * @param  mixed  $html
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withHtml($html)
    {
        $new = (clone $this)->withBody(create_stream($html));

        return $new
            ->withHeader('Content-Type', 'text/html')
            ->withHeader('Content-Length', (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the plain text response body.
     *
     * @param  mixed  $plainText
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withPlainText($plainText)
    {
        $new = (clone $this)->withBody(create_stream($plainText));

        return $new
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Length', (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the JSON response body.
     *
     * @param  mixed  $data
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withJson($data, $opts = 0, $depth = 512)
    {
        $new = (clone $this)->withBody(
            create_stream(json_encode($data, $opts, $depth))
        );

        return $new
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the XML response body.
     *
     * @param  \SimpleXMLElement  $xml
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withXml(SimpleXMLElement $xml)
    {
        $new = (clone $this)->withBody(
            create_stream($xml->asXML())
        );

        return $new
            ->withHeader('Content-Type', 'text/xml')
            ->withHeader('Content-Length', (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the file response body.
     *
     * @param  string  $filename
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withFile($filename)
    {
        $new = (clone $this)->withBody(
            create_stream(fopen($filename, 'r+'))
        );

        return $new
            ->withHeader('Content-Type', mime_content_type($filename))
            ->withHeader('Content-Length', (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the download response body.
     *
     * @param  string  $filename
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withDownload($filename)
    {
        $new = (clone $this)->withBody(
            create_stream(fopen($filename, 'r+'))
        );

        return $new
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Disposition', sprintf("attachment; filename=\"%s\"", basename($filename)));
    }

    /**
     * Check is the response OK.
     *
     * @return bool
     */
    public function isOk()
    {
        return $this->statusCode === StatusCode::OK;
    }

    /**
     * Check is the response an informational response.
     *
     * @return bool
     */
    public function isInformational()
    {
        $this->statusCode >= StatusCode::CONTINUE && $this->statusCode < StatusCode::OK;
    }

    /**
     * Check is the response a successful response.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        $this->statusCode >= StatusCode::OK && $this->statusCode < StatusCode::MULTIPLE_CHOICES;
    }

    /**
     * Check is the response a redirection response.
     *
     * @return bool
     */
    public function isRedirection()
    {
        $this->statusCode >= StatusCode::MULTIPLE_CHOICES && $this->statusCode < StatusCode::BAD_REQUEST;
    }

    /**
     * Check is the response a client error response.
     *
     * @return bool
     */
    public function isClientError()
    {
        $this->statusCode >= StatusCode::BAD_REQUEST && $this->statusCode < StatusCode::INTERNAL_SERVER_ERROR;
    }

    /**
     * Check is the response a server error response.
     *
     * @return bool
     */
    public function isServerError()
    {
        $this->statusCode >= StatusCode::INTERNAL_SERVER_ERROR && $this->statusCode < 600;
    }

    /**
     * Send the response to browser.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function send()
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to send the response! Headers are already sent.');
        }

        header(sprintf("HTTP/%s %s %s", $this->protocolVersion,
                                        $this->statusCode,
                                        $this->reasonPhrase), true);

        foreach (array_keys($this->getHeaders()) as $name) {
            if (0 === strcasecmp($name, 'set-cookie')) {
                foreach ($this->getHeader($name) as $cookie) {
                    header(sprintf("%s: %s", $cookie), false);
                }
            } else {
                header(sprintf("%s: %s", $this->getHeaderLine($name)), true);
            }
        }

        fwrite(fopen('php://output', 'w'), $this->body);

        exit;
    }

    /**
     * Stringify the response.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return stringify($this);
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
        $this->reasonPhrase = isset(static::REASON_PHRASES[$statusCode]) ? static::REASON_PHRASES[$statusCode] : '';
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
