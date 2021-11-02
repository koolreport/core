<?php
/**
 * This file contains class for MySQLDataSource
 * 
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

/* 
 "mysql"=>array(
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'automaker',
    'charset' => 'utf8',  
    'class' => "\koolreport\datasources\MySQLDataSource"  
  ),
 */

namespace koolreport\datasources;
use \koolreport\core\DataSource;
use \koolreport\core\Utility as Util;

/**
 * MySQLDataSource helps to connect to MySQL Database
 * 
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class MySQLDataSource extends DataSource
{
    /**
     * Contains list of reuable connection
     * 
     * @var $connections Contains list of reuable connection
     */
    static $connections;

    /**
     * Current data connection
     * 
     * @var $connection Current data connection
     */
    protected $connection;
    
    /**
     * The SQL query
     * 
     * @var string $query The SQL query
     */
    protected $query;

    /**
     * The parameters for SQL query
     * 
     * @var array $sqlParams 
     */
    protected $sqlParams;

    /**
     * Whether total should be count
     * 
     * @var bool $countTotal 
     */
    protected $countTotal = false;

    /**
     * Whether filter should be count
     * 
     * @var bool $countFilter Whether filter should be count
     */
    protected $countFilter = false;

    protected $queryParams = [];

    /**
     * Init MySQLdataSource
     * 
     * @return null
     */
    protected function onInit()
    {
        $host = Util::get($this->params, "host", ""); //localhost:3306
        $username = Util::get($this->params, "username", "");
        $password = Util::get($this->params, "password", "");
        $dbname = Util::get($this->params, "dbname", "");
        $charset = Util::get($this->params, "charset", null);
        
        $key = md5($host.$username.$password.$dbname);
        if (isset(MySQLDataSource::$connections[$key])) {
            $this->connection = MySQLDataSource::$connections[$key];
        } else {
            $this->connection = new \mysqli($host, $username, $password, $dbname);
            /* check connection */
            if ($this->connection->connect_errno) {
                throw new \Exception(
                    "Failed to connect to MySQL: ("
                    .$this->connection->connect_errno
                    .") "
                    .$this->connection->connect_error
                );
            }
            MySQLDataSource::$connections[$key] = $this->connection;    
        }

        /* change character set */
        if (isset($charset) && ! $this->connection->set_charset($charset)) {
            throw new \Exception(
                "Error loading character set $charset: "
                .$this->connection->error
            );
        }
    }
    
    /**
     * Set the query and parameters
     * 
     * @param string $query     The query statement
     * @param array  $sqlParams The parameters for query
     * 
     * @return MySQLDataSource Return itself for cascade
     */
    public function query($query, $sqlParams=null)
    {
        $this->originalQuery = $this->query = (string)$query;
        if ($sqlParams != null) {
            $this->sqlParams = $sqlParams;
        }
        return $this;
    }

    /**
     * Process query to additional condition
     * 
     * @param string $query       The query string
     * @param array  $queryParams The array containing parameters
     * 
     * @return array Information for query processing
     */
    static function processQuery($query, $queryParams)
    {
        $search = Util::get($queryParams, 'search', '');

        $searchSql = ! empty($search) ? "WHERE $search" : "";

        $order = Util::get($queryParams, 'order', '');
        $orderSql = ! empty($order) ? "ORDER BY $order" : "";
            
        $start = (int) Util::get($queryParams, 'start', 0);
        $length = (int) Util::get($queryParams, 'length', 1);
        $limit =  $length > -1 ? "LIMIT $start, $length" : "";

        $filterQuery = "SELECT count(*) FROM ($query) tmp $searchSql";
        $totalQuery = "SELECT count(*) FROM ($query) tmp";
        $processedQuery = "select * from ($query) tmp $searchSql $orderSql $limit";
        // echo "processedQuery=" . $processedQuery . '<br>';

        return [$processedQuery, $totalQuery, $filterQuery];
    }

    /**
     * Transform query
     * 
     * @param array $queryParams Parameters of query 
     * 
     * @return MySQLDataSource Return itself for cascade
     */
    public function queryProcessing($queryParams) 
    {
        $this->queryParams = $queryParams;
        // echo "queryProcessing queryParams="; print_r($queryParams); echo "<br>";
        list($this->query, $this->totalQuery, $this->filterQuery)
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
     * @return MySQLDataSource This datasource
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
                $query = str_replace($key, $this->escape($value), $query);
            }
        }
        return $query;
    }
    
    /**
     * Escape value for SQL safe
     * 
     * @param string $str The string need to be escape
     * 
     * @return Escaped string
     */
    public function escape($str)
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
        return $this->connection->real_escape_string($str);
    }
    
    /**
     * Map field type to bind type
     * 
     * @param strng $field_type The type of field
     * 
     * @return string KoolReport type of field
     */
    function mapFieldTypeToBindType($field_type)
    {
        switch ($field_type) {
        case MYSQLI_TYPE_DECIMAL:
        case MYSQLI_TYPE_NEWDECIMAL:
        case MYSQLI_TYPE_FLOAT:
        case MYSQLI_TYPE_DOUBLE:
        case MYSQLI_TYPE_BIT:
        case MYSQLI_TYPE_TINY:
        case MYSQLI_TYPE_SHORT:
        case MYSQLI_TYPE_LONG:
        case MYSQLI_TYPE_LONGLONG:
        case MYSQLI_TYPE_INT24:
        case MYSQLI_TYPE_YEAR:
        case MYSQLI_TYPE_ENUM:
            return 'number';

        case MYSQLI_TYPE_DATE:
            return 'date';

        case MYSQLI_TYPE_TIME:
            return 'time';
        case MYSQLI_TYPE_TIMESTAMP:
        case MYSQLI_TYPE_DATETIME:
        case MYSQLI_TYPE_NEWDATE:
            return 'datetime';
        
        case MYSQLI_TYPE_VAR_STRING:
        case MYSQLI_TYPE_STRING:
        case MYSQLI_TYPE_CHAR:
        case MYSQLI_TYPE_GEOMETRY:
        case MYSQLI_TYPE_TINY_BLOB:
        case MYSQLI_TYPE_MEDIUM_BLOB:
        case MYSQLI_TYPE_LONG_BLOB:
        case MYSQLI_TYPE_BLOB:
            return 'string';

        default:
            return 'unknown';
        }
    }

    protected function prepareAndBind($query, $params = [])
    {
        $paramNames = array_keys($params);
        uksort(
            $paramNames,
            function ($k1, $k2) {
                return strlen($k1) - strlen($k2);
            }
        );
        foreach ($paramNames as $k) {
            $query = str_replace($k, "?", $query);
        }
        $stmt = $this->connection->prepare($query);
        $typeStr = "";
        foreach ($params as $v) {
            $typeStr .= is_double($v) ? "d" : 
                (is_int($v) ? "i" : "s");
        }
        if (! empty($typeStr)) {
            // echo "typeStr=$typeStr<br>"; 
            // echo "[typeStr] + params="; print_r([$typeStr] + $params); echo "<br>";
            // echo "...array_values(params) = "; print_r(...array_values($params)); echo "<br>";
            $arr = [$typeStr] + $params;
            $refArr = [];
            foreach($arr as $k => $v){$refArr[] = &$arr[$k];}
            call_user_func_array(array($stmt, 'bind_param'), $refArr);
            // call_user_func_array(array($stmt, 'bind_param'), array_merge([$typeStr], $params));
            // call_user_func_array(array($stmt, 'bind_param'), array_merge([$typeStr], array_values($params)));
            // $stmt->bind_param($typeStr, ...array_values($params)); //spread operator ... only available since PHP 5.6
            // $stmt->bind_param($typeStr, ...$params); //spread operator ... only available since PHP 5.6
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

        if ($this->countTotal) {
            $totalQuery = $this->bindParams($this->totalQuery, $this->sqlParams);
            $totalResult = $this->connection->query($totalQuery);
            if ($totalResult===false) {
                throw new \Exception("Error on query >>> ".$this->connection->error);
            }
            $row = $totalResult->fetch_array();
            $result = $row[0];
            $metaData['totalRecords'] = $result;
        }

        if ($this->countFilter) {
            $filterQuery = $this->bindParams($this->filterQuery, $this->sqlParams);
            $stmt = $this->prepareAndBind($filterQuery, $searchParams);
            $stmt->execute();
            $filterResult = $stmt->get_result();
            // $filterResult = $this->connection->query($filterQuery);
            if ($filterResult===false) {
                throw new \Exception("Error on query >>> ".$this->connection->error);
            }
            $row = $filterResult->fetch_array();
            $result = $row[0];
            $metaData['filterRecords'] = $result;
        }

        $query = $this->bindParams($this->query, $this->sqlParams);
        // echo "query=$query<br>";
        // echo "this->sqlParams="; print_r($this->sqlParams); echo "<br>";
        // $result = $this->connection->query($query);
        $stmt = $this->prepareAndBind($query, $searchParams);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result===false) {
            throw new \Exception("Error on query >>> ".$this->connection->error);
        }

        $finfo = $result->fetch_fields();

        
        $numcols = count($finfo);
        for ($i=0; $i<$numcols; $i++) {
            $type = $this->mapFieldTypeToBindType($finfo[$i]->type);
                $metaData["columns"][$finfo[$i]->name] = array(
                        "type"=>$type,
                    );
            switch($type)
            {
            case "datetime":
                $metaData["columns"][$finfo[$i]->name]["format"] = "Y-m-d H:i:s";
                break;
            case "date":
                $metaData["columns"][$finfo[$i]->name]["format"] = "Y-m-d";
                break;
            case "time":
                $metaData["columns"][$finfo[$i]->name]["format"] = "H:i:s";
                break;          
            }
        }

        $this->sendMeta($metaData, $this);
    
        $this->startInput(null);
        
        while ($row = $result->fetch_assoc()) {
            $this->next($row, $this);
        }

        $this->endInput(null);
    }

    public function fetchFields($query)
    {
        $columns = [];
        $stmt = $this->prepareAndBind($query, []);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result===false) {
            throw new \Exception("Error on query >>> ".$this->connection->error);
        }
        $finfo = $result->fetch_fields();
        $numcols = count($finfo);
        for ($i=0; $i<$numcols; $i++) {
            $type = $this->mapFieldTypeToBindType($finfo[$i]->type);
                $columns[$finfo[$i]->name] = array(
                        "type"=>$type,
                    );
            switch($type)
            {
            case "datetime":
                $columns[$finfo[$i]->name]["format"] = "Y-m-d H:i:s";
                break;
            case "date":
                $columns[$finfo[$i]->name]["format"] = "Y-m-d";
                break;
            case "time":
                $columns[$finfo[$i]->name]["format"] = "H:i:s";
                break;          
            }
        }
        return $columns;
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
        $result = [];
        foreach ($queries as $key => $query) {
            $query = $this->bindParams($query, $this->sqlParams);
            // print_r($this->sqlParams); echo "<br>";
            // echo "query=$query<br>";
            $stmt = $this->prepareAndBind($query, []);
            $stmt->execute();
            $queryResult = $stmt->get_result();
            if ($result===false) {
                throw new \Exception("Error on query >>> ".$this->connection->error);
            }
            $rows = [];
            while ($row = $queryResult->fetch_assoc()) {
                // print_r($row); echo "<br>";
                $rows[] = $row;
            }
            $result[$key] = $rows;
        }
        return $result;
    }
}
