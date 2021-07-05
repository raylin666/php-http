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

namespace Raylin666\Http\Contract;

/**
 * Interface CookieInterface
 * @package Raylin666\Http\Contract
 */
interface CookieInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param $name
     * @return mixed
     */
    public function withName($name);

    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param $value
     * @return mixed
     */
    public function withValue($value);

    /**
     * @return string|null
     */
    public function getDomain(): ?string;

    /**
     * @param $domain
     * @return mixed
     */
    public function withDomain($domain);

    /**
     * @return int
     */
    public function getExpire(): int;

    /**
     * @param int $expire
     * @return mixed
     */
    public function withExpire(int $expire);

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param $path
     * @return mixed
     */
    public function withPath($path);

    /**
     * @return bool
     */
    public function isSecure(): bool;

    /**
     * @param bool $secure
     * @return mixed
     */
    public function withSecure(bool $secure);

    /**
     * @return bool
     */
    public function isHttpOnly(): bool;

    /**
     * @param bool $httpOnly
     * @return mixed
     */
    public function withHttpOnly(bool $httpOnly);

    /**
     * @return mixed
     */
    public function __toString();
}