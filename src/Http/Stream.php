<?php

namespace Lazy\Http;

use Throwable;
use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * {@inheritDoc}
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
     * The stream resource.
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
     * @param  resource  $resource  The stream resource.
     * @param  array  $opts  The array of stream options.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($resource, $opts = [])
    {
        if (! is_resource($resource)) {
            throw new InvalidArgumentException('Resource must be the PHP resource!');
        }

        $this->resource = $resource;

        if (isset($opts['size']) && is_int($opts['size'])) {
            $this->size = $opts['size'];
        } else {
            $this->updateSize();
        }

        $meta = stream_get_meta_data($this->resource);

        if (isset($opts['seekable']) && is_bool($opts['seekable'])) {
            $this->seekable = $opts['seekable'];
        } else {
            $this->seekable = ! empty($meta['seekable']) ? $meta['seekable'] : false;
        }

        if (isset($opts['readable']) && is_bool($opts['readable'])) {
            $this->readable = $opts['readable'];
        } else {
            $this->readable = preg_match(static::READABLE_STREAM_MODE_PATTERN, $meta['mode']);
        }

        if (isset($opts['writable']) && is_bool($opts['writable'])) {
            $this->writable = $opts['writable'];
        } else {
            $this->writable = preg_match(static::WRITABLE_STREAM_MODE_PATTERN, $meta['mode']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        if ($this->resource && fclose($this->resource)) {
            $this->detach();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        $resource = $this->resource;

        if ($resource) {
            $this->size = $this->resource = null;
            $this->seekable = $this->readable = $this->writable = false;
        }

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritDoc}
     */
    public function tell()
    {
        if (! $this->resource) {
            throw new RuntimeException('Stream resource is detached!');
        }

        $position = ftell($this->resource);

        if (false === $position) {
            throw new RuntimeException('Unable to tell the current position of the stream read/write pointer!');
        }

        return $position;
    }

    /**
     * {@inheritDoc}
     */
    public function eof()
    {
        return ! $this->resource || feof($this->resource);
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = \SEEK_SET)
    {
        if (! $this->resource) {
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
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * {@inheritDoc}
     */
    public function write($string)
    {
        if (! $this->resource) {
            throw new RuntimeException('Stream resource is detached!');
        }

        if (! $this->writable) {
            throw new RuntimeException('Stream is not writable!');
        }

        $bytes = fwrite($this->resource, $string);

        if (false === $bytes) {
            throw new RuntimeException('Unable to write data to the stream!');
        }

        $this->updateSize();

        return $bytes;
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * {@inheritDoc}
     */
    public function read($length)
    {
        if (! $this->resource) {
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
     * {@inheritDoc}
     */
    public function getContents()
    {
        if (! $this->resource) {
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
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->resource);

        if (! $key) {
            return $meta;
        }

        return ! empty($meta[$key]) ? $meta[$key] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        try {
            if ($this->seekable) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (Throwable $e) { return ''; }
    }

    /**
     * Clear the stream.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function clear()
    {
        if (! $this->resource) {
            throw new RuntimeException('Stream resource is detached!');
        }

        if (false === ftruncate($this->resource, 0)) {
            throw new RuntimeException('Unable to clear the stream!');
        }

        $this->size = 0;

        if ($this->seekable) {
            $this->rewind();
        }
    }

    /**
     * Append another stream to this stream.
     *
     * @param  \Psr\Http\Message\StreamInterface  $stream  The stream to append.
     * @return $this
     *
     * @throws \RuntimeException
     */
    public function append(StreamInterface $stream)
    {
        $this->seek($this->size);
        $this->write((string) $stream);

        return $this;
    }

    /**
     * Prepend another stream to this stream.
     *
     * @param  \Psr\Http\Message\StreamInterface  $stream  The stream to prepend.
     * @return $this
     *
     * @throws \RuntimeException
     */
    public function prepend(StreamInterface $stream)
    {
        $contents = (string) $this;

        $this->rewind();
        $this->write($stream.$contents);

        return $this;
    }

    /**
     * Send the stream contents to browser.
     *
     * @return void
     */
    public function send()
    {
        fwrite(fopen('php://output', 'w'), (string) $this);
    }

    /**
     * Update the stream size.
     *
     * @return void
     */
    protected function updateSize()
    {
        $fstat = fstat($this->resource);

        if (false === $fstat) {
            $this->size = null;
        } else {
            $this->size = ! empty($fstat['size']) ? $fstat['size'] : null;
        }
    }

    /**
     * Filter a stream mode.
     *
     * @param  string  $mode  The stream mode.
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterMode($mode) {
        if (
            ! preg_match(static::READABLE_STREAM_MODE_PATTERN, $mode) &&
            ! preg_match(static::WRITABLE_STREAM_MODE_PATTERN, $mode)
        ) {
            throw new InvalidArgumentException("Invalid stream mode: {$mode}!");
        }

        return $mode;
    }
}
