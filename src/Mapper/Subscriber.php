<?php

namespace PicodiLab\Expertsender\Mapper;

use PicodiLab\Expertsender\Method\Fields;

class Subscriber
{
    protected $id;
    protected $ip;
    protected $inBlackList;
    protected $email;
    protected $firstname;
    protected $lastname;
    protected $properties = [];
    protected $lists = [];

    /**
     * @param $email
     * @param mixed $data
     */
    public function __construct($email, $data = [])
    {
        $this->setEmail($email);

        if (isset($data['Id'])) {
            $this->setId($data['Id']);
        }
        if (isset($data['Ip'])) {
            $this->setIp($data['Ip']);
        }
        if (isset($data['BlackList'])) {
            $this->setInBlackList($data['BlackList']);
        }
        if (isset($data['Properties'])) {
            if ($data['Properties'] instanceof \SimpleXMLElement) {
                $this->setProperties($data['Properties']->Property);
            } else {
                $this->setProperties($data['Properties']);
            }
        }
        if (isset($data['StateOnLists'])) {
            if ($data['StateOnLists'] instanceof \SimpleXMLElement) {
                $this->setLists($data['StateOnLists']->StateOnList);
            } else {
                $this->setLists($data['StateOnLists']);
            }
        }
    }

    public function setId($value)
    {
        $this->id = (int)$value;
    }

    public function setFirstname($value)
    {
        $this->firstname = $value;
    }

    public function setIp($value)
    {
        $this->ip = (string)$value;
    }

    public function setInBlackList($value)
    {
        $value = (string)$value;
        if ($value == 'true') {
            $this->inBlackList = true;
        } else {
            $this->inBlackList = false;
        }
    }

    public function setProperties($values)
    {
        foreach ($values as $property) {
            if(!empty($property->Name)) {
                $name = trim($property->Name);
                $this->properties[$name] = new Property((array)$property);
            }else{
                $this->properties[] = new Property((array)$property);
            }
        }
    }

    public function setLists($values)
    {
        foreach ($values as $list) {
            $this->lists[] = (array)$list;
        }
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function addProperty($id, $type, $value)
    {
        $this->properties[] = new Property([
            'Id' => $id,
            'Type' => $type,
            'Value' => $value
        ]);
    }
}