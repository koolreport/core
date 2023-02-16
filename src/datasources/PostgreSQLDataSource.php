<?php
/**
 * This file contain PostgreSQLDataSource
 * 
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

/* 
 "postgresql"=>array(
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbbase' => 'automaker',
    'class' => "\koolreport\datasources\PostgreSQLDataSource"  
  ),
 
 */

namespace koolreport\datasources;
use \koolreport\core\DataSource;
use \koolreport\core\Utility as Util;

/**
 * PostgreSQLDataSource helps to connect to  PostgreSQL database
 * 
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class PostgreSQLDataSource extends DataSource
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

    protected $queryParams;
    
    /**
     * Whether the total should be counted.
     * 
     * @var bool $countToal Whether the total should be counted.
     */
    protected $countTotal;
    
    /**
     * Whether the filter should be counted
     * 
     * @var bool $countFilter Whether the filter should be counted
     */    
    protected $countFilter;

    public $originalQuery;
    
    /**
     * DataSource initiation
     * 
     * @return null
     */
    protected function onInit()
    {
        $host = Util::get($this->params, "host", "");//host\instanceName
        $port = Util::get($this->params, "port", 5432);
        $username = Util::get($this->params, "username", "");
        $password = Util::get($this->params, "password", "");
        $dbname = Util::get($this->params, "dbname", "");
        $connString = "host=$host port=$port dbname=$dbname user=$username password=$password";
        
        $key = md5($connString);

        if (isset(PostgreSQLDataSource::$connections[$key])) {
            $this->connection = PostgreSQLDataSource::$connections[$key];
        } else {
            $conn = pg_connect($connString);
            if ($conn) {
                $this->connection = $conn;
            } else {
                throw new \Exception("Could not connect to database");
            }
            PostgreSQLDataSource::$connections[$key] = $this->connection;
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

        $order = Util::get($queryParams, 'order', '');
        $orderSql = ! empty($order) ? "ORDER BY $order" : "";
            
        $start = (int) Util::get($queryParams, 'start', 0);
        $length = (int) Util::get($queryParams, 'length', -1);
        $limit =  $length > -1 ? "LIMIT $length OFFSET $start" : "";

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
     * @return PostgreSQLDataSource Return itself for cascade 
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
     * @return OracleDataSource This datasource
     */  
    public function params($sqlParams)
    {
        $this->sqlParams = $sqlParams;
        return $this;
    }
    
    /**
     * Escape value for SQL safe
     * 
     * @param string $str The string need to be escape
     * 
     * @return string Escaped string
     */    
    protected function escape($str)
    {
        if (is_string($str) || (is_object($str) && method_exists($str, '__toString'))) {
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
        return pg_escape_string($str);
    }
    
    /**
     * Map field type to bind type
     * 
     * @param strng $native_type The type of field
     * 
     * @return string KoolReport type of field
     */
    protected function mapFieldTypeToBindType($native_type)
    {
        $pg_to_php = array(
            'bit' => 'number',
            'boolean' => 'string',
            'box' => 'string',
            'character' => 'string',
            'char' => 'number',
            'bytea' => 'number',
            'cidr' => 'string',
            'circle' => 'string',
            'date' => 'datetime',
            'daterange' => 'datetime',
            'real' => 'number',
            'double precision' => 'number',
            'inet' => 'number',
            'smallint' => 'number',
            'smallserial' => 'number',
            'integer' => 'number',
            'serial' => 'number',
            'int4range' => 'number',
            'bigint' => 'number',
            'bigserial' => 'number',
            'int8range' => 'number',
            'interval' => 'number',
            'json' => 'string',
            'lseg' => 'string',
            'macaddr' => 'string',
            'money' => 'number',
            'decimal' => 'number',
            'numeric' => 'number',
            'numrange' => 'number',
            'path' => 'string',
            'point' => 'string',
            'polygon' => 'string',
            'text' => 'string',
            'time' => 'datetime',
            'time without time zone' => 'datetime',
            'timestamp' => 'datetime',
            'timestamp without time zone' => 'datetime',
            'timestamp with time zone' => 'datetime',
            'time with time zone' => 'datetime',
            'tsquery' => 'string',
            'tsrange' => 'string',
            'tstzrange' => 'string',
            'tsvector' => 'string',
            'uuid' => 'number',
            'bit varying' => 'number',
            'character varying' => 'string',
            'varchar' => 'string',
            'xml' => 'string'
        );
        
        $native_type = strtolower($native_type);
        
        $mappedType = Util::get($pg_to_php, $native_type, "unknown");
        return $mappedType;
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
                    $query = substr_replace($query, str_repeat("?", strlen($paName)), $pos, strlen($paName));
                }
                $count++;
            }
        }
        // Sort new params by their positions, smallest one first
        $sortedPosNewParams = $newParams;
        uksort(
            $sortedPosNewParams, 
            function ($k1, $k2) use ($positions) {
                return $positions[$k1] - $positions[$k2];
            }
        );

        $sortedPosParamNames = array_keys($sortedPosNewParams);
        $sortedPosParamNameIndexes = array_flip($sortedPosParamNames);
        $sortedLenParamNames = array_keys($sortedPosNewParams);
        usort(
            $sortedLenParamNames,
            function ($k1, $k2) {
                return strlen($k2) - strlen($k1);
            }
        );
        $query = $originalQuery;
        $count = 1;
        foreach ($sortedLenParamNames as $paName) {
            $query = str_replace($paName, "$" . ($sortedPosParamNameIndexes[$paName] + 1), $query);
        }
        // echo "query = $query<br><br>";
        // echo "sortedPosNewParams = "; print_r($sortedPosNewParams); echo "<br>";

        $result = pg_query_params($this->connection, $query, array_values($sortedPosNewParams));
        if (!$result) {
            throw new \Exception(
                "PostgreSQL error: " . pg_last_error($this->connection)
                . " || Query = $query"
                . " || Params = " . json_encode($params)
            );
        }
        return $result;
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
            $totalResult = $this->prepareAndBind($this->totalQuery, $this->sqlParams);
            if (!$totalResult) {
                echo pg_last_error($this->connection);
                exit;
            }
            $row = pg_fetch_array($totalResult);
            $total = $row[0];
            $metaData['totalRecords'] = $total;
        }

        if ($this->countFilter) {
            $filterResult = $this->prepareAndBind($this->filterQuery, array_merge($this->sqlParams, $searchParams));
            $row = pg_fetch_array($filterResult);
            $total = $row[0];
            $metaData['filterRecords'] = $total;
        }

        if (!empty($this->aggregates)) {
            foreach ($this->aggregates as $aggregate) {
                $operator = $aggregate["operator"];
                $field = $aggregate["field"];
                $aggQuery = $aggregate["aggQuery"];
                $aggResult = $this->prepareAndBind($aggQuery, array_merge($this->sqlParams, $searchParams));
                $row = pg_fetch_array($aggResult);
                $result = $row[0];
                Util::set($metaData, ['aggregates', $operator, $field], $result);
            }
        }

        $result = $this->prepareAndBind($this->query, array_merge($this->sqlParams, $searchParams));

        $num_fields = pg_num_fields($result);

        for ($i=0; $i<$num_fields; $i++) {
            $name = pg_field_name($result, $i);
            $type = pg_field_type($result, $i);
            $type = $this->mapFieldTypeToBindType($type);
            $metaData["columns"][$name] = array(
                "type"=>$type,
            );
            switch($type)
            {
            case "datetime":
                $metaData["columns"][$name]["format"] = "Y-m-d H:i:s";
                break;
            case "date":
                $metaData["columns"][$name]["format"] = "Y-m-d";
                break;
            }
        }
                
        $this->sendMeta($metaData, $this);
    
        $this->startInput(null);
        
        while ($row = pg_fetch_assoc($result)) {
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
            $queryResult = $this->prepareAndBind($this->query, $this->sqlParams);
            $rows = [];
            while ($row = pg_fetch_assoc($queryResult)) {
                // print_r($row); echo "<br>";
                $rows[] = $row;
            }
            $data[$key] = $rows;
        }
        return $data;
    }
}
