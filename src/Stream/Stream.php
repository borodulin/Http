<?php

declare(strict_types=1);

namespace Borodulin\Http\Stream;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @var array
     */
    private $meta;

    public function __construct($handle)
    {
        if (!\is_resource($handle)) {
            throw new \InvalidArgumentException('Invalid stream resource');
        }

        $this->handle = $handle;

        $this->meta = stream_get_meta_data($handle);
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     *
     * @return string
     */
    public function __toString()
    {
        if (!\is_resource($this->handle)) {
            return '';
        }
        fseek($this->handle, 0);
        $result = stream_get_contents($this->handle);

        return false === $result ? '' : $result;
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close(): void
    {
        if (\is_resource($this->handle)) {
            fclose($this->handle);
        }
        $this->handle = null;
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $handle = $this->handle;
        $this->handle = null;

        return $handle;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null returns the size in bytes if known, or null if unknown
     */
    public function getSize(): ?int
    {
        $stats = fstat($this->handle);

        return $stats['size'] ?? null;
    }

    /**
     * Returns the current position of the file read/write pointer.
     */
    public function tell(): int
    {
        $this->checkHandle();
        $position = ftell($this->handle);
        if (false === $position) {
            throw new InvalidStreamException();
        }

        return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     */
    public function eof(): bool
    {
        $this->checkHandle();

        return feof($this->handle);
    }

    /**
     * Returns whether or not the stream is seekable.
     */
    public function isSeekable(): bool
    {
        return $this->meta['seekable'] ?? false;
    }

    /**
     * Seek to a position in the stream.
     *
     * @see http://www.php.net/manual/en/function.fseek.php
     *
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *                    based on the seek offset. Valid values are identical to the built-in
     *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                    offset bytes SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     *
     * @throws \RuntimeException on failure
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->checkHandle();
        fseek($this->handle, $offset, $whence);
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @throws \RuntimeException on failure
     *
     * @see http://www.php.net/manual/en/function.fseek.php
     * @see seek()
     */
    public function rewind(): void
    {
        $this->checkHandle();
        fseek($this->handle, 0, SEEK_SET);
    }

    /**
     * Returns whether or not the stream is writable.
     */
    public function isWritable(): bool
    {
        return false !== strpbrk($this->meta['mode'], '+acwx');
    }

    /**
     * Write data to the stream.
     *
     * @param string $string the string that is to be written
     *
     * @throws \RuntimeException on failure
     */
    public function write($string): int
    {
        $this->checkHandle();
        if (!$this->isWritable()) {
            throw new InvalidStreamException('Stream is not writable');
        }

        $result = fwrite($this->handle, $string);

        if (false === $result) {
            throw new InvalidStreamException('Unable to write to the stream');
        }

        return $result;
    }

    /**
     * Returns whether or not the stream is readable.
     */
    public function isReadable(): bool
    {
        return false !== strpbrk($this->meta['mode'], '+r');
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     *
     * @return string returns the data read from the stream, or an empty string
     *                if no bytes are available
     *
     * @throws \RuntimeException if an error occurs
     */
    public function read($length): ?string
    {
        $this->checkHandle();
        $string = fread($this->handle, $length);

        return false === $string ? null : $string;
    }

    /**
     * Returns the remaining contents in a string.
     *
     * @throws \RuntimeException if unable to read or an error occurs while
     *                           reading
     */
    public function getContents(): string
    {
        $this->checkHandle();

        return stream_get_contents($this->handle);
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     *
     * @param string $key specific metadata to retrieve
     *
     * @return array|mixed|null Returns an associative array if no key is
     *                          provided. Returns a specific key value if a key is provided and the
     *                          value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if (null !== $key) {
            return $this->meta[$key] ?? null;
        }

        return $this->meta;
    }

    private function checkHandle(): void
    {
        if (!\is_resource($this->handle)) {
            throw new InvalidStreamException();
        }
    }
}
