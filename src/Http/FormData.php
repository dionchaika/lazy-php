<?php

namespace Lazy\Http;

use Throwable;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * The multipart/form-data body model.
 *
 * @see https://tools.ietf.org/html/rfc2046#section-5.1
 */
class FormData
{
    /**
     * The multipart/form-data boundary charset.
     */
    const BOUNDARY_CHARSET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * The array of multipart/form-data body parts.
     *
     * @var mixed[]
     */
    protected $parts = [];

    /**
     * The multipart/form-data body boundary.
     *
     * @var string
     */
    protected $boundary;

    /**
     * The multipart/form-data body constructor.
     *
     * @param  mixed[]  $parts
     * @param  string|null  $boundary
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $parts = [], ?string $boundary = null)
    {
        foreach ($parts as $part) {
            $this->append($part);
        }

        $this->boundary = $boundary ?? static::generateBoudary();
    }

    /**
     * Generate a multipart/form-data boundary.
     *
     * @param  int  $length
     * @param  string  $prefix
     * @return string
     */
    public static function generateBoudary(int $length = 16,
                                           string $prefix = '----TheLazyPHPFormBoundary'): string
    {
        $randMin = 0;
        $randMax = strlen(static::BOUNDARY_CHARSET) - 1;

        $boundary = '';

        for ($i = 0; $i < $length; $i++) {
            $boundary .= static::BOUNDARY_CHARSET[rand($randMin, $randMax)];
        }

        return $prefix.$boundary;
    }

    /**
     * Get the multipart/form-data body boundary.
     *
     * @return string
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }

    /**
     * Append a new multipart/form-data body part.
     *
     * @param  mixed[]  $part
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function append(array $part): self
    {
        foreach (['name', 'contents'] as $key) {
            if (! array_key_exists($key, $part)) {
                throw new InvalidArgumentException("Part key is not set: {$key}!");
            }
        }

        if (! isset($part['headers'])) {
            $part['headers'] = [];
        }

        if (! isset($part['filename'])) {
            $part['filename'] = null;
        }

        $part['contents'] = create_stream($part['contents']);

        if (! $this->hasHeader('Content-Disposition', $part['headers'])) {
            $disposition = $part['filename']
                ? sprintf("form-data; name=\"%s\"; filename=\"%s\"",
                    $part['name'],
                    basename($part['filename']))
                : sprintf("form-data; name=\"%s\"", $part['name']);

            $part['headers']['Content-Disposition'] = $disposition;
        }

        if ($part['filename']) {
            if (! $this->hasHeader('Content-Type', $part['headers'])) {
                $part['headers']['Content-Type'] = mime_content_type($part['filename']);
            }

            $length = $part['contents']->getSize();

            if (! $this->hasHeader('Content-Length', $part['headers']) && $length) {
                $part['headers']['Content-Length'] = $length;
            }
        }

        $this->parts[] = $part;

        return $this;
    }

    /**
     * Get the multipart/form-data body stream.
     *
     * @return \Psr\Http\Message\StreamInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getStream(): StreamInterface
    {
        return create_stream((string) $this);
    }

    /**
     * Stringify the multipart/form-data body.
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            $str = '';

            foreach ($this->parts as $part) {
                $str .= $this->stringifyHeaders($part['headers']).$part['contents']."\r\n";
            }

            return $str.$this->boundary;
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }

    /**
     * Check is the header exists in the array.
     *
     * @param  string  $name
     * @param  mixed[]  $headers
     * @return bool
     */
    protected function hasHeader(string $name, array $headers): bool
    {
        return array_key_exists(
            strtolower($name), array_change_key_case($headers)
        );
    }

    /**
     * Stringify headers for the multipart/form-data body.
     *
     * @param  mixed[]  $headers
     * @return string
     */
    protected function stringifyHeaders(array $headers): string
    {
        $str = '';

        foreach ($headers as $name => $value) {
            $str .= sprintf("%s: %s\r\n", $name, implode(', ', (array) $value));
        }

        return sprintf("%s\r\n%s\r\n", $this->boundary, $str);
    }
}
