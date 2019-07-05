<?php

namespace Lazy\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

use function GuzzleHttp\Psr7\stream_for;

/**
 * The multipart/form-data body model.
 *
 * @see https://tools.ietf.org/html/rfc2046#section-5.1
 */
class FormData
{
    /**
     * The form data boundary.
     *
     * @var string
     */
    protected $boundary;

    /**
     * The array of form data entries.
     *
     * @var mixed[]
     */
    protected $entries = [];

    /**
     * The form data constructor.
     *
     * @param  mixed[]  $formData
     * @param  string|null  $boundary
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $formData = [], string $boundary = null)
    {
        $this->boundary = $boundary ?? $this->generateBoudary();

        foreach ($formData as $entry) {
            if (! isset($entry['name'])) {
                throw new InvalidArgumentException('Invalid form data! Form data field "name" is not set.');
            }

            if (! isset($entry['value'])) {
                throw new InvalidArgumentException('Invalid form data! Form data field "value" is not set.');
            }

            $this->append(
                $entry['name'],
                $entry['value'],
                isset($entry['filename']) ? $entry['filename'] : null,
                isset($entry['headers']) ? $entry['headers'] : []
            );
        }
    }

    /**
     * Get the form data boundary.
     *
     * @return string
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }

    /**
     * Append a new form data field.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @param  string|null  $filename
     * @param  mixed[]  $headers
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function append(string $name,
                           $value,
                           ?string $filename = null,
                           array $headers    = []): self
    {
        if (0 === strpos($value, '@')) {
            $filePath = substr($value, 1);

            if (! is_file($filePath)) {
                throw new InvalidArgumentException("File does not exists: {$filePath}!");
            }

            $value = file_get_contents($filePath);

            if (false === $value) {
                throw new InvalidArgumentException("Unable to get the contents of the file: {$filePath}!");
            }

            $filename = $filename ?? basename($filePath);

            if (! array_key_exists(
                'content-type', array_change_key_case($headers, \CASE_LOWER))
            ) {
                $type = mime_content_type($filePath);

                if (false === $type) {
                    throw new InvalidArgumentException("Unable to get a MIME-type of the file: {$filePath}!");
                }

                $headers['Content-Type'] = $type;
            }
        }

        $this->entries[$name][] = [

            'value'    => $value,
            'filename' => $filename,
            'headers'  => $headers
        ];

        return $this;
    }

    /**
     * Generate a form data boundary.
     *
     * @return string
     */
    public function generateBoudary(): string
    {
        $boundaryLen = 16;
        $boundaryChars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $randMin = 0;
        $randMax = strlen($boundaryChars) - 1;

        $boundary = '';

        for ($i = 0; $i < $boundaryLen; $i++) {
            $boundary .= $boundaryChars[rand($randMin, $randMax)];
        }

        return '----LazyFormBoundary'.$boundary;
    }

    /**
     * Get the form data stream.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getStream(): StreamInterface
    {
        return stream_for((string) $this);
    }

    /**
     * Get the string
     * representation of the form data.
     *
     * @return string
     */
    public function __toString(): string
    {
        $formData = '';

        foreach ($this->entries as $name => $entry) {
            foreach ($entry as $field) {
                $formData .= "--{$this->boundary}\r\n";
                $formData .= "Content-Disposition: form-data; name=\"{$name}\"";

                if (null !== $field['filename']) {
                    $formData .= "; filename=\"{$field['filename']}\"\r\n";
                } else {
                    $formData .= "\r\n";
                }

                foreach ($field['headers'] as $headerName => $headerValue) {
                    $headerValue = ! is_array($headerValue)
                        ? $headerValue
                        : implode(', ', $headerValue);

                    $formData .= "{$headerName}: {$headerValue}\r\n";
                }

                $formData .= "\r\n{$field['value']}\r\n";
            }
        }

        return "{$formData}--{$this->boundary}";
    }
}
