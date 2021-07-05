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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class ServerRequest
 *
 * 表示服务器端接收到的 HTTP 请求。
 *
 * 根据 HTTP 规范，此接口包含以下属性：
 *
 * - HTTP 协议版本号
 * - HTTP 请求方法
 * - URI
 * - 报头信息
 * - 消息内容
 *
 * 此外，它封闭了从 CGI 和/或 PHP 环境变量，包括：
 *
 * - `$_SERVER` 中表示的值。
 * - 提供的任意 Cookie 信息（通常通过 `$_COOKIE` 获取）
 * - 查询字符串参数（通常通过 `$_GET` 获取，或者通过 `parse_str()` 解析）
 * - 如果存在的话，上传文件的信息（通常通过 `$_FILES` 获取）
 * - 反序列化的消息体参数（通常来自于 `$_POST`）
 *
 * `$_SERVER` 的值 **必须** 被视为不可变的，因为代表了请求时应用程序的状态；因此，没有允许修改的方法。
 * 其他值则提供了修改的方法，因为可以从 `$_SERVER` 或请求体中恢复，并且可能在应用程序中被处理
 * （比如可能根据内容类型对消息体参数进行反序列化）。
 *
 * 此外，这个接口要识别请求的扩展信息和匹配其他的参数。
 * （例如，通过 URI 进行路径匹配，解析 Cookie 值，反序列化非表单编码的消息体，报头中的用户名进行匹配认证）
 * 这些参数存储在「attributes」中。
 *
 * HTTP 请求是被视为无法修改的，所有能修改状态的方法，都 **必须** 有一套机制，在内部保
 * 持好原有的内容，然后把修改状态后的，新的 HTTP 请求实例返回。
 *
 * @package Raylin666\Http\Message
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @var array
     */
    public $cookieParams = [];

    /**
     * @var array
     */
    public $queryParams = [];

    /**
     * @var array
     */
    public $bodyParams = [];

    /**
     * @var array
     */
    public $serverParams = [];

    /**
     * @var array
     */
    public $uploadFiles = [];

    /**
     * ServerRequest constructor.
     * @param null                 $uri
     * @param string               $method
     * @param array                $headers
     * @param StreamInterface|null $body
     */
    public function __construct($uri = null, $method = self::METHOD_GET, array $headers = [], StreamInterface $body = null)
    {
        parent::__construct($uri, $method, $headers, $body);
    }

    /**
     * 返回服务器参数。
     *
     * 返回与请求环境相关的数据，通常从 PHP 的 `$_SERVER` 超全局变量中获取，但不是必然的。
     *
     * @return array
     */
    public function getServerParams(): array
    {
        // TODO: Implement getServerParams() method.

        return $this->serverParams;
    }

    /**
     * @param array $serverParams
     * @return ServerRequest
     */
    public function withServerParams(array $serverParams = []): ServerRequest
    {
        if (empty($this->headers)) {
            array_walk($serverParams, function ($value, $key) {
                if (0 === strpos($key, 'HTTP_')) {
                    $this->withAddedHeader(str_replace('HTTP_', '', $key), $value);
                }
            });
        }

        $this->serverParams = $serverParams;
        return $this;
    }

    /**
     * 获取 Cookie 数据。
     *
     * 获取从客户端发往服务器的 Cookie 数据。
     *
     * 这个数据的结构 **必须** 和超全局变量 `$_COOKIE` 兼容。
     *
     * @return array
     */
    public function getCookieParams(): array
    {
        // TODO: Implement getCookieParams() method.

        return $this->cookieParams;
    }

    /**
     * 返回具体指定 Cookie 的实例。
     *
     * 这个数据不是一定要来源于 `$_COOKIE`，但是 **必须** 与之结构兼容。通常在实例化时注入。
     *
     * 这个方法 **禁止** 更新实例中的 Cookie 报头和服务器参数中的相关值。
     *
     * 此方法在实现的时候，**必须** 保留原有的不可修改的 HTTP 消息实例，然后返回
     * 一个新的修改过的 HTTP 消息实例。
     *
     * @param array $cookies 表示 Cookie 的键值对。
     * @return self
     */
    public function withCookieParams(array $cookies): ServerRequest
    {
        // TODO: Implement withCookieParams() method.

        foreach ($cookies as $name => $value) {
            $this->cookieParams[$name] = $value;
        }

        return $this;
    }

    /**
     * 获取查询字符串参数。
     *
     * 如果可以的话，返回反序列化的查询字符串参数。
     *
     * 注意：查询参数可能与 URI 或服务器参数不同步。如果你需要确保只获取原始值，则可能需要调用
     * `getUri()->getQuery()` 或服务器参数中的 `QUERY_STRING` 获取原始的查询字符串并自行解析。
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        // TODO: Implement getQueryParams() method.

        return $this->queryParams;
    }

    /**
     * 返回具体指定查询字符串参数的实例。
     *
     * 这些值 **应该** 在传入请求的闭包中保持不变。它们 **可能** 在实例化的时候注入，
     * 例如来自 `$_GET` 或者其他一些值（例如 URI）中得到。如果是通过解析 URI 获取，则
     * 数据结构必须与 `parse_str()` 返回的内容兼容，以便处理查询参数、嵌套的代码可以复用。
     *
     * 设置查询字符串参数 **不得** 更改存储的 URI 和服务器参数中的值。
     *
     * 此方法在实现的时候，**必须** 保留原有的不可修改的 HTTP 消息实例，然后返回
     * 一个新的修改过的 HTTP 消息实例。
     *
     * @param array $query 查询字符串参数数组，通常来源于 `$_GET`。
     * @return self
     */
    public function withQueryParams(array $query): ServerRequest
    {
        // TODO: Implement withQueryParams() method.

        foreach ($query as $name => $value) {
            $this->queryParams[$name] = $value;
        }

        return $this;
    }

    /**
     * 获取规范化的上传文件数据。
     *
     * 这个方法会规范化返回的上传文件元数据树结构，每个叶子结点都是 `Psr\Http\Message\UploadedFileInterface` 实例。
     *
     * 这些值 **可能** 在实例化的时候从 `$_FILES` 或消息体中获取，或者通过 `withUploadedFiles()` 获取。
     *
     * @return array `UploadedFileInterface` 的实例数组；如果没有数据则必须返回一个空数组。
     */
    public function getUploadedFiles(): array
    {
        // TODO: Implement getUploadedFiles() method.

        return $this->uploadFiles;
    }

    /**
     * 返回使用指定的上传文件数据的新实例。
     *
     * 此方法在实现的时候，**必须** 保留原有的不可修改的 HTTP 消息实例，然后返回
     * 一个新的修改过的 HTTP 消息实例。
     *
     * @param array `UploadedFileInterface` 实例的树结构，类似于 `getUploadedFiles()` 的返回值。
     * @return self
     * @throws \InvalidArgumentException 如果提供无效的结构时抛出。
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequest
    {
        // TODO: Implement withUploadedFiles() method.

        $this->uploadFiles = $this->uploadedFilesNormalizer($uploadedFiles);
        return $this;
    }

    /**
     * @param $files
     * @return array
     */
    protected function uploadedFilesNormalizer($files)
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (! is_array($value['name'])) {
                $normalized[$key] = $this->uploadedFileNormalizer($value);
            } elseif (is_array($value['name'])) {
                $array = [];
                foreach ($value['name'] as $index => $item) {
                    if (empty($item)) {
                        continue;
                    }
                    $array[] = $this->uploadedFileNormalizer([
                        'name'          =>      $value['name'][$index],
                        'type'          =>      $value['type'][$index],
                        'tmp_name'      =>      $value['tmp_name'][$index],
                        'error'         =>      $value['error'][$index],
                        'size'          =>      $value['size'][$index],
                    ]);
                }
                $normalized[$key] = $array;
                continue;
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * @param array $file
     * @return UploadedFile
     */
    private function uploadedFileNormalizer(array $file): UploadedFile
    {
        return new UploadedFile($file['name'], $file['type'], $file['tmp_name'], (int) $file['error'], (int) $file['size']);
    }

    /**
     * 获取请求消息体中的参数。
     *
     * 如果请求的 Content-Type 是 application/x-www-form-urlencoded 或 multipart/form-data 且请求方法是 POST，
     * 则此方法 **必须** 返回 $_POST 的内容。
     *
     * 如果是其他情况，此方法可能返回反序列化请求正文内容的任何结果；当解析返回返回的结构化内容时，潜在的类型 **必须**
     * 只能是数组或 `object` 类型。`null` 表示没有消息体内容。
     *
     * @return null|array|object 如果存在则返回反序列化消息体参数。一般是一个数组或 `object`。
     */
    public function getParsedBody()
    {
        // TODO: Implement getParsedBody() method.

        return $this->bodyParams;
    }

    /**
     * 返回具有指定消息体参数的实例。
     *
     * **可能** 在实例化时注入。
     *
     * 如果请求的 Content-Type 是 application/x-www-form-urlencoded 或 multipart/form-data 且请求方法是 POST，
     * 则方法的参数只能是 $_POST。
     *
     * 数据不一定要来自 $_POST，但是 **必须** 是反序列化请求正文内容的结果。由于需要反序列化/解析返回的结构化数据，
     * 所以这个方法只接受数组、 `object` 类型和 `null`（如果没有可用的数据解析）。
     *
     * 例如，如果确定请求数据是一个 JSON，可以使用此方法创建具有反序列化参数的请求实例。
     *
     * 此方法在实现的时候，**必须** 保留原有的不可修改的 HTTP 消息实例，然后返回
     * 一个新的修改过的 HTTP 消息实例。
     *
     * @param null|array|object $data 反序列化的消息体数据，通常是数组或 `object`。
     * @return self
     * @throws \InvalidArgumentException 如果提供的数据类型不支持。
     */
    public function withParsedBody($data): ServerRequest
    {
        // TODO: Implement withParsedBody() method.

        $this->bodyParams = $data;
        return $this;
    }

    /**
     * 获取从请求派生的属性。
     *
     * 请求「attributes」可用于从请求导出的任意参数：比如路径匹配操作的结果；解密 Cookie 的结果；
     * 反序列化非表单编码的消息体的结果；属性将是应用程序与请求特定的，并且可以是可变的。
     *
     * @return mixed[] 从请求派生的属性。
     */
    public function getAttributes()
    {
        // TODO: Implement getAttributes() method.

        return $this->attributes;
    }
    /**
     * 获取单个派生的请求属性。
     *
     * 获取 getAttributes() 中声明的某一个属性，如果不存在则返回提供的默认值。
     *
     * 这个方法不需要 hasAttribute 方法，因为允许在找不到指定属性的时候返回默认值。
     *
     * @see getAttributes()
     * @param string $name 属性名称。
     * @param mixed $default 如果属性不存在时返回的默认值。
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        // TODO: Implement getAttribute() method.

        return (! array_key_exists($name, $this->attributes)) ? $default : $this->attributes[$name];
    }

    /**
     * 返回具有指定派生属性的实例。
     *
     * 此方法允许设置 getAttributes() 中声明的单个派生的请求属性。
     *
     * 此方法在实现的时候，**必须** 保留原有的不可修改的 HTTP 消息实例，然后返回
     * 一个新的修改过的 HTTP 消息实例。
     *
     * @see getAttributes()
     * @param string $name 属性名。
     * @param mixed $value 属性值。
     * @return self
     */
    public function withAttribute($name, $value): ServerRequest
    {
        // TODO: Implement withAttribute() method.

        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * 返回移除指定属性的实例。
     *
     * 此方法允许移除 getAttributes() 中声明的单个派生的请求属性。
     *
     * 此方法在实现的时候，**必须** 保留原有的不可修改的 HTTP 消息实例，然后返回
     * 一个新的修改过的 HTTP 消息实例。
     *
     * @see getAttributes()
     * @param string $name 属性名。
     * @return self
     */
    public function withoutAttribute($name): ServerRequest
    {
        // TODO: Implement withoutAttribute() method.

        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }

        return $this;
    }

    /**
     * 获取客户端 IP
     * @return string
     */
    public function getClientIP(): string
    {
        $unknown = $ip = 'unknown';
        if (
            isset($this->serverParams['HTTP_X_FORWARDED_FOR'])
            && $this->serverParams['HTTP_X_FORWARDED_FOR']
            && strcasecmp($this->serverParams['HTTP_X_FORWARDED_FOR'], $unknown)
        ) {
            $ip = $this->serverParams['HTTP_X_FORWARDED_FOR'];
        } else if (
            isset($this->serverParams['REMOTE_ADDR'])
            && $this->serverParams['REMOTE_ADDR']
            && strcasecmp($this->serverParams['REMOTE_ADDR'], $unknown)
        ) {
            $ip = $this->serverParams['REMOTE_ADDR'];
        }

        if (false !== strpos($ip, ',')) {
            $ip = reset(explode(',', $ip));
        }

        return $ip;
    }

    /**
     * @param array $serverParams
     * @return string
     */
    public static function createUriFromGlobal(array $serverParams): string
    {
        $uri = sprintf('%s://', Uri::HTTP_SCHEME);
        if (isset($serverParams['REQUEST_SCHEME'])) {
            $uri = strtolower($serverParams['REQUEST_SCHEME']) . '://';
        } else {
            if (isset($serverParams['HTTPS']) && 'on' === $serverParams['HTTPS']) {
                $uri = sprintf('%s://', Uri::HTTPS_SCHEME);
            }
        }
        if (isset($serverParams['SERVER_NAME'])) {
            $uri .= $serverParams['SERVER_NAME'];
        } elseif (isset($serverParams['HTTP_HOST'])) {
            $uri .= $serverParams['HTTP_HOST'];
        }
        if (isset($serverParams['SERVER_PORT']) && ! empty($serverParams['SERVER_PORT'])) {
            if ( ! in_array($serverParams['SERVER_PORT'], [Uri::HTTP_PORT, Uri::HTTPS_PORT])) {
                $uri .= ':' . $serverParams['SERVER_PORT'];
            }
        }
        if (isset($serverParams['REQUEST_URI'])) {
            $requestUriParts = explode('?', $serverParams['REQUEST_URI']);
            $uri .= $requestUriParts[0];
            unset($requestUriParts);
        }
        if (isset($serverParams['QUERY_STRING']) && ! empty($serverParams['QUERY_STRING'])) {
            $uri .= '?' . $serverParams['QUERY_STRING'];
        }

        return $uri;
    }

    /**
     * Create a new server request from PHP globals.
     *
     * @return ServerRequestInterface
     */
    public static function createServerRequestFromGlobals()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        foreach ($headers as $name => $value) {
            unset($headers[$name]);
            $name = str_replace('-', '_', $name);
            $headers[$name] = $value;
        }

        return new static($method, static::createUriFromGlobal($_SERVER), $headers, new PhpInputStream(), $_SERVER);
    }

    /**
     * 设置重定向
     *
     * @param       $uri        不填则根据 new Request() 中的 uri 地址进行重定向
     * @param int   $status
     * @param array $headers
     * @return Response
     */
    public function setRedirect($uri = null, $status = Response::HTTP_FOUND, array $headers = [])
    {
        if (! $uri) {
            $getUri = $this->getUri();
            $uri = $getUri->getScheme() . '://' . $getUri->getHost() . '?' . http_build_query($getUri->getQuery());
        }

        return (new Response())->redirect($uri, $status, $headers);
    }
}