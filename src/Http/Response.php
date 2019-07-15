<?php

namespace Lazy\Http;

use Throwable;
use RuntimeException;
use SimpleXMLElement;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * {@inheritDoc}
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
     * @param  \Lazy\Http\Headers|array|null  $headers
     * @param  \Psr\Http\Message\StreamInterface|mixed|null  $body
     * @param  string  $protocolVersion
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($code = StatusCode::OK,
                                $reasonPhrase = self::REASON_PHRASES[StatusCode::OK],
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

        if (! $headers) {
            $this->headers = new Headers();
        } else {
            $this->headers = ($headers instanceof Headers)
                ? $headers
                : new Headers($headers);
        }

        $this->body = create_stream($body);
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * Create a new response from string.
     *
     * @param  string  $response
     * @return \Lazy\Http\Response
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function fromString($response)
    {
        return parse_response($response);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
        return $this->withAddedHeader(Header::SET_COOKIE, (string) $cookie);
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
            ->withHeader(Header::LOCATION, (string) $location);
    }

    /**
     * Return an instance
     * with the "text/html" response body.
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
            ->withHeader(Header::CONTENT_TYPE, 'text/html')
            ->withHeader(Header::CONTENT_LENGTH, (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the "text/plain" response body.
     *
     * @param  mixed  $text
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withPlainText($text)
    {
        $new = (clone $this)->withBody(create_stream($text));

        return $new
            ->withHeader(Header::CONTENT_TYPE, 'text/plain')
            ->withHeader(Header::CONTENT_LENGTH, (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the "application/json" response body.
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
            ->withHeader(Header::CONTENT_TYPE, 'application/json')
            ->withHeader(Header::CONTENT_LENGTH, (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the "text/xml" response body.
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
            ->withHeader(Header::CONTENT_TYPE, 'text/xml')
            ->withHeader(Header::CONTENT_LENGTH, (string) $new->getBody()->getSize());
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
            ->withHeader(Header::CONTENT_TYPE, mime_content_type($filename))
            ->withHeader(Header::CONTENT_LENGTH, (string) $new->getBody()->getSize());
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
            ->withHeader(Header::CONTENT_TYPE, 'application/octet-stream')
            ->withHeader(Header::CONTENT_DESCRIPTION, 'File Transfer')
            ->withHeader(Header::CONTENT_DISPOSITION, sprintf("attachment; filename=\"%s\"", basename($filename)));
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
     * Check is the response an "Informational" response.
     *
     * @return bool
     */
    public function isInformational()
    {
        $this->statusCode >= StatusCode::CONTINUE && $this->statusCode < StatusCode::OK;
    }

    /**
     * Check is the response a "Successful" response.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        $this->statusCode >= StatusCode::OK && $this->statusCode < StatusCode::MULTIPLE_CHOICES;
    }

    /**
     * Check is the response a "Redirection" response.
     *
     * @return bool
     */
    public function isRedirection()
    {
        $this->statusCode >= StatusCode::MULTIPLE_CHOICES && $this->statusCode < StatusCode::BAD_REQUEST;
    }

    /**
     * Check is the response a "Client Error" response.
     *
     * @return bool
     */
    public function isClientError()
    {
        $this->statusCode >= StatusCode::BAD_REQUEST && $this->statusCode < StatusCode::INTERNAL_SERVER_ERROR;
    }

    /**
     * Check is the response a "Server Error" response.
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

        header(sprintf("HTTP/%s %s %s", $this->protocolVersion, $this->statusCode, $this->reasonPhrase));

        $this->headers->send();
        $this->body->send();
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
            throw new InvalidArgumentException("Invalid status code: {$code}! "."Status code 306 is unused.");
        }

        if (100 > $code || 599 < $code) {
            throw new InvalidArgumentException("Invalid status code: {$code}! Status code must be between 100 and 599.");
        }

        return $code;
    }
}
