<?php

namespace Lazy\Http;

use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * {@inheritDoc}
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * The error messages.
     */
    const ERROR_MESSAGES = [

        \UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success.',
        \UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        \UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        \UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        \UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        \UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        \UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        \UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.'

    ];

    /**
     * @var int|null The uploaded file size.
     */
    protected $size;

    /**
     * @var int The uploaded file error.
     */
    protected $error = \UPLOAD_ERR_OK;

    /**
     * @var bool Is the uploaded file moved.
     */
    protected $moved = false;

    /**
     * @var \Psr\Http\Message\StreamInterface The uploaded file stream.
     */
    protected $stream;

     /**
     * @var string The uploaded file filename.
     */
    protected $filename;

    /**
     * @var string|null The uploaded file client filename.
     */
    protected $clientFilename;

    /**
     * @var string|null The uploaded file client media type.
     */
    protected $clientMediaType;

    /**
     * The uploaded file constructor.
     *
     * @param  \Psr\Http\Message\StreamInterface|string  $file  The uploaded file.
     * @param  int|null  $size  The uploaded file size.
     * @param  int  $error  The uploaded file error.
     * @param  string|null  $clientFilename  The uploaded file filename.
     * @param  string|null  $clientMediaType  The uploaded file client media type.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($file, $size = null, $error = \UPLOAD_ERR_OK, $clientFilename = null, $clientMediaType = null)
    {
        if ($file instanceof StreamInterface) {
            if (! $file->isReadable()) {
                throw new InvalidArgumentException('Invalid stream! Stream is not readable.');
            }

            $this->size = $size ?? $this->stream->getSize();
            $this->stream = $file;
        } else {
            $this->size = $size;
            $this->filename = $file;
        }

        $this->error = $this->filterError($error);
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * Create a new array of uploaded files from PHP globals.
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public static function fromGlobals()
    {
        return static::normalizeFiles($_FILES);
    }

    /**
     * Normalize an array of uploaded files.
     *
     * @param  array  $files  The array of uploaded files.
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public static function normalizeFiles(array $files)
    {
        $normalized = [];

        foreach ($files as $name => $info) {
            if ($info instanceof UploadedFileInterface) {
                $normalized[$name] = $info;
                continue;
            }

            if (! isset($info['error'])) {
                if (is_array($info)) {
                    $normalized[$name] = static::normalizeFiles($info);
                }

                continue;
            }

            $normalized[$name] = [];

            if (! is_array($info['error'])) {
                $normalized[$name] = new static(
                    $info['tmp_name'],
                    ! empty($info['size']) ? $info['size'] : null,
                    $info['error'],
                    ! empty($info['name']) ? $info['name'] : null,
                    ! empty($info['type']) ? $info['type'] : null
                );
            } else {
                $nestedInfo = [];

                foreach (array_keys($info['error']) as $key) {
                    $nestedInfo[$key]['tmp_name'] = $info['tmp_name'][$key];
                    $nestedInfo[$key]['size'] = $info['size'][$key];
                    $nestedInfo[$key]['error'] = $info['error'][$key];
                    $nestedInfo[$key]['name'] = $info['name'][$key];
                    $nestedInfo[$key]['type'] = $info['type'][$key];

                    $normalized[$name] = static::normalizeFiles($nestedInfo);
                }
            }
        }

        return $normalized;
    }

    /**
     * {@inheritDoc}
     */
    public function getStream()
    {
        if ($this->moved) {
            throw new RuntimeException('Stream is not avaliable! Uploaded file is moved.');
        }

        if (! $this->stream) {
            $resource = fopen($this->filename, 'r+');

            if (false === $resource) {
                throw new RuntimeException('Unable to get the uploaded file stream!');
            }

            $this->stream = create_stream($resource);
        }

        return $this->stream;
    }

    /**
     * {@inheritDoc}
     */
    public function moveTo($targetPath)
    {
        if ($this->moved) {
            throw new RuntimeException('Uploaded file is already moved!');
        }

        $targetPath = $this->filterTargetPath($targetPath);

        if ($this->filename) {
            if ('cli' === \PHP_SAPI) {
                if (false === rename($this->filename, $targetPath)) {
                    throw new RuntimeException('Unable to rename the uploaded file!');
                }
            } else {
                if (
                    false === is_uploaded_file($this->filename) ||
                    false === move_uploaded_file($this->filename, $targetPath)
                ) {
                    throw new RuntimeException('Unable to move the uploaded file!');
                }
            }
        } else {
            $oldStream = $this->getStream();

            $resource = fopen($targetPath, 'r+');

            if (false === $resource) {
                throw new RuntimeException('Unable to create a stream for a new uploaded file location!');
            }

            $newStream = create_stream($resource);

            if (false === stream_copy_to_stream($oldStream, $newStream)) {
                throw new RuntimeException('Unable to copy the uploaded file stream!');
            }
        }

        $this->moved = true;
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
    public function getError()
    {
        return $this->error;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * Get the uploaded file error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return static::ERROR_MESSAGES[$this->error];
    }

    /**
     * Filter an uploaded file error.
     *
     * @param  int  $error  The uploaded file error.
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function filterError($error)
    {
        if (
            $error !== \UPLOAD_ERR_OK &&
            $error !== \UPLOAD_ERR_INI_SIZE &&
            $error !== \UPLOAD_ERR_FORM_SIZE &&
            $error !== \UPLOAD_ERR_PARTIAL &&
            $error !== \UPLOAD_ERR_NO_FILE &&
            $error !== \UPLOAD_ERR_NO_TMP_DIR &&
            $error !== \UPLOAD_ERR_CANT_WRITE &&
            $error !== \UPLOAD_ERR_EXTENSION
        ) {
            throw new InvalidArgumentException(
                "Invalid error: {$error}! "
                ."Error must be a PHP file upload error."
            );
        }

        return $error;
    }

    /**
     * Filter an uploaded file target path.
     *
     * @param  string  $targetPath  The uploaded file target path.
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterTargetPath($targetPath)
    {
        if (! $targetPath) {
            throw new InvalidArgumentException("Invalid target path: {$targetPath}! Target path can not be empty.");
        }

        return $targetPath;
    }
}
