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
class OlapDataSource extends DataSource
{
    
    protected function onInit()
    {
        $this->url = Util::get($this->params, 'url');
        $this->user = Util::get($this->params, 'user');
        $this->password = Util::get($this->params, 'password');
        parent::onInit();
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

    public function query($pivotSetting)
    {
        /* 
        [
            "row" => [
                "fields" => ["emp_name"]
            ],
            "column" => [
                "fields" => []
            ],
            "measures" => [
                [
                    "operator" => "sum",
                    "field" => "salary"
                ]
            ]
        ]
        */
        $this->pivotSetting = $pivotSetting;
        
        $measures = [];
        $aggregates = Util::get($pivotSetting, "aggregates", []);
        foreach ($aggregates as $operator => $fields) {
            if (is_string($fields)) $fields = explode(",", $fields);
            $fields = array_map("trim", $fields);
            foreach ($fields as $field) $measures[] = [
                "operator" => $operator,
                "field" => $field
            ];
        }

        $rowFields = Util::get($pivotSetting, "row", []);
        if (is_string($rowFields)) $rowFields = explode(",", $rowFields);
        $this->rowFields = $rowFields = array_map("trim", $rowFields);

        $columnFields = Util::get($pivotSetting, "column", []);
        if (is_string($columnFields)) $columnFields = explode(",", $columnFields);
        $this->columnFields = $columnFields = array_map("trim", $columnFields);

        $this->olapQuery = [
            "row" => [ "fields" => $rowFields ],
            "column" => [ "fields" => $columnFields ],
            "measures" => $measures,
        ];
        // echo ("this->olapQuery = "); Util::prettyPrint($this->olapQuery); echo "<br>";
        // exit;
        return $this;
    }

    protected function aggregatesToMeta($aggregates)
    {
        $aggregatesMeta = [];
        foreach ($aggregates as $operator => $aggFields) {
            $op = trim($operator);
            if (is_string($aggFields)) $aggFields = explode(",", $aggFields);
            $aggFields = array_map("trim", $aggFields);
            foreach ($aggFields as $af) {
                Util::init($aggregatesMeta, $af, []);
                $aggregatesMeta[$af][] = $op;
            }
        }
        return $aggregatesMeta;
    }

    protected function loginOlap()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . "/api/login",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            // CURLOPT_POSTFIELDS => [
            //     "name" => $this->user, "password" => $this->password
            // ],
            CURLOPT_POSTFIELDS => json_encode([
                "name" => $this->user, "password" => $this->password
            ]),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                // 'Content-Type: multipart/form-data'
            ),
        ));

        $response = curl_exec($curl);
        // echo "response=$response<br>";

        curl_close($curl);
        $response = json_decode($response, true);
        return Util::get($response, 'token');
    }

    protected function getData()
    {
        $olapQuery = $this->olapQuery;
        // echo json_encode($this->olapQuery) . "<br>";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . "/api/cube/query",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($this->olapQuery),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer {$this->bearerToken}",
            ),
        ));

        $response = curl_exec($curl);
        // echo "response=$response<br>";

        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    /**
     * Start piping data
     * 
     * @return null
     */
    public function start()
    {
        // ob_start();
        $this->bearerToken = $this->loginOlap();
        // ob_end_clean();
        // echo "bearerToken={$this->bearerToken}<br>";
        // // var_dump($this->bearerToken);
        // echo "<br>";
        // exit;

        $data = $this->getData();

        // echo "OlapDataSource start() data = "; 
        // Util::prettyPrint($data); 
        // exit;

        if ($data && count($data)>0) {
            $metaData = array(
                'pivotFormat' => 'pivot2D',
                'pivotRows' => $this->rowFields,
                'pivotColumns' => $this->columnFields,
                'pivotAggregates' => $this->aggregatesToMeta(
                    Util::get($this->pivotSetting, 'aggregates')),
                'pivotFieldDelimiter' => " || ",
                "columns"=>array()
            );
            // Util::prettyPrint($metaData);
            foreach ($data[0] as $key=>$value) {
                $metaData["columns"][$key]=array(
                    "type"=>$this->guessType($value),
                );
            }
            $this->sendMeta($metaData, $this);
            $this->startInput(null);
            foreach ($data as $row) {
                // Util::prettyPrint($row); 
                $this->next($row);
            }
        }
        $this->endInput(null);
    }
}