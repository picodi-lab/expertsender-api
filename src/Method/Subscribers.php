<?php

namespace PicodiLab\Expertsender\Method;

use GuzzleHttp\Psr7\Response;
use PicodiLab\Expertsender\Mapper;

class Subscribers extends AbstractMethod
{
    const INFO_OPTION_FULL = 'Full';
    const INFO_OPTION_LONG = 'Long';
    const INFO_OPTION_SHORT = 'Short';
    const INFO_OPTION_EVENT_HISTORY = 'EventsHistory';

    const METHOD_SUBSCRIBERS = 'Subscribers';
    const METHOD_REMOVED_SUBSCRIBERS = 'RemovedSubscribers';
    const METHOD_SNOOZED_SUBSCRIBERS = 'SnoozedSubscribers';
    const METHOD_SEGMENT_SIZE = 'GetSegmentSize';

    const MODE_AddAndUpdate = 'AddAndUpdate';
    const MODE_AddAndReplace = 'AddAndReplace';
    const MODE_AddAndIgnore = 'AddAndIgnore';
    const MODE_IgnoreAndUpdate = 'IgnoreAndUpdate';
    const MODE_IgnoreAndReplace = 'IgnoreAndReplace';

    protected $mapperName = 'Subscriber';

    /**
     * Add subscriber
     * @param Subscriber|array $subscriber
     * @param $listId
     * @param array|null $options
     * @return bool
     */
    public function add($subscriber, $listId, array $options = null, $without = 0)
    {
        $options = $this->getOptions([
            'Mode' => self::MODE_AddAndIgnore
        ], $options);

        return $this->getAddRequest($subscriber, $listId, $options, $without);
    }

    /**
     * Update subscriber
     * @param Subscriber|array $subscriber
     * @param $listId
     * @param array|null $options
     * @return bool
     */
    public function update($subscriber, $listId, array $options = null)
    {
        $options = $this->getOptions([
            'Mode' => self::MODE_IgnoreAndUpdate
        ], $options);

        return $this->getAddRequest($subscriber, $listId, $options);
    }

    /**
     * @param Subscriber|array $subscriber
     * @param $listId
     * @param array|null $options
     * @return bool
     */
    protected function getAddRequest($subscriber, $listId, array $options = null, $without = null)
    {
        if(is_array($subscriber))
        {
            $subscriber = new Mapper\Subscriber($subscriber['Email'], $subscriber);
        }

        if($without)
            $template = 'Subscribers/SubscribersWithoutData';
        else
            $template = 'Subscribers/Subscribers';

        $requestUrl = $this->buildApiUrl(self::METHOD_SUBSCRIBERS);
        $requestBody = $this->renderRequestBody($template, [
            'apiKey' => $this->connection->getKey(),
            'Subscriber' => [
                'Mode' => $this->getOption('Mode', self::MODE_AddAndUpdate, $options),
                'Force' => $this->getOption('Force', 'false', $options),
                'ListId' => $listId,
                'Email' => $subscriber->getEmail(),
                'Firstname' => $subscriber->getFirstname(),
                'Lastname' => $subscriber->getLastname(),
                'TrackingCode' => $this->getOption('TrackingCode', '', $options),
                'Vendor' => $this->getOption('Vendor', '', $options),
                'Ip' => $subscriber->getIp(),
                'Properties' => $subscriber->getProperties()
            ]
        ]);

        $response = $this->connection->post($requestUrl, $requestBody);

        $this->connection->isResponseValid($response);

        return (boolean)$response->getBody();
    }

    /**
     * Add multiple subscriber
     * @param Subscriber|array $subscriber
     * @param $listId
     * @param array|null $options
     * @return bool
     */
    public function addMulti($subscribers, $listId, array $options = null)
    {
        $options = $this->getOptions([
            'Mode' => self::MODE_AddAndIgnore
        ], $options);

        return $this->multiRequest($subscribers, $listId, $options);
    }

    /**
     * @param Subscriber|array $subscriber
     * @param $listId
     * @param array|null $options
     * @return bool
     */
    protected function multiRequest($subscribers, $listId, array $options = null)
    {
        $formatted_subscribers = array();
        if(!is_array($subscribers))
        {
            throw new \Exception('multi is for lot subscribers');
        }else{
            foreach($subscribers as $subscriber){
                $subscriber = new Mapper\Subscriber($subscriber['Email'], $subscriber);
                $formatted_subscribers[] = [
                    'Mode' => $this->getOption('Mode', self::MODE_AddAndUpdate, $options),
                    'Force' => $this->getOption('Force', 'false', $options),
                    'ListId' => $listId,
                    'Email' => $subscriber->getEmail(),
                    'Firstname' => $subscriber->getFirstname(),
                    'Lastname' => $subscriber->getLastname(),
                    'TrackingCode' => $this->getOption('TrackingCode', '', $options),
                    'Vendor' => $this->getOption('Vendor', '', $options),
                    'Ip' => $subscriber->getIp(),
                    'Properties' => $subscriber->getProperties()
                ];
            }
        }

        $requestUrl = $this->buildApiUrl(self::METHOD_SUBSCRIBERS);
        $requestBody = $this->renderRequestBody('Subscribers/SubscribersMulti', [
            'apiKey' => $this->connection->getKey(),
            'rows' => $formatted_subscribers
        ]);

        $response = $this->connection->post($requestUrl, $requestBody);
        /**
         * Secure for black list emails
         */
        if ($response->getStatusCode() == 400) {
            $subscribers = $this->removeBadEmails($response, $subscribers);

            $formatted_subscribers = array();
            if (!is_array($subscribers)) {
                throw new \Exception('multi is for lot subscribers');
            } else {
                foreach ($subscribers as $subscriber) {
                    $subscriber = new Mapper\Subscriber($subscriber['Email'], $subscriber);
                    $formatted_subscribers[] = [
                        'Mode' => $this->getOption('Mode', self::MODE_AddAndUpdate, $options),
                        'Force' => $this->getOption('Force', 'false', $options),
                        'ListId' => $listId,
                        'Email' => $subscriber->getEmail(),
                        'Firstname' => $subscriber->getFirstname(),
                        'Lastname' => $subscriber->getLastname(),
                        'TrackingCode' => $this->getOption('TrackingCode', '', $options),
                        'Vendor' => $this->getOption('Vendor', '', $options),
                        'Ip' => $subscriber->getIp(),
                        'Properties' => $subscriber->getProperties()
                    ];
                }
            }
            $requestUrl = $this->buildApiUrl(self::METHOD_SUBSCRIBERS);

            $requestBody = $this->renderRequestBody('Subscribers/SubscribersMulti', [
                'apiKey' => $this->connection->getKey(),
                'rows' => $formatted_subscribers
            ]);

            $response = $this->connection->post($requestUrl, $requestBody);
        }

        $this->connection->isResponseValid($response);

        return (boolean)$response->getBody();
    }


