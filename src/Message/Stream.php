<?php
// +----------------------------------------------------------------------
// | Created by linshan. 版权所有 @
// +----------------------------------------------------------------------
// | Copyright (c) 2021 All rights reserved.
// +----------------------------------------------------------------------
// | Technology changes the world . Accumulation makes people grow .
// +----------------------------------------------------------------------
// | Author: kaka梦很美 <1099013371@qq.com>
// +----------------------------------------------------------------------

namespace Raylin666\Http\Message;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class Stream
 *
 * 描述数据流。
 * 通常，实例将包装PHP流; 此接口提供了最常见操作的包装，包括将整个流序列化为字符串。
 *
 * @package Raylin666\Http\Message
 */
class Stream implements StreamInterface
{
    /**
     * 流类型
     */
    const STREAM_INPUT = 'php://input';
    const STREAM_MEMORY = 'php://memory';

    /**
     * @var string
     */
    protected $stream;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var bool
     */
    protected $readable = false;

    /**
     * @var bool
     */
    protected $writable = false;

    /**
     * @var bool
     */
    protected $seekable = false;

    /**
     * @var array
     */
    protected static $modeHash = [
        'read' => [
            'r'   => true,
            'w+'  => true,
            'r+'  => true,
            'x+'  => true,
            'c+'  => true,
            'rb'  => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'rt'  => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a+'  => true
        ],
        'write' => [
            'w'   => true,
            'w+'  => true,
            'rw'  => true,
            'r+'  => true,
            'x+'  => true,
            'c+'  => true,
            'wb'  => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a'   => true,
            'a+'  => true
        ]
    ];

    /**
     * Stream constructor.
     *
     * @see http://php.net/manual/zh/wrappers.php.php
     * @param $stream
     * @param string $mode
     */
    public function __construct($stream, string $mode = 'r')
    {
        $this->stream = $stream;
        $this->mode = $mode;
        $this->resource = fopen($stream, $this->mode);
        $meta = $this->getMetadata();
        $this->seekable = $meta['seekable'];
        $this->readable = isset(static::$modeHash['read'][$meta['mode']]);
        $this->writable = isset(static::$modeHash['write'][$meta['mode']]);
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * 从头到尾将流中的所有数据读取到字符串。
     *
     * 这个方法 **必须** 在开始读数据前定位到流的开头，并读取出所有的数据。
     *
     * 警告：这可能会尝试将大量数据加载到内存中。
     *
     * 这个方法 **不得** 抛出异常以符合 PHP 的字符串转换操作。
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.

        $string = '';

        try {
            $this->rewind();
            $string = $this->getContents();
        } catch (RuntimeException $e) {}

        return $string;
    }

    /**
     * 关闭流和任何底层资源
     *
     * @return void
     */
    public function close()
    {
        // TODO: Implement close() method.

        if (null !== $this->resource) {
            $resource = $this->detach();
            fclose($resource);;
        }
    }

