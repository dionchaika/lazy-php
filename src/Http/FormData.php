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

        $formData = new static([], $boundary);

        foreach ($parts as $part) {
            if (false === strpos($part, "\r\n\r\n")) {
                throw new InvalidArgumentException(
                    "Invalid \"multipart/form-data\" part: {$part}! "
                    ."\"multipart/form-data\" part must contain header fields and the contents."
                );
            }

            [$headers, $contents] = explode("\r\n\r\n", $part, 2);
            [$headers, $contents] = [Headers::fromString($headers), create_stream($contents)];

            if (
                ! $headers->has('Content-Disposition') ||
                ! preg_match('/^form\-data\;\s*name\=\".+\"/', $headers->getLine('Content-Disposition'))
            ) {
                throw new InvalidArgumentException(
                    "Invalid \"multipart/form-data\" part: {$part}! \"multipart/form-data\" part "
                    ."must contain a \"Content-Disposition\" header field with \"form-data\" type and \"name\" parameter."
                );
            }

            preg_match('/name\=\"(.+)\"/', $headers->getLine('Content-Disposition'), $nameMatches);
            preg_match('/filename\=\"(.+)\"/', $headers->getLine('Content-Disposition'), $filenameMatches);

            $formData->append([

                'name'     => $nameMatches[1],
                'headers'  => $headers,
                'contents' => $contents,
                'filename' => isset($filenameMatches[1]) ? $filenameMatches[1] : null

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

        extract($part);

        if (! isset($headers)) {
            $headers = new Headers;
        } else {
            $headers = ($headers instanceof Headers) ? $headers : new Headers($headers);
        }

        $filename = isset($filename) ? $filename : null;
        $contents = ($contents instanceof StreamInterface) ? $contents : create_stream($contents);

        if (! $headers->has('Content-Disposition')) {
            $headers->set('Content-Disposition', ! $filename
                ? sprintf('form-data; name="%s"', $name)
                : sprintf('form-data; name="%s"; filename="%s"', $name, basename($filename))
            );
        }

        if ($filename && ! $headers->has('Content-Type')) {
            try {
                $mimeType = mime_content_type($filename);
                $headers->set('Content-Type', $mimeType);
            } catch (Throwable $e) {
                $headers->set('Content-Type', 'application/octet-stream');
            }
        }

        $this->parts[] = compact('name', 'contents', 'headers', 'filename');
    }

    /**
     * Get the array of multipart/form-data parts.
     *
     * @return array
     */
    public function getParts()
    {
        return $this->parts;
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
        return create_stream($this);
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
                $str .= sprintf(
                    "--%s\r\n%s\r\n%s\r\n", $this->boundary, $part['headers'], $part['contents']
                );
            }

            return sprintf('%s--%s', $str, $this->boundary);
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }
}
