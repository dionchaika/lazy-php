<?php

namespace Lazy\Http;

use InvalidArgumentException;

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
     * The array of
     * multipart/form-data body parts.
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

        $contents = create_stream($part['contents']);

        return $this;
    }
}
