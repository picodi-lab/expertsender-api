<?php

namespace PicodiLab\Expertsender\Method;

use GuzzleHttp\Psr7\Response;
use PicodiLab\Expertsender\Exception\InvalidExpertsenderApiRequestException;
use PicodiLab\Expertsender\Exception\MethodInMapperNotFoundException;
use PicodiLab\Expertsender\Mapper;
use PicodiLab\Expertsender\Mapper\Activity;

class Activities extends AbstractMethod
{

    const METHOD_Activities = 'Activities';

    protected $mapperName = 'Activity';

    /**
     *
     * get subscriber activities
     *
     * @param $type
     * @param $date
     * @param array $params
     * @param string $format
     * @return string
     * @throws InvalidExpertsenderApiRequestException
     * @throws \PicodiLab\Expertsender\Method\MethodInMapperNotFoundException
     */
    public function get($type, $date, $params = [])
    {
        $requestUrl = $this->buildApiUrl(self::METHOD_Activities);

        $defaultParams = [
            'apiKey' => $this->connection->getKey(),
            'date' => $date, //validate ?
            'channel' => 'Email',
            'type' => $type, //validate ?
            'http_errors' => false,
            'returnGuid' => 'true',
            'columns' => 'Extended',
            'returnTitle' => 'true'
        ];

        $params = array_merge($defaultParams, $params);
        $response = $this->connection->get($requestUrl, $params);

        $this->connection->isResponseValid($response);

        return $this->formatResponse($response);
    }






}