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

use InvalidArgumentException;
use Raylin666\Http\Contract\CookieInterface;

/**
 * Class Cookie
 * @package Raylin666\Http\Message
 */
class Cookie implements CookieInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $domain;

    /**
     * Default time() + $expire.
     *
     * @var int
     */
    protected $expire;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $secure;

    /**
     * @var bool
     */
    protected $httpOnly;

    /**
     * Cookie constructor.
     * @param      $name
     * @param null $value
     * @param null $expire
     * @param null $path
     * @param null $domain
     * @param null $secure
     * @param null $httpOnly
     */
    public function __construct(
        $name,
        $value = null,
        $expire = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httpOnly = null
    )
    {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        $this->withPath($path)
            ->withName($name)
            ->withValue($value)
            ->withDomain($domain)
            ->withExpire($expire)
            ->withSecure($secure)
            ->withHttpOnly($httpOnly);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        // TODO: Implement getName() method.

        return $this->name;
    }

    /**
     * @param $name
     * @return Cookie
     */
    public function withName($name): Cookie
    {
        // TODO: Implement withName() method.

        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        // TODO: Implement getValue() method.

        return $this->value;
    }

    /**
     * @param $value
     * @return Cookie
     */
    public function withValue($value): Cookie
    {
        // TODO: Implement withValue() method.

        $this->value = $value;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        // TODO: Implement getDomain() method.

        return $this->domain;
    }

    /**
     * @param $domain
     * @return Cookie
     */
    public function withDomain($domain): Cookie
    {
        // TODO: Implement withDomain() method.

        $this->domain = $domain;
        return $this;
    }

    /**
     * @return int
     */
    public function getExpire(): int
    {
        // TODO: Implement getExpire() method.

        return $this->expire;
    }

    /**
     * @param int $expire
     * @return Cookie
     */
    public function withExpire(int $expire): Cookie
    {
        // TODO: Implement withExpire() method.

        $this->expire = $expire;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        // TODO: Implement getPath() method.

        return $this->path;
    }

    /**
     * @param $path
     * @return Cookie
     */
    public function withPath($path): Cookie
    {
        // TODO: Implement withPath() method.

        $this->path = $path;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        // TODO: Implement isSecure() method.

        return $this->secure;
    }

    /**
     * @param bool $secure
     * @return Cookie
     */
    public function withSecure(bool $secure): Cookie
    {
        // TODO: Implement withSecure() method.

        $this->secure = $secure;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        // TODO: Implement isHttpOnly() method.

        return $this->httpOnly;
    }

    /**
     * @param bool $httpOnly
     * @return Cookie
     */
    public function withHttpOnly(bool $httpOnly): Cookie
    {
        // TODO: Implement withHttpOnly() method.

        $this->httpOnly = $httpOnly;
        return $this;
    }

    /**
     * @return string
     */
    public function asString(): string
    {
        $str = urlencode($this->name) . '=';
        if ('' === strval($this->value)) {
            $str .= 'deleted; expires=' . gmdate("D, d-M-Y H:i:s T", time() - 31536001);
        } else {
            $str .= urlencode($this->value);
        }
        if ($this->expire > 0) {
            $str .= '; expires=' . gmdate("D, d-M-Y H:i:s T", time () + $this->expire);
        }
        if ($this->path) {
            $str .= '; path=' . $this->path;
        }
        if ($this->domain) {
            $str .= '; domain=' . $this->domain;
        }
        if (true === $this->secure) {
            $str .= '; secure';
        }
        if (true === $this->httpOnly) {
            $str .= '; httponly';
        }

        return $str;
    }

    /**
     * Returns the cookie's value.
     * @return string The cookie value
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.

        return (string) $this->value;
    }
}