<?php

namespace Lazy\Http;

use Throwable;
use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * The PSR-7 stream wrapper class.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 */
class Stream implements StreamInterface
{
    /**
     * The readable stream mode pattern.
     */
    const READABLE_STREAM_MODE_PATTERN = '/r|r\+|w\+|a\+|x\+|c\+/';

    /**
     * The writable stream mode pattern.
     */
    const WRITABLE_STREAM_MODE_PATTERN = '/r\+|w|w\+|a|a\+|x|x\+|c|c\+/';

    /**
     * The stream size.
     *
     * @var int|null
     */
    protected $size;

    /**
     * The stream underlying resource.
     *
     * @var resource
     */
    protected $resource;

    /**
     * Is the stream seekable.
     *
     * @var bool
     */
    protected $seekable = false;

    /**
     * Is the stream readable.
     *
     * @var bool
     */
    protected $readable = false;

    /**
     * Is the stream writable.
     *
     * @var bool
     */
    protected $writable = false;

    /**
     * The stream constructor.
     *
     * Allowed stream options:
     *      1. size (int) - the stream size.
     *      2. seekable (bool) - is the stream seekable.
     *      3. readable (bool) - is the stream readable.
     *      4. writable (bool) - is the stream writable.
     *
     * @param  string|resource  $body
     * @param  string  $mode
     * @param  mixed[]  $opts
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct($body = '', $mode = 'r', $opts = [])
    {
        if (is_string($body)) {
            if (is_file($body) || 0 === strpos($body, 'php://')) {
                $mode = $this->filterMode($mode);

                $resource = fopen($body, $mode);
                if (false === $resource) {
                    throw new RuntimeException('Unable to create a stream from file: '.$body.'!');
                }

                $this->resource = $resource;
            } else {
                $resource = fopen('php://temp', 'r+');
                if (false === $resource || false === fwrite($resource, $body)) {
                    throw new RuntimeException('Unable to create a stream from string!');
                }

                $this->resource = $resource;
            }
        } else if (is_resource($body)) {
            $this->resource = $body;
        } else {
            throw new InvalidArgumentException('Resource must be the PHP resource!');
        }

        if (isset($opts['size'])) {
            $this->size = $opts['size'];
        } else {
            $fstat = fstat($this->resource);
            if (false === $fstat) {
                $this->size = null;
            } else {
                $this->size = ! empty($fstat['size']) ? $fstat['size'] : null;
            }
        }

        $meta = stream_get_meta_data($this->resource);

        if (isset($opts['seekable'])) {
            $this->seekable = $opts['seekable'];
        } else {
            $this->seekable = ! empty($meta['seekable']) ? $meta['seekable'] : false;
        }

        if (isset($opts['readable'])) {
            $this->readable = $opts['readable'];
        } else {
            $this->readable = preg_match(static::READABLE_STREAM_MODE_PATTERN, $meta['mode']);
        }

        if (isset($opts['writable'])) {
            $this->writable = $opts['writable'];
        } else {
            $this->writable = preg_match(static::WRITABLE_STREAM_MODE_PATTERN, $meta['mode']);
        }
    }

    /**
     *  Close the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if (null !== $this->resource && fclose($this->resource)) {
            $this->detach();
        }
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * @return resource|null
     */
    public function detach()
    {
        $resource = $this->resource;

        if (null !== $resource) {
            $this->size = $this->resource = null;
            $this->seekable = $this->readable = $this->writable = false;
        }

        return $resource;
    }

    /**
     * Get the stream size.
     *
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Return the current position of the stream read/write pointer.
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    public function tell()
    {
        if (null === $this->resource) {
            throw new RuntimeException('Stream resource is detached!');
        }

        $position = ftell($this->resource);
        if (false === $position) {
            throw new RuntimeException('Unable to tell the current position of the stream read/write pointer!');
        }

        return $position;
    }

    /**
     * Check is the stream at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return null === $this->resource || feof($this->resource);
    }

    /**
     * Check is the stream seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * Seek to a position in the stream.
     *
     * @param  int  $offset
     * @param  int  $whence
     * @return void
     *
     * @throws \RuntimeException
     */
    public function seek($offset, $whence = \SEEK_SET)
    {
        if (null === $this->resource) {
            throw new RuntimeException('Stream resource is detached!');
        }

        if (! $this->seekable) {
            throw new RuntimeException('Stream is not seekable!');
        }

        if (-1 === fseek($this->resource, $offset, $whence)) {
            throw new RuntimeException('Unable to seek to a position in the stream!');
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * Check is the stream writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * Write data to the stream.
     *
     * @param  string  $string
     * @return int
     *
     * @throws \RuntimeException
     */
    public function write($string)
    {
        if (null === $this->resource) {
            throw new RuntimeException('Stream resource is detached!');
        }

        if (! $this->writable) {
            throw new RuntimeException('Stream is not writable!');
        }

        $bytes = fwrite($this->resource, $string);
        if (false === $bytes) {
            throw new RuntimeException('Unable to write data to the stream!');
        }

        $fstat = fstat($this->resource);
        if (false === $fstat) {
            $this->size = null;
        } else {
            $this->size = ! empty($fstat['size']) ? $fstat['size'] : null;
        }

        return $bytes;
    }

    /**
     * Check is the stream readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * Read data from the stream.
     *
     * @param  int  $length
     * @return string
     *
     * @throws \RuntimeException
     */
    public function read($length)
    {
        if (null === $this->resource) {
            throw new RuntimeException('Stream resource is detached!');
        }

        if (! $this->readable) {
            throw new RuntimeException('Stream is not readable!');
        }

        $data = fread($this->resource, $length);
        if (false === $data) {
            throw new RuntimeException('Unable to read data from the stream!');
        }

        return $data;
    }

    /**
     * Get the contents of the stream.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getContents()
    {
        if (null === $this->resource) {
            throw new RuntimeException('Stream resource is detached!');
        }

        if (! $this->readable) {
            throw new RuntimeException('Stream is not readable!');
        }

        $contents = stream_get_contents($this->resource);
        if (false === $contents) {
            throw new RuntimeException('Unable to get the contents of the stream!');
        }

        return $contents;
    }

    /**
     * Get the stream metadata
     * as an associative array or retrieve a specific key.
     *
     * @param  string|null  $key
     * @return mixed|mixed[]|null
     */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->resource);

        if (null === $key) {
            return $meta;
        }

        return ! empty($meta[$key]) ? $meta[$key] : null;
    }

    /**
     * Read all data from the stream
     * into a string, from the beginning to end.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            if ($this->seekable) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (Throwable $e) {
            return '';
        }
    }

    /**
     * Filter a stream mode.
     *
     * @param  string  $mode
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterMode($mode) {
        if (
            ! preg_match(static::READABLE_STREAM_MODE_PATTERN, $mode) &&
            ! preg_match(static::WRITABLE_STREAM_MODE_PATTERN, $mode)
        ) {
            throw new InvalidArgumentException('Invalid stream mode!');
        }

        return $mode;
    }
}