    protected function getOption($option, $default, array $options = null)
    {
        if(!empty($options) && array_key_exists($option, $options))
        {
            return $options[$option];
        }
        return $default;
    }

    protected function getOptions(array $defaults, array $options = null)
    {
        foreach($defaults as $key => $value)
        {
            $options[$key] = $this->getOption($key, $value, $options);
        }
        return $options;
    }

    /**
     * Subscriber info
     * @param $email
     * @param string $option
     * @return Mapper\Subscriber|null
     */
    public function get($email, $option = self::INFO_OPTION_FULL, $mapCustomFields = true)
    {
        $response = $this->connection->get('Subscribers', [
            'apiKey' => $this->connection->getKey(),
            'email' => $email,
            'option' => $option,
        ]);

        $this->connection->isResponseValid($response);

        $rXml = $this->connection->prepareResponse($response);

        return new Mapper\Subscriber($email, isset($rXml->Data) ? (array)$rXml->Data : []);
    }

    public function deleteSubscriber($email, $listid){

        $response = $this->connection->delete(self::METHOD_SUBSCRIBERS . '?apiKey='.$this->connection->getKey().'&email='.$email.'&listId='.$listid, [
            'apiKey' => $this->connection->getKey(),
            'email' => $email,
            'listId' => $listid
        ]);
    }

    /**
     * Save subscriber to list
     * @param Mapper\Subscriber $subscriber
     * @param $listId
     * @return bool
     */
    public function save(Mapper\Subscriber $subscriber, $listId)
    {
        $result = $this->connection->post('Subscribers', $this->getSubscriberXml($subscriber, $listId));
        return $result['code'] === 201;
    }

    /**
     * Return xml-object with subscriber data (for send to save method)
     * @param Mapper\Subscriber $subscriber
     * @param $listId
     * @return \SimpleXMLElement
     */
    protected function getSubscriberXml(Mapper\Subscriber $subscriber, $listId)
    {
        $xml = $this->connection->getDefaultRequestXml();

        // enable detailed response
        $xml->addChild('ReturnData', 'true');
        // add Data node with subscriber fields
        $dataXml = $xml->addChild('Data');
        $dataXml->addAttribute('xmlns:xsi:type', 'Subscriber');
        $dataXml->addChild('Email', $subscriber->email);
        $dataXml->addChild('Firstname', $subscriber->firstname);
        $dataXml->addChild('ListId', $listId);

        // add Properties list for subscriber
        $xmlProperties = $dataXml->addChild('Properties');
        foreach ($subscriber->properties as $property) {

            $xmlProperty = $xmlProperties->addChild('Property');
            $xmlProperty->addChild('Id', $property->id);
            $xmlProperty->addChild('Type', $property->type);
            $xmlPropertyValue = $xmlProperty->addChild('Value', $property->value);
            $xmlPropertyValue->addAttribute('xmlns:xsi:type', 'xs:' . $property->type);
        }

        return $xml;
    }

    /**
     * @param $id
     * @return \SimpleXMLElement
     */
    public function getSegmentSize($id)
    {
        $url = $this->buildApiUrl(self::METHOD_SEGMENT_SIZE);

        $params = [
            'apiKey' => $this->connection->getKey(),
            'id' => $id
        ];

        $response = $this->connection->get($url, $params);
        $this->connection->isResponseValid($response);

        $formattedResponse = $this->formatResponse($response);
        $xml = new \SimpleXMLElement($formattedResponse);

        return $xml;
    }

    private function removeBadEmails($response, $subscribers){
        $bad_emails = array();
        $xml = simplexml_load_string($response->getBody());
        foreach ($xml->ErrorMessage->Messages as $message) {
            foreach ($message as $ms) {
                $bad_emails[] = $ms->attributes()->for;
            }
        }

        foreach($subscribers as $key => $subscriber){
            if(in_array($subscriber['Email'], $bad_emails)){
                unset($subscribers[$key]);
            }
        }

        return $subscribers;
    }
}