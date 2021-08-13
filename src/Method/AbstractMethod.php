<?php

namespace PicodiLab\Expertsender\Method;

use GuzzleHttp\Psr7\Response;
use PicodiLab\Expertsender\ExpertSenderApi;
use PicodiLab\Expertsender\ExpertSenderApiConnection;
use ReflectionClass;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;

abstract class AbstractMethod
{
    /** @var ExpertSenderApiConnection */
    protected $connection;

    protected $outputFormat = null;
    protected $mapperName = null;

    const FORMAT_CSV = 'CSV';
    const FORMAT_JSON = 'JSON';
    const FORMAT_ARRAY = 'ARRAY';
    const FORMAT_OBJECT = 'OBJECT';
    const FORMAT_RAW = 'RAW';

//    const FORMAT_XML = 'XML';

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->outputFormat = self::FORMAT_RAW;
    }


    /**
     * builds the proper request url
     * @param $method
     * @return string
     * @throws MethodInMapperNotFoundException
     */
    public function buildApiUrl($method)
    {
        $ref = new \ReflectionClass(get_class($this));
        $availableMethods = preg_grep('/^METHOD_/', array_flip($ref->getConstants()));

        $const = null;
        if (!array_key_exists($method, $availableMethods)) {
            throw new MethodInMapperNotFoundException('Method ' . $method . ' not found in \Mapper\*');
        } else {
            $apiRequestUrl = $this->connection->getUrl() . $method;
        }

        return $apiRequestUrl;
    }

    /**
     * rendering proper request template
     * @param $template
     * @param $params
     * @return string
     */
    public function renderRequestBody($template, Array $params)
    {
        $reflector = new ReflectionClass(ExpertSenderApi::class);
        $fn = $reflector->getFileName();

        $templatePath = dirname($fn) . '/Template/';

        $loader = new FilesystemLoader($templatePath);
        $twig = new Environment($loader, [
            'autoescape' => false,
        ]);

        $temp = $twig->load($template . '.xml.twig');
        $output = $temp->render($params);

        return $output;
    }


    public function setOutputFormat($format = self::FORMAT_CSV)
    {
        $this->outputFormat = $format; //validate?
        return $this;
    }

    /**
     * @return null
     */
    public function getMapperName()
    {
        return $this->mapperName;
    }

    /**
     * @param null $mapperName
     * @return AbstractMethod
     */
    public function setMapperName($mapperName)
    {
        $this->mapperName = $mapperName;
        return $this;
    }


    /**
     * converts the response to the chosen output format
     * @param Response $response
     * @param $format
     * @return string
     */
    protected function formatResponse(Response $response)
    {
        $format = $this->outputFormat;
        $output = '';
        $responseBody = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', (string)$response->getBody());

        switch ($format) {
            case self::FORMAT_RAW:
                return $responseBody;
            case self::FORMAT_CSV:
                return $this->asCSV($responseBody);
                break;
            case self::FORMAT_JSON:
                return $this->asJSON($responseBody);
                break;
            case self::FORMAT_ARRAY:
                return $this->asArray($responseBody);
                break;
            case self::FORMAT_OBJECT:
                return $this->asObject($responseBody);
                break;
        }

        return $output;
    }


    public function asCSV($input)
    {
        $output = $input; //let's say it's the default one in ExpertSender.
        return $output;
    }

    /**
     * returning array response encoded in JSON format
     * @param $input
     * @return string
     */
    public function asJSON($input)
    {
        return json_encode($this->asArray($input));
    }

    /**
     * converts standard CSV response to Array
     * @param $response
     * @return mixed
     */
    public function asArray($input)
    {
        $aInput = explode(PHP_EOL, $input);
        $labels = $output = [];

        foreach ($aInput as $line) {
            if (empty($line)) {
                continue;
            }
            $aLine = str_getcsv(trim($line), ',', '"');
            if (empty($labels)) {
                $labels = $aLine;
            } else {
                $output[] = array_combine($labels, $aLine);
            }
        }

        return $output;
    }

    /**
     * converting the responce to Object. (initializing by arrays)
     * @param $input
     * @return array|null
     */
    public function asObject($input)
    {

        if (empty($this->mapperName)) {
            return null;
        }

        $mapperClass = '\PicodiLab\Expertsender\Mapper\\' . $this->mapperName;
        $aObjects = [];

        $asArray = $this->asArray($input);
        foreach ($asArray as $k => $v) {
            $mapper = new $mapperClass;
            $aObjects[] = $mapper->initialize($v);
        }

        return $aObjects;
    }

}