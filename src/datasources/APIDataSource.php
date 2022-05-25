<?php
/**
 * This file contains base class to pull data from an API
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

class APIDataSource extends DataSource
{
    /**
     * Be called when APIDataSource is initiated
     * 
     * @return null
     */
    protected function onInit()
    {
        $this->curlOptions = Util::get($this->params, 'curlOptions', []);
        $this->method = Util::get($this->params, 'method', 'get');
        $this->url = Util::get($this->params, 'url');
        $this->reqHeaders = Util::get($this->params, 'reqHeaders');
        $this->reqData = Util::get($this->params, 'reqData');
        $this->iteration = Util::get($this->params, 'iteration', []);

        $this->metaData = array("columns"=>array());
        $this->metaSent = false;
    }

    protected function curl($method, $url, $curlOptions = [], $headers, $data)
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
        // // Optional Authentication:
        // $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        // $options[CURLOPT_USERPWD] = "username:password";
        if (! empty($headers)) $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_RETURNTRANSFER] = 1;
        $newOptions = [];
        $rep = isset($this->rep) ? $this->rep : [];
        foreach ($options as $k => $v) {
            foreach ($rep as $placeholder => $replace) {
                $k = str_replace($placeholder, $replace, $k);
                $v = str_replace($placeholder, $replace, $v);
            }
            $newOptions[$k] = $v;
        }
        curl_setopt_array($curl, $newOptions);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    protected function fileGetContent()
    {
    }

    protected function Guzzle()
    {
    }

    protected function Httpful()
    {
    }

    protected function RestClient()
    {
    }

    protected function callAPI()
    {
        $result = $this->curl($this->method, $this->url, $this->curlOptions,
            $this->reqHeaders, $this->reqData);
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

    protected function rawToArray($result)
    {
        return json_decode($result, true);
    }

    protected function mapRow($row)
    {
        return $row;
    }

    protected function requestApiAndSend()
    {
        $rawData = $this->callAPI();
        $data = $this->rawToArray($rawData);
        if (is_array($data) && count($data)>0) {
            if (! $this->metaSent) {
                $metaData = $this->metaData;
                $row0 = $this->mapRow(array_values($data)[0]);
                foreach ($row0 as $key=>$value) {
                    $metaData["columns"][$key]=array(
                        "type"=>$this->guessType($value),
                    );
                }
                $this->sendMeta($metaData, $this);
                $this->metaSent = true;
                $this->startInput(null);
            }
            foreach ($data as $row) {
                $row = $this->mapRow($row);
                $this->next($row);
            }
        }
    }

    /**
     * Start piping data
     * 
     * @return null
     */
    public function start()
    {
        if (count($this->iteration) > 0) {
            foreach ($this->iteration as $rep) {
                $this->rep = $rep;
                $this->requestApiAndSend();
            }
        } else {
            $this->requestApiAndSend();
        }
        $this->endInput(null);
    }
}