<?php

namespace PicodiLab\Expertsender;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class ExpertSenderApiConnection
{
    /** @var string api url */
    protected $url = 'https://api2.esv2.com/Api/';

    /** @var  string api key */
    protected $key;

    /** @var Client */
    protected $httpClient;

    protected $errorLog = [];

    public function __construct($key, $url)
    {
        $this->key = $key;

        if ($url) {
            $this->url = $url;
        }

        $this->httpClient = new Client(['base_uri' => $this->url]);
    }

    /**
     * gets the API URL
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * gets the API key
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * GET request
     * @param $method
     * @param array $data
     * @return ResponseInterface
     */
    public function get($method, array $data)
    {
        $data['apiKey'] = $this->key;

        $response = $this->httpClient->request('GET', $method, [
            'query' => $data,
            'http_errors' => false,
        ]);

        return $response;
    }

    /**
     * POST method
     * @param $method
     * @param $requestBody
     * @return ResponseInterface
     */
    public function post($method, $requestBody, $prepareResonse = false)
    {

        $request = new Request('POST', $method, ['content-type' => 'text/xml'], $requestBody);
        $response = $this->httpClient->send($request, ['http_errors' => false]);

        return $response;

    }

    /**
     * DELETE method
     * @param $method
     * @param $data
     * @return ResponseInterface
     */
    public function delete($method, array $data)
    {
        $response = $this->httpClient->delete($method, [
            'query' => $data,
            'http_errors' => false,
        ]);

        return $response;

    }

    /**
     * Convert response to object
     * @param ResponseInterface $response
     * @return \SimpleXMLElement
     */
    public function prepareResponse(ResponseInterface $response)
    {
        $responseBody = (string)$response->getBody();
        if ($responseBody) {
            return simplexml_load_string($responseBody);
        }
        return null;
    }

    public function isResponseValid(ResponseInterface $response)
    {
        $ok = preg_match('/^2..$/', (string)$response->getStatusCode());

        if (!$ok) {
            $rXml = $this->prepareResponse($response);
            if ($rXml instanceof \SimpleXMLElement) {
                $errorCode = (string)$rXml->xpath('//ErrorMessage/Code')[0];
                $errorMessage = (string)$rXml->xpath('//ErrorMessage/Message')[0];
                $this->errorLog[] = [
                    'code' => $errorCode, 'message' => $errorMessage
                ];
            }
            return false;
        }

        return true;
    }


    /**
     * @return array
     */
    public function getErrorLog()
    {
        return $this->errorLog;
    }

    /**
     * @return array
     */
    public function getLastError()
    {
        if(is_array($this->errorLog) && isset($this->errorLog[count($this->errorLog) - 1]))
            return $this->errorLog[count($this->errorLog) - 1];
        else
            return null;
    }

}
