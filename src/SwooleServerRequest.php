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

use Raylin666\Http\Message\Uri;
use swoole_http_request;

/**
 * Class SwooleServerRequest
 * @package Raylin666\Http
 */
class SwooleServerRequest extends Request
{
    /**
     * @param swoole_http_request $request
     * @return Request
     */
    public static function createServerRequestFromSwoole(swoole_http_request $request): Request
    {
        $get = isset($request->get) ? $request->get : [];
        $post = isset($request->post) ? $request->post : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];
        $files = isset($request->files) ? $request->files : [];

        $host = Uri::DEFAULT_HOST;
        foreach (['host', 'server_addr'] as $name) {
            if (! empty($request->header[$name])) {
                $host = parse_url($request->header[$name], PHP_URL_HOST) ?: $request->header[$name];
            }
        }

        $server = [
            'REQUEST_METHOD'        =>  $request->server['request_method'],
            'REQUEST_URI'           =>  $request->server['request_uri'],
            'PATH_INFO'             =>  $request->server['path_info'],
            'REQUEST_TIME'          =>  $request->server['request_time'],
            'REQUEST_TIME_FLOAT'    =>  $request->server['request_time_float'],
            'GATEWAY_INTERFACE'     =>  'swoole/' . SWOOLE_VERSION,

            // Server
            'SERVER_PROTOCOL'       =>  isset($request->header['server_protocol']) ? $request->header['server_protocol'] : $request->server['server_protocol'],
            'REQUEST_SCHEMA'        =>  isset($request->header['request_scheme']) ? $request->header['request_scheme'] : explode('/', $request->server['server_protocol'])[0],
            'SERVER_NAME'           =>  isset($request->header['server_name']) ? $request->header['server_name'] : $host,
            'SERVER_ADDR'           =>  $host,
            'SERVER_PORT'           =>  isset($request->header['server_port']) ? $request->header['server_port'] : $request->server['server_port'],
            'REMOTE_ADDR'           =>  $host,
            'REMOTE_PORT'           =>  isset($request->header['remote_port']) ? $request->header['remote_port'] : $request->server['remote_port'],
            'QUERY_STRING'          =>  isset($request->server['query_string']) ? $request->server['query_string'] : '',

            // Headers
            'HTTP_HOST'             =>  $host,
            'HTTP_USER_AGENT'       =>  isset($request->header['user-agent']) ? $request->header['user-agent'] : '',
            'HTTP_ACCEPT'           =>  isset($request->header['accept']) ? $request->header['accept'] : '*/*',
            'HTTP_ACCEPT_LANGUAGE'  =>  isset($request->header['accept-language']) ? $request->header['accept-language'] : '',
            'HTTP_ACCEPT_ENCODING'  =>  isset($request->header['accept-encoding']) ? $request->header['accept-encoding'] : '',
            'HTTP_CONNECTION'       =>  isset($request->header['connection']) ? $request->header['connection'] : '',
            'HTTP_CACHE_CONTROL'    =>  isset($request->header['cache-control']) ? $request->header['cache-control'] : '',
        ];

        $headers = [];
        foreach ($request->header as $name => $value) {
            $headers[str_replace('-', '_', $name)] = $value;
        }

        $serverRequest = new Request(
            $server['REQUEST_METHOD'],
            static::createUriFromGlobal($server),
            $headers,
            null,
            $server
        );

        unset($headers);

        $serverRequest->getBody()->write($request->rawContent());

        return $serverRequest
            ->withParsedBody($post)
            ->withQueryParams($get)
            ->withCookieParams($cookie)
            ->withUploadedFiles($files);
    }
}