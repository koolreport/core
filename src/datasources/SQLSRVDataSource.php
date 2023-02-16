<?php
/**
 * This file contains SQLSRVDataSource
 * 
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

/* 
 "sqlsrv"=>array(
    'datahost' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'automaker',
    'charset' => 'utf8',  
    'class' => "\koolreport\datasources\SQLSRVDataSource",
    'returnDatesAsStrings'=>true  
  ),
 
 */

namespace koolreport\datasources;
use \koolreport\core\DataSource;
use \koolreport\core\Utility as Util;

/**
 * SQLSRVDataSource helps to connect to MS SQL Server
 * 
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class SQLSRVDataSource extends DataSource
{
    /**
     * List of available connections for reusing
     * 
     * @var array $connections List of available connections for reusing
     */    
    static $connections;
    
    /**
     * The current connection
     * 
     * @var $connection The current connection
     */    
    protected $connection;


    /**
     * The query
     * 
     * @var string $query The query
     */    
    protected $query;

    /**
     * The params of query
     * 
     * @var array $sqlParams The params of query
     */    
    protected $sqlParams;

    /**
     * The params of query
     * 
     * @var array $queryParams The params of query
     */    
    protected $queryParams;

    /**
     * Whether the total should be counted.
     * 
     * @var bool $countToal Whether the total should be counted.
     */
    protected $countTotal = false;
    
    /**
     * Whether the filter should be counted
     * 
     * @var bool $countFilter Whether the filter should be counted
     */    
    protected $countFilter = false;

    public $originalQuery;

    /**
     * Datasource initiation
     * 
     * @return null
     */
    protected function onInit()
    {
        $host = Util::get($this->params, "host", ""); //"serverName\\sqlexpress, 1542"
        $username = Util::get($this->params, "username", "");
        $password = Util::get($this->params, "password", "");
        $dbname = Util::get($this->params, "dbname", "");
        $returnDatesAsStrings = Util::get($this->params, "returnDatesAsStrings", true);
        $charset = Util::get($this->params, "charset", 'utf-8');
        $connectionInfo = array(
            "Database"=>$dbname,
            "UID"=>$username,
            "PWD"=>$password, 
            "ReturnDatesAsStrings" => $returnDatesAsStrings, 
            "CharacterSet" => $charset
        );

        $key = md5($host.$username.$password.$dbname);
        if (isset(SQLSRVDataSource::$connections[$key])) {
            $this->connection = SQLSRVDataSource::$connections[$key];
        } else {
            $conn = sqlsrv_connect($host, $connectionInfo);
            if ($conn) {
                $this->connection = $conn;
            } else {
                throw new \Exception(
                    "Connection could not be established: "
                    .print_r(sqlsrv_errors(), true)
                );
            }    
            SQLSRVDataSource::$connections[$key] = $this->connection;
        }
    }
    
    /**
     * Set the query and params
     * 
     * @param string $query     The SQL query statement
     * @param array  $sqlParams The parameters of SQL query
     * 
     * @return PostgreSQLDataSource This datasource object
     */
    public function query($query, $sqlParams=null)
    {
        $this->originalQuery = $this->query =  (string)$query;
        if ($sqlParams!=null) {
            $this->sqlParams = $sqlParams;
        }
        return $this;
    }

    /**
     * Process query to additional condition
     * 
     * @param string $query       The query
     * @param array  $queryParams The parameters for query
     * 
     * @return array Information of query
     */
    static function processQuery($query, $queryParams)
    {
        $search = Util::get($queryParams, 'search', '');
        $searchSql = ! empty($search) ? "WHERE $search" : "";

        $order = Util::get($queryParams, 'order', '1');
        $orderSql = ! empty($order) ? "ORDER BY $order" : "";
            
        $start = (int) Util::get($queryParams, 'start', 0);
        $length = (int) Util::get($queryParams, 'length', -1);
        $limit =  $length > -1 ? "OFFSET $start ROWS
        FETCH NEXT $length ROWS ONLY" : "";

        $filterQuery = "SELECT count(*) FROM ($query) tmp $searchSql";
        $totalQuery = "SELECT count(*) FROM ($query) tmp";
        $processedQuery = "select * from ($query) tmp $searchSql $orderSql $limit";

        $thisAggregates = [];
        if (!empty($queryParams["aggregates"])) {
            $aggregates = $queryParams["aggregates"];
            foreach ($aggregates as $operator => $fields) {
                foreach ($fields as $field) {
                    $aggQuery = "SELECT $operator($field) FROM ($query) tmp $searchSql";
                    $thisAggregates[] = [
                        "operator" => $operator,
                        "field" => $field,
                        "aggQuery" => $aggQuery
                    ];
                }
            }
        }

        return [$processedQuery, $totalQuery, $filterQuery, $thisAggregates];
    }

    /**
     * Transform query
     * 
     * @param array $queryParams Parameters of query
     * 
     * @return array The information of query 
     */
    public function queryProcessing($queryParams) 
    {
        $this->queryParams = $queryParams;
        list($this->query, $this->totalQuery, $this->filterQuery, $this->aggregates)
            = self::processQuery($this->originalQuery, $queryParams);

        $this->countTotal = Util::get($queryParams, 'countTotal', false);
        $this->countFilter = Util::get($queryParams, 'countFilter', false);

        return $this;
    }

    /**
     * Insert params for query
     * 
     * @param array $sqlParams The parameters for query
     * 
     * @return SQLSRVDataSource This datasource
     */  
    public function params($sqlParams)
    {
        $this->sqlParams = $sqlParams;
        return $this;
    }
    
    /**
     * Perform data binding
     * 
     * @param string $query     Query need to bind params
     * @param array  $sqlParams The parameters will be bound to query
     * 
     * @return string Procesed query 
     */
    protected function bindParams($query, $sqlParams)
    {
        if (empty($sqlParams)) {
            $sqlParams = [];
        }

        uksort(
            $sqlParams,
            function ($k1, $k2) {
                return strlen($k1) - strlen($k2);
            }
        );
        foreach ($sqlParams as $key=>$value) {
            if (gettype($value)==="array") {
                $value = array_map(
                    function ($v) {
                        return $this->escape($v);
                    },
                    $value
                );
                $value = implode(",", $value);
                $query = str_replace($key, $value, $query);
            } else {
                $query = str_replace($key, (string)$this->escape($value), $query);
            }
        }
        return $query;
    }
    
    /**
     * Escape value for SQL safe
     * 
     * @param string $str The string need to be escape
     * 
     * @return null
     */    
    protected function escape($str)
    {
        if (is_string($str) OR (is_object($str) && method_exists($str, '__toString'))) {
            return "'".$this->escapeStr($str)."'";
        } elseif (is_bool($str)) {
            return ($str === false) ? 0 : 1;
        } elseif ($str === null) {
            return 'NULL';
        }
        return $str;
    }

    /**
     * Escape string
     * 
     * @param string $str The string needed to be escaped.
     * 
     * @return string The escaped string
     */
    protected function escapeStr($str)
    {
        return str_replace("'", "''", $str);
    }

    /**
     * Map field type to bind type
     * 
     * @param string $field_type The type of field
     * 
     * @return string KoolReport type of field
     */
    function mapFieldTypeToBindType($field_type)
    {
        switch ($field_type) {
        case -5:
        case -2:
        case -7:
        case 3:
        case 6:
        case -4:
        case 4:
        case 2:
        case 7:
        case 5:
        case -6:
            return 'number';
        case 91:
            return 'date';
        case -2:
        case 93:
            return 'datetime';
        
        case 1:
        case -10:
        case -3:
        case -9:
        case 12:
            return 'string';

        default:
            return 'unknown';
        }
    }

    protected function prepareAndBind($query, $params = [])
    {
        $paNames = array_keys($params);
        // Sort param names, longest name first,
        // so that longer ones are replaced before shorter ones in query
        // to avoid case when a shorter name is a substring of a longer name
        usort(
            $paNames,
            function ($k1, $k2) {
                return strlen($k2) - strlen($k1);
            }
        );
        // echo "paNames = "; print_r($paNames); echo "<br>";

        // Spreadh array parameters
        foreach ($paNames as $paName) {
            $paValue = $params[$paName];
            if (gettype($paValue)==="array") {
                $numValues = strlen((string)count($paValue));
                $paramList = [];
                foreach ($paValue as $i=>$value) {
                    $order = $i + 1;
                    // Pad order to keep all array param name length equal
                    $order = str_pad($order, $numValues, "0", STR_PAD_LEFT);
                    $paArrElName = $paName . "_arr_$order";
                    $paramList[] = $paArrElName;
                    $params[$paArrElName] = $value;
                }
                $query = str_replace($paName, implode(",", $paramList), $query);
            } 
        }

        $paNames = array_keys($params);
        usort(
            $paNames,
            function ($k1, $k2) {
                return strlen($k2) - strlen($k1);
            }
        );
        // echo "paNames = "; print_r($paNames); echo "<br><br>";
        // echo "query = $query<br><br>";

        $newParams = [];
        $positions = [];
        $originalQuery = $query;
        foreach ($paNames as $paName) {
            $count = 1;
            $pos = -1;
            while (true) {
                $pos = strpos($query, $paName, $pos + 1);
                if ($pos === false) {
                    break;
                } else {
                    $newPaName = $count > 1 ? $paName . "_" . $count : $paName;
                    $newParams[$newPaName] = $params[$paName];
                    $positions[$newPaName] = $pos;
                    // $query = substr_replace($query, "?", $pos, strlen($paName));
                    $query = substr_replace($query, str_repeat("?", strlen($paName)), $pos, strlen($paName));
                }
                $count++;
            }
        }
        $sortedLenPaNames = array_keys($newParams);
        usort(
            $sortedLenPaNames,
            function ($k1, $k2) {
                return strlen($k2) - strlen($k1);
            }
        );
        // echo "positions = " . print_r($positions, true) . "<br><br>";
        // echo "sortedLenPaNames = " . print_r($sortedLenPaNames, true) . "<br><br>";
        // echo "query = $query<br><br>";
        $query = $originalQuery;
        foreach ($sortedLenPaNames as $paName) {
            $query = str_replace($paName, "?", $query);
        }
        // echo "query = $query<br><br>";

        // Sort new params by their positions, smallest one first
        $sortedPosParams = $newParams;
        uksort(
            $sortedPosParams,
            function ($k1, $k2) use ($positions) {
                return $positions[$k1] - $positions[$k2];
            }
        );
        // echo "sortedPosParams = " . print_r($sortedPosParams, true) . "<br><br>";

        $stmt = sqlsrv_query($this->connection, $query, array_values($sortedPosParams));
        if( $stmt === false ) {
            throw new \Exception(
                "Sqlsrv error: " . json_encode(sqlsrv_errors()) 
                . " || Sql query = $query"
                . " || params = " . json_encode($params)
            );
        }
        return $stmt;
    }
    
    /**
     * Start piping data
     * 
     * @return null
     */
    public function start()
    {
        $metaData = array("columns"=>array());

        $searchParams = Util::get($this->queryParams, 'searchParams', []);

        if (empty($this->sqlParams)) $this->sqlParams = [];
        if (empty($searchParams)) $searchParams = [];

        if ($this->countTotal) {
            $stmt = $this->prepareAndBind($this->totalQuery, $this->sqlParams);
            $row = sqlsrv_fetch_array($stmt);
            $total = $row[0];
            $metaData['totalRecords'] = $total;
        }

        if ($this->countFilter) {
            $stmt = $this->prepareAndBind($this->filterQuery, array_merge($this->sqlParams, $searchParams));
            $row = sqlsrv_fetch_array($stmt);
            $total = $row[0];
            $metaData['filterRecords'] = $total;
        }

        if (!empty($this->aggregates)) {
            foreach ($this->aggregates as $aggregate) {
                $operator = $aggregate["operator"];
                $field = $aggregate["field"];
                $aggQuery = $aggregate["aggQuery"];
                $stmt = $this->prepareAndBind($aggQuery, array_merge($this->sqlParams, $searchParams));
                $row = sqlsrv_fetch_array($stmt);
                $result = $row[0];
                Util::set($metaData, ['aggregates', $operator, $field], $result);
            }
        }

        $stmt = $this->prepareAndBind($this->query, array_merge($this->sqlParams, $searchParams));

        $finfo = sqlsrv_field_metadata($stmt);
        
        $numcols = count($finfo);
        for ($i=0; $i<$numcols; $i++) {
            $type = $this->mapFieldTypeToBindType($finfo[$i]['Type']);
            $metaData["columns"][$finfo[$i]['Name']] = array(
                "type"=>$type,
            );
            switch($type)
            {
            case "datetime":
                $metaData["columns"][$finfo[$i]['Name']]["format"] = "Y-m-d H:i:s";
                break;
            case "date":
                $metaData["columns"][$finfo[$i]['Name']]["format"] = "Y-m-d";
                break;
            }
        }
                
        $this->sendMeta($metaData, $this);
    
        $this->startInput(null);
        
        while ( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ) {
            $this->next($row, $this);
        }
    
        $this->endInput(null);
    }

    public function fetchData($query, $queryParams = null)
    {
        if (isset($queryParams) && 
            (isset($queryParams['countTotal']) || isset($queryParams['countFilter']))) {
            list($query, $totalQuery, $filterQuery)
                = self::processQuery($query, $queryParams);
            
            $queries = [
                'data' => $query,
                'total' => $totalQuery,
                'filter' => $filterQuery
            ];
        } else {
            $queries = [
                'data' => $query
            ];
        }
        $data = [];
        foreach ($queries as $key => $query) {
            $stmt = $this->prepareAndBind($this->query, $this->sqlParams);
            $rows = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // print_r($row); echo "<br>";
                $rows[] = $row;
            }
            $data[$key] = $rows;
        }
        return $data;
    }
}