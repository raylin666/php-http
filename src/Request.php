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

namespace Raylin666\Http;

use Psr\Http\Message\StreamInterface;
use Raylin666\Http\Message\ServerRequest;

/**
 * Class Request
 * @package Raylin666\Http
 */
class Request extends ServerRequest
{
    /**
     * @var array
     */
    private $parsedPostBodyMethod = [
        ServerRequest::METHOD_PUT,
        ServerRequest::METHOD_DELETE,
        ServerRequest::METHOD_PATCH,
        ServerRequest::METHOD_OPTIONS,
    ];

    /**
     * Request constructor.
     * @param                      $method
     * @param                      $uri
     * @param array                $headers
     * @param StreamInterface|null $body
     * @param array                $serverParams
     */
    public function __construct(
        $method,
        $uri,
        array $headers = [],
        StreamInterface $body = null,
        array $serverParams = []
    )
    {
        parent::__construct($uri, $method, $headers, $body);

        $this
            ->withServerParams($serverParams)
            ->withCookieParams($_COOKIE)
            ->withQueryParams($this->uri->getQuery())
            ->withParsedBody($_POST)
            ->withUploadedFiles($_FILES);

        if (in_array(strtoupper($method), $this->parsedPostBodyMethod)) {
            parse_str((string) $body, $data);
            if (empty($data)) {
                $data = json_decode((string) $body);
            }
            $this->withParsedBody($data);
        }
    }
}