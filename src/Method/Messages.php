<?php

namespace PicodiLab\Expertsender\Method;

use GuzzleHttp\Psr7\Response;
use PicodiLab\Expertsender\Mapper;

class Messages extends AbstractMethod
{
    const METHOD_MESSAGES = 'Messages';
    const METHOD_NEWSLLETER = 'Newsletters';
    const METHOD_TRANSACTIONAL_MESSAGE = 'Transactionals';
    protected $mapperName = 'Messages';

    public function getList($options = null)
    {
        $_options = [
            'apiKey' => $this->connection->getKey(),
        ];
        if($options && is_array($options))
        {
            $_options = array_merge($options, $_options);
        }

        $response = $this->connection->get(self::METHOD_MESSAGES, $_options);

        $this->connection->isResponseValid($response);
        $rXml = $this->connection->prepareResponse($response);

        $messages = [];
        foreach($rXml->Data->Messages[0] as $message)
        {
            $messages[] = new Mapper\Message(isset($message) ? (array)$message : []);
        }

        return $messages;
    }

    public function getMessage($id)
    {
        $response = $this->connection->get(self::METHOD_MESSAGES . '/' . $id, [
            'apiKey' => $this->connection->getKey(),
        ]);

        $this->connection->isResponseValid($response);
        $rXml = $this->connection->prepareResponse($response);

        return new Mapper\Message(isset($rXml->Data) ? (array)$rXml->Data : []);
    }

    public function setNewsletter($data){
        $requestUrl = $this->buildApiUrl(self::METHOD_NEWSLLETER);

        $requestBody = $this->renderRequestBody(
            'Newsletters/Newsletters',
            array_merge(
                ['data' => $data],
                ['apiKey' => $this->connection->getKey()]
            )
        );


        $response = $this->connection->post($requestUrl, $requestBody);
        $ok = $this->connection->isResponseValid($response);
        $response = $this->connection->prepareResponse($response);
        if($ok)
            return $response;
        else
            return $ok;
    }

    public function stopNewsletter($id){

        $response = $this->connection->delete(self::METHOD_MESSAGES . '/' . $id, [
            'apiKey' => $this->connection->getKey(),
        ]);


        $this->connection->isResponseValid($response);
        $rXml = $this->connection->prepareResponse($response);
        $ok = $this->connection->isResponseValid($response);
        $response = $this->connection->prepareResponse($response);
        if($ok)
            return $response;
        else
            return $ok;

    }

    public function startNewsletter($id){
        $requestUrl = $this->buildApiUrl(self::METHOD_NEWSLLETER.'/'.$id);
        $requestBody = $this->renderRequestBody('Newsletters/ActionNewsletter', [
            'apiKey' => $this->connection->getKey(),
            'action' => 'ResumeMessage'
        ]);

        $response = $this->connection->post($requestUrl, $requestBody);

        $ok = $this->connection->isResponseValid($response);
        $response = $this->connection->prepareResponse($response);
        if($ok)
            return $response;
        else
            return $ok;

    }

    public function sendTransactinalMessage($email, $params, $message_id){
        $requestUrl = $this->buildApiUrl(self::METHOD_TRANSACTIONAL_MESSAGE);
        $requestUrl .= '/'.$message_id;
        $requestBody = $this->renderRequestBody('Transactional/Message', [
            'apiKey' => $this->connection->getKey(),
            'email' => $email,
            'params' => $params
        ]);

        $response = $this->connection->post($requestUrl, $requestBody);

        $ok = $this->connection->isResponseValid($response);
        $response = $this->connection->prepareResponse($response);
        if($ok)
            return $response;
        else
            return $ok;
    }
}