<?php

namespace desher\Expertsender;

class ExpertSenderApi
{
    protected $connection;
    protected $sections = [];

    public function __construct($apiKey, $apiUrl = null)
    {
        $this->connection = new ExpertSenderApiConnection($apiKey, $apiUrl);
    }

    /**
     * @return \desher\Expertsender\Method\Subscribers
     */
    public function getSubscribers()
    {
        return $this->getApiSection('subscribers');
    }

    /**
     * @return \desher\Expertsender\Method\Tables
     */
    public function getTables()
    {
        return $this->getApiSection('tables');
    }

    /**
     * @return \desher\Expertsender\Method\Lists
     */
    public function getLists()
    {
        return $this->getApiSection('lists');
    }

    /**
     * @param $name
     * @return mixed instance of specified section
     */
    protected function getApiSection($name)
    {
        $className = ucfirst($name);
        if (!isset($this->sections[$name])) {
            $classFullName = 'desher\\Expertsender\\Method\\' . $className;
            return $this->sections[$name] = new $classFullName($this->connection);
        }
        return $this->sections[$name];
    }
}