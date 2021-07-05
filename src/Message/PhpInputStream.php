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

/**
 * Class PhpInputStream
 * @package Raylin666\Http\Message
 */
class PhpInputStream extends Stream
{
    /**
     * @var string
     */
    private $cache = '';

    /**
     * @var bool
     */
    private $reachedEof = false;

    /**
     * PhpInputStream constructor.
     *
     * @param string $stream
     * @param string $mode
     */
    public function __construct($stream = Stream::STREAM_INPUT, string $mode = 'r')
    {
        parent::__construct($stream, $mode);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (! $this->reachedEof) {
            $this->rewind();
            $this->getContents();
        }

        return $this->cache;
    }

    /**
     * @return bool false
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * @param int $length
     * @return string
     */
    public function read($length): string
    {
        $content = parent::read($length);
        if ($content && ! $this->reachedEof) {
            $this->cache .= $content;
        }

        if ($this->eof()) {
            $this->reachedEof = true;
        }

        return $content;
    }

    /**
     * @param int $maxLength
     * @return string
     */
    public function getContents($maxLength = -1): string
    {
        if ($this->reachedEof) {
            return $this->cache;
        }

        $contents = stream_get_contents($this->resource, $maxLength);
        $this->cache .= $contents;

        if ($maxLength === -1 || $this->eof()) {
            $this->reachedEof = true;
        }

        return $contents;
    }
}