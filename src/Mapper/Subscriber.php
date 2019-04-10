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
    public function __construct($email = null, $data = [])
    {
        if (!empty($email)) {
            $this->setEmail($email);
        }
        if (isset($data['Id'])) {
            $this->setId($data['Id']);
        }
        if (isset($data['Firstname'])) {
            $this->setFirstname($data['Firstname']);
        }
        if (isset($data['Lastname'])) {
            $this->setLastname($data['Lastname']);
        }
        if (isset($data['Ip'])) {
            $this->setIp($data['Ip']);
        }
        if (isset($data['BlackListEmail'])) {
            $this->setInBlackList($data['BlackListEmail']);
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
        return $this;
    }

    public function setFirstname($value)
    {
        $this->firstname = $value;
        return $this;
    }

    public function setLastname($value)
    {
        $this->lastname = $value;
        return $this;
    }

    public function setIp($value)
    {
        $this->ip = (string)$value;
        return $this;
    }

    public function setInBlackList($value)
    {
        $value = (string)$value;
        if ($value == 'true') {
            $this->inBlackList = true;
        } else {
            $this->inBlackList = false;
        }
        return $this;
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
        return $this;
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
        return $this;
    }

    public function addProperty($id, $type, $value)
    {
        $this->properties[] = new Property([
            'Id' => $id,
            'Type' => $type,
            'Value' => $value
        ]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getInBlackList()
    {
        return $this->inBlackList;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getLists()
    {
        return $this->lists;
    }
}