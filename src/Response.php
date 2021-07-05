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

use Raylin666\Http\Message\Response as MessageResponse;

/**
 * Class Response
 * @package Raylin666\Http
 */
class Response extends MessageResponse
{
    /**
     * Json Response Format
     * @param null  $data
     * @param int   $status
     * @param array $headers
     * @return \Raylin666\Http\Message\Response
     */
    public function toJson($data = null, $status = Response::HTTP_OK, array $headers = []): Response
    {
        if (! is_null($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            $this->withContentType(Response::HEADER_CONTENTTYPE_JSON);
        }

        parent::__construct($data, $status, $headers);
        return $this;
    }

    /**
     * Redirect URI
     * @param       $uri
     * @param int   $status
     * @param array $headers
     * @return $this
     */
    public function toRedirect($uri, $status = Response::HTTP_FOUND, array $headers = []): Response
    {
        parent::__construct('', $status, $headers);
        $this->withLocation($uri);
        return $this;
    }
}