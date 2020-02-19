<?php
/**
 * This file contains base class to pull data from array
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

namespace koolreport\datasources;
use \koolreport\core\DataSource;
use \koolreport\core\Utility as Util;


/**
 * ArrayDataSource helps to load data from array
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class RestfulDataSource extends DataSource
{
    /**
     * Either "table" format or "associate" format
     * 
     * @var string $dataFormat Either "table" format or "associate" format
     */
    protected $dataFormat = "associate";

    /**
     * Containing data
     * 
     * @var array $data Containing data
     */
    protected $data;

    /**
     * Be called when arraydatasource is initiated
     * 
     * @return null
     */
    protected function onInit()
    {
        $this->url = Util::get($this->params, 'url');
        $this->curlOptions = Util::get($this->params, 'curlOptions', []);
        $this->method = Util::get($this->params, 'method', 'get');
        $this->headers = Util::get($this->params, 'headers');
        $this->apiParams = Util::get($this->params, 'apiParams');
    }

    function curl($method, $url, $curlOptions = [], $headers, $data)
    {
        $options = $curlOptions;
        $curl = curl_init();
        switch (strtoupper($method)) {
            case "POST":
                $options[CURLOPT_POST] = 1;
                if ($data) $options[CURLOPT_POSTFIELDS] = $data;
                break;
            case "PUT": 
                $options[CURLOPT_PUT] = 1; 
                break;
            default:
                if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        if ($headers) $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_URL] =$url;
        $options[CURLOPT_RETURNTRANSFER] = 1;
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    function fileGetContent()
    {
    }

    function Guzzle()
    {
    }

    function Httpful()
    {
    }

    function RestClient()
    {
    }

    function callAPI()
    {
        $result = $this->curl($this->method, $this->url, $this->curlOptions,
            $this->headers, $this->apiParams);
        return $result;
    }
    
    /**
     * Guess type of a value
     * 
     * @param mixed $value The value
     * 
     * @return string Type of value
     */
    protected function guessType($value)
    {
        $map = array(
            "float"=>"number",
            "double"=>"number",
            "int"=>"number",
            "integer"=>"number",
            "bool"=>"number",
            "numeric"=>"number",
            "string"=>"string",
            "array"=>"array",
        );

        $type = strtolower(gettype($value));
        foreach ($map as $key=>$value) {
            if (strpos($type, $key)!==false) {
                return $value;
            }
        }
        return "unknown";
    }

    function rawToArray($result)
    {
        return json_decode($result, true);
    }

    function mapRow($row)
    {
        return $row;
    }

    /**
     * Start piping data
     * 
     * @return null
     */
    public function start()
    {
        $rawData = $this->callAPI();
        $data = $this->rawToArray($rawData);
        // Util::prettyPrint($data); 
        if (is_array($data) && count($data)>0) {
            $metaData = array("columns"=>array());
            $row0 = $this->mapRow($data[0]);
            foreach ($row0 as $key=>$value) {
                $metaData["columns"][$key]=array(
                    "type"=>$this->guessType($value),
                );
            }
            $this->sendMeta($metaData, $this);
            $this->startInput(null);
            foreach ($data as $row) {
                $row = $this->mapRow($row);
                $this->next($row);
            }
        }
        $this->endInput(null);
    }
}