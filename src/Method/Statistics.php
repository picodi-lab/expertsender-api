<?php

namespace PicodiLab\Expertsender\Method;

use PicodiLab\Expertsender\Exception\InvalidExpertsenderApiRequestException;
use PicodiLab\Expertsender\Mapper;
use PicodiLab\Expertsender\Mapper\SummaryStatistics;

class Statistics extends AbstractMethod
{

    const METHOD_LinkStatistics = 'LinkStatistics';
    const METHOD_MessageStatistics = 'MessageStatistics';
    const METHOD_SummaryStatistics = 'SummaryStatistics';
    const METHOD_SubscriberStatistics = 'SubscriberStatistics';
    const METHOD_SplitTestStatistics = 'SplitTestStatistics';

    protected $mapperName = null;

    /**
     * @param $id
     * @return Mapper\MessageStatistics
     */
    public function get($id)
    {
        $response = $this->connection->get(self::METHOD_MessageStatistics . '/' . $id, [
            'apiKey' => $this->connection->getKey(),
        ]);

        $this->connection->isResponseValid($response);
        $rXml = $this->connection->prepareResponse($response);

        return new Mapper\MessageStatistics(isset($rXml->Data) ? (array)$rXml->Data : []);
    }

    /**
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param string|null $scope
     * @param string|null $scopeValue
     * @param string|null $grouping
     * https://sites.google.com/a/expertsender.com/api-documentation/methods/statistics/get-summary-statistics
     * @throws InvalidExpertsenderApiRequestException
     * @throws NoDataException
     * @return SummaryStatistics
     */
    public function getSummaryStatistics(
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        $scope = null,
        $scopeValue = null,
        $grouping = null
    ) {

        $data = [
            'apiKey' => $this->connection->getKey()
        ];

        if ($startDate !== null) {
            $data['startDate'] = $startDate->format('Y-m-d');
        }

        if ($endDate !== null) {
            $data['endDate'] = $startDate->format('Y-m-d');
        }

        if ($scope !== null) {
            $data['scope'] = $scope;
        }

        if ($scopeValue !== null) {
            $data['scopeValue'] = $scopeValue;
        }

        if ($grouping !== null) {
            $data['grouping'] = $grouping;
        }

        $response = $this->connection->get(self::METHOD_SummaryStatistics, $data);
        $result = $this->connection->isResponseValid($response);

        if (!$result) {
            throw new InvalidExpertsenderApiRequestException();
        }

        $rXml = $this->connection->prepareResponse($response);

        if (!$rXml) {
            throw new NoDataException();
        }

        return new SummaryStatistics($rXml->Data);
    }

}