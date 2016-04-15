<?php

namespace PicodiLab\Expertsender\Method;

use PicodiLab\Expertsender\Exception\InvalidExpertsenderApiRequestException;
use PicodiLab\Expertsender\Mapper;

class Lists extends AbstractMethod
{
    const METHOD_Lists = 'Lists';


    /**
     * creates new subscriber list
     * @param $name
     * @param array $params
     * @return bool
     * @throws InvalidExpertsenderApiRequestException
     * @throws MethodInMapperNotFoundException
     */
    public function create($name, $params = [])
    {

        $defaultParams = [
            'Name' => $name,
            'FriendlyName' => null,
            'Description' => null,
            'Language' => null,
            'OptInMode' => null,
            // ... not necessary now.
        ];

        $params = array_merge($defaultParams, $params);

        $requestUrl = $this->buildApiUrl(self::METHOD_Lists);
        $requestBody = $this->render('Lists/Lists', array_merge(['settings' => $params], [
            'apiKey' => $this->connection->getKey(),
        ]));

        $response = $this->connection->post($requestUrl, $requestBody);

        $ok = $this->connection->isResponseValid($response);

        return (boolean)$ok;
    }


    /**
     *
     * gets subscribers lists
     * @param array $params
     * @param bool $raw
     * @return array
     * @throws MethodInMapperNotFoundException
     * @throws InvalidExpertsenderApiRequestException
     */
    public function get($params = [], $raw = false)
    {
        $requestUrl = $this->buildApiUrl(self::METHOD_Lists);

        $defaultParams = [
            'apiKey' => $this->connection->getKey(),
            'http_errors' => false,
            'query' => [],
        ];

        $params = array_merge($defaultParams, $params);
        $response = $this->connection->get($requestUrl, $params);

        if (!$raw) {
            $this->connection->isResponseValid($response);

            $rXml = $this->connection->prepareResponse($response);
            $lists = array_values((array)json_decode(json_encode($rXml->xpath('//Data/Lists')[0]), true));
            return $lists;
        }

        return $response->getBody();
    }

}