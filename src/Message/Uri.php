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
use Psr\Http\Message\UriInterface;

/**
 * Class Uri
 *
 * URI 数据对象。
 *
 * 此接口按照 RFC 3986 来构建 HTTP URI，提供了一些通用的操作，你可以自由的对此接口
 * 进行扩展。你可以使用此 URI 接口来做 HTTP 相关的操作，也可以使用此接口做任何 URI
 * 相关的操作。
 *
 * 此接口的实例化对象被视为无法修改的，所有能修改状态的方法，都 **必须** 有一套机制，在内部保
 * 持好原有的内容，然后把修改状态后的，新的实例返回。
 *
 * 通常，HOST 信息也将出现在请求消息中。对于服务器端的请求，通常可以在服务器参数中发现此信息。
 *
 * @see [URI 通用标准规范](http://tools.ietf.org/html/rfc3986)
 *
 * @package Raylin666\Http\Message
 */
class Uri implements UriInterface
{
    /**
     * 默认 HOST 地址
     */
    const DEFAULT_HOST = '::1';

    /**
     * 协议端口
     */
    const HTTP_PORT = 80;
    const HTTPS_PORT = 443;

    /**
     * 协议名称
     */
    const HTTP_SCHEME = 'http';
    const HTTPS_SCHEME = 'https';

    /**
     * 协议格式
     * @var string
     */
    protected $scheme = '';

    /**
     * 端口协议对应关系
     * @var array
     */
    protected $allowedSchemes = [
        self::HTTP_SCHEME  => self::HTTP_PORT,
        self::HTTPS_SCHEME => self::HTTPS_PORT,
    ];

    /**
     * URI 地址
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var string
     */
    protected $userInfo = '';

    /**
     * @var string
     */
    protected $user = '';

    /**
     * @var string
     */
    protected $pass = '';

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var string
     */
    protected $fragment = '';

    /**
     * @var string
     */
    protected $relationPath = '/';

    /**
     * @var string
     */
    protected $uriString = '';

    /**
     * Uri constructor.
     * @param string $uri
     */
    public function __construct(string $uri = '')
    {
        $this->uri = $uri;
        if (! empty($uri)) {
            $this->parseUri($uri);
        }
    }

    /**
     * 解析 URI
     * @param string $uri
     */
    protected function parseUri(string $uri)
    {
        $parts = parse_url($uri);

        if (false === $parts) {
            throw new InvalidArgumentException(
                'The source URI string appears to be malformed'
            );
        }

        $this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
        $this->host = $parts['host'] ?? self::DEFAULT_HOST;
        $this->userInfo = $this->user = $parts['user'] ?? '';
        if (isset($parts['port'])) {
            $this->port = intval($parts['port']);
        } elseif ($this->scheme == self::HTTPS_SCHEME) {
            $this->port = self::HTTPS_PORT;
        } else {
            $this->port = self::HTTP_PORT;
        }

        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '/';
        $this->query = isset($parts['query']) ? $this->filterQuery($parts['query']) : [];
        $this->fragment = isset($parts['fragment']) ? $this->filterFragment($parts['fragment']) : '';

        if (isset($parts['pass'])) {
            $this->pass = $parts['pass'];
            $this->userInfo .= ':' . $parts['pass'];
        }

        if (false !== $index = strpos($uri, '.php')) {
            if ($relationPath = substr($uri, ($index + 4))) {
                $this->relationPath = $relationPath;
            }
        }
    }

