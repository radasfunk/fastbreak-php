<?php namespace Radasfunk\Fastbreak;

use GuzzleHttp\Client as GuzzleClient;
use Radasfunk\Fastbreak\FastbreakException;

class Client
{
   
    protected string $apiUrl;
    protected string $token;
    protected $guzzle;
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

        $this->guzzle = new GuzzleClient([
            'base_uri' => $this->apiUrl,
            'http_errors' => false
        ]);
    }


    public function baseRequest($method, $endpoint, $data = [])
    {

        $defaultData = [];

        $data = array_merge($defaultData, $data);
        $payload = [
            'headers' => [
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
                'FB-Channel' => $this->channel,
                'FB-User' => $this->user,
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'json' => $data
        ];

        if (in_array($method, ['GET', 'DELETE'])) {
            $payload['query'] = $data;
        }

        if (in_array($method, ['UPLOAD'])) {
            $payload['multipart'] = $data;
            $method = 'POST';
        }
        $response = $this->guzzle->request($method, $endpoint, $payload);

        $body = json_decode((string)$response->getBody(), true);

        $this->code = $response->getStatusCode();

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
