<?php

namespace PicodiLab\Expertsender\Method;

use PicodiLab\Expertsender\Exception\InvalidExpertsenderApiRequestException;
use PicodiLab\Expertsender\Mapper;
use PicodiLab\Expertsender\ResponseFormatter\XmlBased;

class Lists extends AbstractMethod
{

    use XmlBased;
    
    const METHOD_Lists = 'Lists';

    protected $mapperName = 'SubscribersList';


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
        $requestBody = $this->renderRequestBody('Lists/Lists', array_merge(['settings' => $params], [
            'apiKey' => $this->connection->getKey(),
        ]));

        $response = $this->connection->post($requestUrl, $requestBody);

        $this->connection->isResponseValid($response);

        return $this->formatResponse($response);
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
    public function get($params = [])
    {
        $requestUrl = $this->buildApiUrl(self::METHOD_Lists);

        $defaultParams = [
            'apiKey' => $this->connection->getKey(),
            'http_errors' => false,
            'query' => [],
        ];

        $params = array_merge($defaultParams, $params);
        $response = $this->connection->get($requestUrl, $params);

        $this->connection->isResponseValid($response);
        return $this->formatResponse($response);
    }

}