    /**
     * Filters the scheme to ensure it is a valid scheme.
     * @param string $scheme
     * @return string
     */
    protected function filterScheme(string $scheme): string
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);
        if (! array_key_exists($scheme, $this->allowedSchemes)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported scheme "%s"; must be any empty string or in the set (%s)',
                    $scheme,
                    implode(', ', array_keys($this->allowedSchemes)
                    )
            ));
        }

        return $scheme ? : '';
    }

    /**
     * Filters the path of a URI to ensure it is properly encoded.
     *
     * @param $path
     * @return string|string[]|null
     */
    protected function filterPath($path)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'urlEncodeChar'],
            $path
        );
    }

    /**
     * Filter a query string to ensure it is propertly encoded.
     *
     * Ensures that the values in the query string are properly urlencoded.
     *
     * @param $query
     * @return array
     */
    protected function filterQuery($query): array
    {
        $queryInfo = [];

        foreach (explode('&', $query) as $part) {
            list($name, $value) = explode('=', (false === strpos($part, '=') ? "{$part}=" : $part), 2);

            $name = rawurldecode($name);
            $value = rawurldecode($value);

            if (0 === preg_match_all('/\[([^\]]*)\]/m', $name, $matches)) {
                $queryInfo[$name] = $value;
                continue;
            }

            $name   = substr($name, 0, strpos($name, '['));
            $keys   = array_merge([$name], $matches[1]);
            $target = &$queryInfo;

            foreach ($keys as $index) {
                if ('' === $index) {
                    $target = &$target[];
                } else {
                    $target = &$target[$index];
                }
            }

            $target = $value;
        }

        return $queryInfo;
    }

    /**
     * Filter a fragment value to ensure it is properly encoded.
     *
     * @param $fragment
     * @return mixed
     */
    protected function filterFragment($fragment)
    {
        if (null === $fragment) {
            $fragment = '';
        }

        if (!empty($fragment) && strpos($fragment, '#') === 0) {
            $fragment = substr($fragment, 1);
        }

        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'urlEncodeChar'],
            $fragment
        );
    }

    /**
     * URL encode a character returned by a regex.
     *
     * @param array $matches
     * @return string
     */
    private function urlEncodeChar(array $matches): string
    {
        return rawurlencode($matches[0]);
    }

    /**
     * 从 URI 中取出 scheme。
     *
     * 如果不存在 Scheme，此方法 **必须** 返回空字符串。
     *
     * 根据 RFC 3986 规范 3.1 章节，返回的数据 **必须** 是小写字母。
     *
     * 最后部分的「:」字串不属于 Scheme，**不得** 作为返回数据的一部分。
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string URI Ccheme 的值。
     */
    public function getScheme(): string
    {
        // TODO: Implement getScheme() method.

        return strtolower($this->scheme);
    }

    /**
     * 返回 URI 认证信息。
     *
     * 如果没有 URI 认证信息的话，**必须** 返回一个空字符串。
     *
     * URI 的认证信息语法是：
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * 如果端口部分没有设置，或者端口不是标准端口，**不应该** 包含在返回值内。
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string URI 认证信息，格式为：「[user-info@]host[:port]」。
     */
    public function getAuthority(): string
    {
        // TODO: Implement getAuthority() method.

        if (empty($this->host)) {
            return '';
        }

        $authority = $this->host;
        $authority = (! empty($this->userInfo)) ? $this->userInfo . '@' . $authority : $authority;
        $authority .= $this->isNonStandardPort() ? sprintf(':%d', $this->port) : '';
        return $authority;
    }

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @return bool
     */
    protected function isNonStandardPort(): bool
    {
        return ! in_array($this->port, array_values($this->allowedSchemes));
    }

    /**
     * 从 URI 中获取用户信息。
     *
     * 如果不存在用户信息，此方法 **必须** 返回一个空字符串。
     *
     * 如果 URI 中存在用户，则返回该值；此外，如果密码也存在，它将附加到用户值，用冒号（「:」）分隔。
     *
     * 用户信息后面跟着的 "@" 字符，不是用户信息里面的一部分，**不得** 在返回值里出现。
     *
     * @return string URI 的用户信息，格式："username[:password]"
     */
    public function getUserInfo(): string
    {
        // TODO: Implement getUserInfo() method.

        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPass(): string
    {
        return $this->pass;
    }

    /**
     * 从 URI 中获取 HOST 信息。
     *
     * 如果 URI 中没有此值，**必须** 返回空字符串。
     *
     * 根据 RFC 3986 规范 3.2.2 章节，返回的数据 **必须** 是小写字母。
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string URI 中的 HOST 信息。
     */
    public function getHost(): string
    {
        // TODO: Implement getHost() method.

        return $this->host;
    }

    /**
     * 从 URI 中获取端口信息。
     *
     * 如果端口信息是与当前 Scheme 的标准端口不匹配的话，就使用整数值的格式返回，如果是一
     * 样的话，**应该** 返回 `null` 值。
     *
     * 如果不存在端口和 Scheme 信息，**必须** 返回 `null` 值。
     *
     * 如果不存在端口数据，但是存在 Scheme 的话，**可能** 返回 Scheme 对应的
     * 标准端口，但是 **应该** 返回 `null`。
     *
     * @return int URI 中的端口信息。
     */
    public function getPort(): int
    {
        // TODO: Implement getPort() method.

        return intval($this->port);
    }

    /**
     * 从 URI 中获取路径信息。
     *
     * 路径可以是空的，或者是绝对的（以斜线「/」开头），或者相对路径（不以斜线开头）。
     * 实现 **必须** 支持所有三种语法。
     *
     * 根据 RFC 7230 第 2.7.3 节，通常空路径「」和绝对路径「/」被认为是相同的。
     * 但是这个方法 **不得** 自动进行这种规范化，因为在具有修剪的基本路径的上下文中，
     * 例如前端控制器中，这种差异将变得显著。用户的任务就是可以将「」和「/」都处理好。
     *
     * 返回的值 **必须** 是百分号编码，但 **不得** 对任何字符进行双重编码。
     * 要确定要编码的字符，请参阅 RFC 3986 第 2 节和第 3.3 节。
     *
     * 例如，如果值包含斜线（「/」）而不是路径段之间的分隔符，则该值必须以编码形式（例如「%2F」）
     * 传递给实例。
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string URI 路径信息。
     */
    public function getPath(): string
    {
        // TODO: Implement getPath() method.

        return $this->path;
    }

    /**
     * 获取 URI 中的查询字符串。
     *
     * 如果不存在查询字符串，则此方法必须返回空字符串。
     *
     * 前导的「?」字符不是查询字符串的一部分，**不得** 添加在返回值中。
     *
     * 返回的值 **必须** 是百分号编码，但 **不得** 对任何字符进行双重编码。
     * 要确定要编码的字符，请参阅 RFC 3986 第 2 节和第 3.4 节。
     *
     * 例如，如果查询字符串的键值对中的值包含不做为值之间分隔符的（「&」），则该值必须
     * 以编码形式传递（例如「%26」）到实例。
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string URI 中的查询字符串| 该改为数组形式返回
     */
    public function getQuery(): array
    {
        // TODO: Implement getQuery() method.

        return $this->query;
    }

    /**
     * 获取 URI 中的片段（Fragment）信息。
     *
     * 如果没有片段信息，此方法 **必须** 返回空字符串。
     *
     * 前导的「#」字符不是片段的一部分，**不得** 添加在返回值中。
     *
     * 返回的值 **必须** 是百分号编码，但 **不得** 对任何字符进行双重编码。
     * 要确定要编码的字符，请参阅 RFC 3986 第 2 节和第 3.5 节。
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string URI 中的片段信息。
     */
    public function getFragment(): string
    {
        // TODO: Implement getFragment() method.

        return $this->fragment;
    }

    /**
     * 返回具有指定 Scheme 的实例。
     *
     * 此方法 **必须** 保留当前实例的状态，并返回包含指定 Scheme 的实例。
     *
     * 实现 **必须** 支持大小写不敏感的「http」和「https」的 Scheme，并且在
     * 需要的时候 **可能** 支持其他的 Scheme。
     *
     * 空的 Scheme 相当于删除 Scheme。
     *
     * @param string $scheme 给新实例使用的 Scheme。
     * @return self 具有指定 Scheme 的新实例。
     * @throws \InvalidArgumentException 使用无效的 Scheme 时抛出。
     * @throws \InvalidArgumentException 使用不支持的 Scheme 时抛出。
     */
    public function withScheme($scheme): Uri
    {
        // TODO: Implement withScheme() method.

        $scheme = $this->filterScheme($scheme);
        if ($scheme !== $this->scheme) {
            $this->scheme = $scheme;
        }

        return $this;
    }

    /**
     * 返回具有指定用户信息的实例。
     *
     * 此方法 **必须** 保留当前实例的状态，并返回包含指定用户信息的实例。
     *
     * 密码是可选的，但用户信息 **必须** 包括用户；用户信息的空字符串相当于删除用户信息。
     *
     * @param string $user 用于认证的用户名。
     * @param null|string $password 密码。
     * @return self 具有指定用户信息的新实例。
     */
    public function withUserInfo($user, $password = null): Uri
    {
        // TODO: Implement withUserInfo() method.

        $this->user = strval($user);
        $this->pass = strval($password);
        if ($password) {
            $user .= ':' . $password;
        }

        if ($user !== $this->userInfo) {
            $this->userInfo = $user;
        }

        return $this;
    }

    /**
     * 返回具有指定 HOST 信息的实例。
     *
     * 此方法 **必须** 保留当前实例的状态，并返回包含指定 HOST 信息的实例。
     *
     * 空的 HOST 信息等同于删除 HOST 信息。
     *
     * @param string $host 用于新实例的 HOST 信息。
     * @return self 具有指定 HOST 信息的实例。
     * @throws \InvalidArgumentException 使用无效的 HOST 信息时抛出。
     */
    public function withHost($host): Uri
    {
        // TODO: Implement withHost() method.

        if ($host !== $this->host) {
            $this->host = $host;
        }

        return $this;
    }

    /**
     * 返回具有指定端口的实例。
     *
     * 此方法 **必须** 保留当前实例的状态，并返回包含指定端口的实例。
     *
     * 实现 **必须** 为已建立的 TCP 和 UDP 端口范围之外的端口引发异常。
     *
     * 为端口提供的空值等同于删除端口信息。
     *
     * @param null|int $port 用于新实例的端口；`null` 值将删除端口信息。
     * @return self 具有指定端口的实例。
     * @throws \InvalidArgumentException 使用无效端口时抛出异常。
     */
    public function withPort($port): Uri
    {
        // TODO: Implement withPort() method.

        if (!(is_integer($port) || (is_string($port) && is_numeric($port)))) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid port "%s" specified; must be an integer or integer string',
                    (is_object($port) ? get_class($port) : gettype($port))
                )
            );
        }

        $port = intval($port);

        if ($port !== $this->port) {
            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid port "%d" specified; must be a valid TCP/UDP port',
                        $port
                    )
                );
            }

            $this->port = $port;
        }

        return $this;
    }

    /**
     * 返回具有指定路径的实例。
     *
     * 此方法 **必须** 保留当前实例的状态，并返回包含指定路径的实例。
     *
     * 路径可以是空的、绝对的（以斜线开头）或者相对路径（不以斜线开头），实现必须支持这三种语法。
     *
     * 如果 HTTP 路径旨在与 HOST 相对而不是路径相对，，那么它必须以斜线开头。
     * 假设 HTTP 路径不以斜线开头，对应该程序或开发人员来说，相对于一些已知的路径。
     *
     * 用户可以提供编码和解码的路径字符，要确保实现了 `getPath()` 中描述的正确编码。
     *
     * @param string $path 用于新实例的路径。
     * @return self 具有指定路径的实例。
     * @throws \InvalidArgumentException 使用无效的路径时抛出。
     */
    public function withPath($path): Uri
    {
        // TODO: Implement withPath() method.

        if (! is_string($path)) {
            throw new InvalidArgumentException(
                'Invalid path provided; must be a string'
            );
        }

        if (strpos($path, '?') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );
        }

        if (strpos($path, '#') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a URI fragment'
            );
        }

        $path = $this->filterPath($path);
        if ($path !== $this->path) {
            $this->path = $path;
        }

        return $this;
    }

    /**
     * 返回具有指定查询字符串的实例。
     *
     * 此方法 **必须** 保留当前实例的状态，并返回包含查询字符串的实例。
     *
     * 用户可以提供编码和解码的查询字符串，要确保实现了 `getQuery()` 中描述的正确编码。
     *
     * 空查询字符串值等同于删除查询字符串。
     *
     * @param string $query 用于新实例的查询字符串。
     * @return self 具有指定查询字符串的实例。
     * @throws \InvalidArgumentException 使用无效的查询字符串时抛出。
     */
    public function withQuery($query): Uri
    {
        // TODO: Implement withQuery() method.

        if (! is_string($query)) {
            throw new InvalidArgumentException(
                'Query string must be a string'
            );
        }

        if (strpos($query, '#') !== false) {
            throw new InvalidArgumentException(
                'Query string must not include a URI fragment'
            );
        }

        $query = $this->filterQuery($query);
        if ($query !== $this->query) {
            $this->query = $query;
        }

        return $this;
    }

    /**
     * 返回具有指定 URI 片段（Fragment）的实例。
     *
     * 此方法 **必须** 保留当前实例的状态，并返回包含片段的实例。
     *
     * 用户可以提供编码和解码的片段，要确保实现了 `getFragment()` 中描述的正确编码。
     *
     * 空片段值等同于删除片段。
     *
     * @param string $fragment 用于新实例的片段。
     * @return self 具有指定 URI 片段的实例。
     */
    public function withFragment($fragment): Uri
    {
        // TODO: Implement withFragment() method.

        $fragment = $this->filterFragment($fragment);
        if ($fragment !== $this->fragment) {
            $this->fragment = $fragment;
        }

        return $this;
    }

    /**
     * 返回字符串表示形式的 URI。
     *
     * 根据 RFC 3986 第 4.1 节，结果字符串是完整的 URI 还是相对引用，取决于 URI 有哪些组件。
     * 该方法使用适当的分隔符连接 URI 的各个组件：
     *
     * - 如果存在 Scheme 则 **必须** 以「:」为后缀。
     * - 如果存在认证信息，则必须以「//」作为前缀。
     * - 路径可以在没有分隔符的情况下连接。但是有两种情况需要调整路径以使 URI 引用有效，因为 PHP
     *   不允许在 `__toString()` 中引发异常：
     *     - 如果路径是相对的并且有认证信息，则路径 **必须** 以「/」为前缀。
     *     - 如果路径以多个「/」开头并且没有认证信息，则起始斜线 **必须** 为一个。
     * - 如果存在查询字符串，则 **必须** 以「?」作为前缀。
     * - 如果存在片段（Fragment），则 **必须** 以「#」作为前缀。
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.

        if (empty($this->uriString)) {
            $this->uriString = self::createUriString(
                $this->scheme,
                $this->getAuthority(),
                $this->getPath(), // Absolute URIs should use a "/" for an empty path
                $this->query,
                $this->fragment
            );
        }

        return $this->uriString;
    }

    /**
     * Create a URI string from its various parts
     *
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param array $query
     * @param string $fragment
     * @return string
     */
    public static function createUriString(string $scheme, string $authority, string $path, array $query, string $fragment): string
    {
        $uri = '';
        if (! empty($scheme)) {
            $uri .= sprintf('%s://', $scheme);
        }

        if (! empty($authority)) {
            $uri .= $authority;
        }

        if ($path) {
            if (empty($path) || '/' !== substr($path, 0, 1)) {
                $path = '/' . $path;
            }

            $uri .= $path;
        }

        if ($query) {
            $uri .= sprintf('?%s', http_build_query($query));
        }

        if ($fragment) {
            $uri .= sprintf('#%s', $fragment);
        }

        return $uri;
    }
}