    /**
     * 从流中分离任何底层资源。
     *
     * 分离之后，流处于不可用状态。
     *
     * @return resource|null 如果存在的话，返回底层 PHP 流。
     */
    public function detach()
    {
        // TODO: Implement detach() method.

        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * 如果可知，获取流的数据大小。
     *
     * @return int|null 如果可知，返回以字节为单位的大小，如果未知返回 `null`。
     */
    public function getSize(): ?int
    {
        // TODO: Implement getSize() method.

        return $this->resource ? null : fstat($this->resource)['size'];
    }

    /**
     * 返回当前读/写的指针位置。
     *
     * @return int 指针位置。
     * @throws \RuntimeException 产生错误时抛出。
     */
    public function tell(): int
    {
        // TODO: Implement tell() method.

        if (! $this->resource) {
            throw new RuntimeException('No resource available; cannot tell position');
        }

        $result = ftell($this->resource);
        if (! is_int($result)) {
            throw new RuntimeException('Error occurred during tell operation');
        }

        return $result;
    }

    /**
     * 返回是否位于流的末尾
     *
     * @return bool
     */
    public function eof(): bool
    {
        // TODO: Implement eof() method.

        return $this->resource ? feof($this->resource) : true;
    }

    /**
     * 返回流是否可随机读取.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        // TODO: Implement isSeekable() method.

        return $this->seekable;
    }

    /**
     * 定位流中的指定位置。
     *
     * @see http://www.php.net/manual/en/function.fseek.php
     * @param int $offset 要定位的流的偏移量。
     * @param int $whence 指定如何根据偏移量计算光标位置。有效值与 PHP 内置函数 `fseek()` 相同。
     *     SEEK_SET：设定位置等于 $offset 字节。默认。
     *     SEEK_CUR：设定位置为当前位置加上 $offset。
     *     SEEK_END：设定位置为文件末尾加上 $offset （要移动到文件尾之前的位置，offset 必须是一个负值）。
     * @throws \RuntimeException 失败时抛出。
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        // TODO: Implement seek() method.

        if (! $this->resource) {
            throw new RuntimeException('No resource available; cannot seek position');
        }

        if (! $this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        $result = fseek($this->resource, $offset, $whence);

        if (0 !== $result) {
            throw new RuntimeException('Error seeking within stream');
        }

        return true;
    }

    /**
     * 定位流的起始位置。
     *
     * 如果流不可以随机访问，此方法将引发异常；否则将执行 seek(0)。
     *
     * @see seek()
     * @see http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException 失败时抛出。
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.

        return $this->seek(0);
    }

    /**
     * 返回流是否可写
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        // TODO: Implement isWritable() method.

        return $this->writable;
    }

    /**
     * 向流中写数据。
     *
     * @param string $string 要写入流的数据。
     * @return int 返回写入流的字节数。
     * @throws \RuntimeException 失败时抛出。
     */
    public function write($string): int
    {
        // TODO: Implement write() method.

        if (! $this->resource) {
            throw new RuntimeException('No resource available; cannot write');
        }

        $result = fwrite($this->resource, $string);

        if (false === $result) {
            throw new RuntimeException('Unable to writing from stream');
        }

        return $result;
    }

    /**
     * 返回流是否可读
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        // TODO: Implement isReadable() method.

        return $this->readable;
    }

    /**
     * 从流中读取数据。
     *
     * @param int $length 从流中读取最多 $length 字节的数据并返回。如果数据不足，则可能返回少于
     *     $length 字节的数据。
     * @return string 返回从流中读取的数据，如果没有可用的数据则返回空字符串。
     * @throws \RuntimeException 失败时抛出。
     */
    public function read($length): string
    {
        // TODO: Implement read() method.

        if (! $this->resource) {
            throw new RuntimeException('No resource available; cannot read');
        }

        if (! $this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $string = fread($this->resource, (int) $length);

        if (false === $string) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    /**
     * 返回字符串中的剩余内容。
     *
     * @return string
     * @throws \RuntimeException 如果无法读取则抛出异常。
     * @throws \RuntimeException 如果在读取时发生错误则抛出异常。
     */
    public function getContents(): string
    {
        // TODO: Implement getContents() method.

        if (! $this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = stream_get_contents($this->resource);

        if (false === $result) {
            throw new RuntimeException('Error reading from stream');
        }

        return $result;
    }

    /**
     * 获取流中的元数据作为关联数组，或者检索指定的键。
     *
     * 返回的键与从 PHP 的 stream_get_meta_data() 函数返回的键相同。
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key 要检索的特定元数据。
     * @return array|mixed|null 如果没有键，则返回关联数组。如果提供了键并且找到值，
     *     则返回特定键值；如果未找到键，则返回 null。
     */
    public function getMetadata($key = null)
    {
        // TODO: Implement getMetadata() method.

        if (! $this->resource) {
            throw new RuntimeException('No resource available; cannot write');
        }

        $metadata = stream_get_meta_data($this->resource);

        if (null === $key) {
            return $metadata;
        }

        return !array_key_exists($key, $metadata) ? null : $metadata[$key];
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }
}