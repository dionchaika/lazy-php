<?php

namespace Lazy\Http;

use Throwable;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * The multipart/form-data model class.
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
     * The array of multipart/form-data parts.
     *
     * @var array
     */
    protected $parts = [];

    /**
     * The multipart/form-data boundary.
     *
     * @var string
     */
    protected $boundary;

    /**
     * The multipart/form-data constructor.
     *
     * @param  array  $parts
     * @param  string|null  $boundary
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($parts = [], $boundary = null)
    {
        foreach ($parts as $part) {
            $this->append($part);
        }

        $this->boundary = $boundary ?? static::generateBoudary();
    }

    /**
     * Create a new multipart/form-data from string.
     *
     * @param  string  $formData
     * @param  string  $boundary
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString($formData, $boundary)
    {
        $parts = preg_split("/(\r\n)*\-\-{$boundary}(\r\n)*/", $formData);

        array_pop($parts);
        array_shift($parts);

        $formData = new static;

        foreach ($parts as $part) {
            if (false === strpos($part, "\r\n\r\n")) {
                throw new InvalidArgumentException(
                    "Invalid \"multipart/form-data\" part: {$part}! "
                    ."\"multipart/form-data\" part must be compliant with the \"RFC-2046\" standart."
                );
            }

            [$headers, $contents] = explode("\r\n\r\n", $part, 2);

            $headers = Headers::fromString($headers);

            if (! $headers->has('Content-Disposition')) {
                throw new InvalidArgumentException(
                    "Invalid \"multipart/form-data\" part: {$part}! "
                    ."\"multipart/form-data\" part must contain a \"Content-Disposition\" header."
                );
            }

            preg_match('/name\=([^\s]+)/', $headers->getLine('Content-Disposition'), $matches);

            if (! isset($matches[1]) || ! $name = trim($matches[1], '"')) {
                throw new InvalidArgumentException(
                    "Invalid \"multipart/form-data\" part: {$part}! "
                    ."\"multipart/form-data\" part \"Content-Disposition\" header must contain a \"name\" directive."
                );
            }

            preg_match('/filename\=([^\s]+)/', $headers->getLine('Content-Disposition'), $matches);

            $formData->append([

                'name'     => $name,
                'headers'  => $headers,
                'contents' => $contents,
                'filename' => isset($matches[1]) ? trim($matches[1], '"') : null

            ]);
        }

        return $formData;
    }

    /**
     * Generate a multipart/form-data boundary.
     *
     * @param  int  $length
     * @param  string  $prefix
     * @return string
     */
    public static function generateBoudary($length = 16, $prefix = '----TheLazyPHPFormBoundary')
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
     * Append a new multipart/form-data part.
     *
     * @param  array  $part
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function append(array $part)
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

        extract($part);

        $headers = ($headers instanceof Headers) ? $headers : new Headers($headers);
        $contents = create_stream($contents);

        $this->parts[] = compact('name', 'contents', 'headers', 'filename');

        return $this;
    }

    /**
     * Get the multipart/form-data boundary.
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * Get the multipart/form-data stream.
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
     * Stringify the multipart/form-data.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $str = '';

            foreach ($this->parts as $part) {
                $str .= sprintf("--%s\r\n%s\r\n%s\r\n", $this->boundary,
                                                        $part['headers'],
                                                        $part['contents']);
            }

            return $str.'--'.$this->boundary;
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }
}
