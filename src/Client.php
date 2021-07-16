<?php

namespace Radasfunk\Fastbreak;

use Curl\Curl;
use Radasfunk\Fastbreak\FastbreakException;

class Client
{

    protected string $apiUrl;
    protected string $token;
    protected $curl;
    protected $code;
    protected $errors;
    protected $headers = [];
    protected $body;
    protected $withExceptions = false;

    function __construct(
        $channel,
        $token,
        $user = null,
        $apiUrl = "https://api.fastbreak.tech/api/"
    ) {
        $this->channel = $channel;
        $this->token = $token;
        $this->user = $user;
        $this->apiUrl = $apiUrl;

        

        $this->headers = [
            'Accept' => 'application/json',
            'FB-Channel' => $this->channel,
            'Authorization' => 'Bearer ' . $this->token,
        ];

        if (is_null($this->user) !== true) {
            $this->headers['FB-User'] = $this->user;
        }

        
    }

    public function baseRequest($method, $endpoint, $payload = [])
    {

        $this->curl = new Curl($this->apiUrl);
        $this->curl->setHeaders($this->headers);

        $this->body = null;
        $this->errors = null;
        $this->code = null;

        $this->curl->{strtolower($method)}($endpoint, $payload);

        $body = json_decode($this->curl->getRawResponse(), true);

        $this->code = $this->curl->getHttpStatusCode();
        $this->body = $body;
        $this->errors = $this->body['errors'] ?? [];

        if ($this->hasErrors() && $this->withExceptions) {
            $this->throwException();
        }
        

        return $this;
    }

    public function get($endpoint, $params = [])
    {

        return  $this->baseRequest('GET', $endpoint, $params);
    }


    public function post($endpoint, $data = [])
    {

        return  $this->baseRequest('POST', $endpoint, $data);
    }

    public function put($endpoint, $data)
    {
        return $this->baseRequest('PUT', $endpoint, $data);
    }

    public function patch($endpoint, $data)
    {
        return $this->baseRequest('PATCH', $endpoint, $data);
    }


    public function delete($endpoint, $params = [])
    {
        return $this->baseRequest('DELETE', $endpoint, $params);
    }

    public function upload($endpoint, $data = [])
    {
        return  $this->baseRequest('UPLOAD', $endpoint, $data);
    }

    public function data()
    {
        return $this->body['data'] ?? [];
    }

    public function included()
    {
        return $this->body['included'] ?? [];
    }

    public function meta()
    {
        return $this->body['meta'] ?? [];
    }

    public function body()
    {
        return $this->body;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }
    public function errors()
    {
        return $this->errors;
    }


    public function error()
    {
        return $this->error[0] ?? null;
    }

    public function throwException()
    {
        throw new FastbreakException($this->error());
    }

    public function withExceptions()
    {
        $this->withExceptions = true;
        return $this;
    }
}
