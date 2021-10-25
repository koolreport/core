<?php
/**
 * This file contain class to handle pulling data from MySQL, Oracle, SQL Server and many others.
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

 /*
 For Pdo with Oracle for Apache on Windows:
    - Install Oracle database 32 bit only (php on Windows is only 32 bit).
    - Download and extract Oracle Instant Client 32 bit, add the extracted folder
    to Windows' Path environment variable.
    - Enable extension=php_pdo_oci.dll in php.ini.
    - Restart Apache.

    "pdoOracle"=>array(
        'connectionString' => 'oci:dbname=//localhost:1521/XE',
        'username' => 'sa',
        'password' => 'root',
        'class' => "\koolreport\datasources\PdoDataSource",
    ),
 */

namespace koolreport\datasources;

use \koolreport\core\DataSource;
use \koolreport\core\Utility as Util;
use PDO;

/**
 * PDODataSource helps to connect to various databases such as MySQL, SQL Server or Oracle
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class PdoDataSource extends DataSource
{
    /**
     * List of available connections for reusing
     *
     * @var array $connections List of available connections for reusing
     */
    public static $connections;

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


    /**
     * Store error info if there is
     * @var array
     */
    protected $errorInfo;
    

    /**
     * Datasource initiation
     *
     * @return null
     */
    protected function onInit()
    {
        // $this->connection = Util::get($this->params,"connection",null);
        $connectionString = Util::get($this->params, "connectionString", "");
        $username = Util::get($this->params, "username", "");
        $password = Util::get($this->params, "password", "");
        $charset = Util::get($this->params, "charset");
        $options = Util::get($this->params, "options");
        
        $key = md5($connectionString.$username.$password);
        if (PdoDataSource::$connections==null) {
            PdoDataSource::$connections = array();
        }
        if (isset(PdoDataSource::$connections[$key])) {
            $this->connection = PdoDataSource::$connections[$key];
        } else {
            $this->connection = new PDO(
                $connectionString,
                $username,
                $password,
                $options
            );
            PdoDataSource::$connections[$key] = $this->connection;
        }
        if ($charset) {
            $this->connection->exec("set names '$charset'");
        }
    }

    /**
     * Set the query and params
     *
     * @param string $query     The SQL query statement
     * @param array  $sqlParams The parameters of SQL query
     *
     * @return PdoDataSource This datasource object
     */
    public function query($query, $sqlParams=null)
    {
        $this->originalQuery = $this->query =  (string)$query;
        if ($sqlParams!=null) {
            $this->sqlParams = $sqlParams;
        }
        return $this;
    }

    public function escapeStr($value)
    {
        return $this->connection->quote($value);
    }

    /**
     * Transform query
     *
     * @param array $queryParams Parameters of query
     *
     * @return null
     */
    public function queryProcessing($queryParams)
    {
        $this->queryParams = $queryParams;
        $driver = strtolower($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));
        //drivers = Array ( [0] => mysql [1] => oci [2] => pgsql [3] => sqlite [4] => sqlsrv )
        switch ($driver) {
            case 'mysql':
                list($this->query, $this->totalQuery, $this->filterQuery)
                    = MySQLDataSource::processQuery($this->originalQuery, $queryParams);
                break;
            case 'oci':
                list($this->query, $this->totalQuery, $this->filterQuery)
                    = OracleDataSource::processQuery($this->originalQuery, $queryParams);
                break;
            case 'pgsql':
                list($this->query, $this->totalQuery, $this->filterQuery)
                    = PostgreSQLDataSource::processQuery($this->originalQuery, $queryParams);
                break;
            case 'sqlsrv':
                list($this->query, $this->totalQuery, $this->filterQuery)
                    = SQLSRVDataSource::processQuery($this->originalQuery, $queryParams);
                break;
            default:
                break;
        }
        
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
     * Prepare SQL statement
     *
     * @param string $query     Query need to bind params
     * @param array  $sqlParams The parameters will be bound to query
     *
     * @return string Procesed query
     */
    protected function prepareParams($query, $sqlParams)
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
        $resultQuery = $query;
        $paramNum = 0;
        foreach ($sqlParams as $paName => $paValue) {
            if (gettype($paValue)==="array") {
                $paramList = [];
                foreach ($paValue as $i=>$value) {
                    // $paramList[] = $paName . "_param$i";
                    $paramList[] = ":pdoParam$paramNum";
                    $paramNum++;
                }
                $resultQuery = str_replace($paName, implode(",", $paramList), $resultQuery);
            }
        }
        return $resultQuery;
    }

    /**
     * Convert type to PdoParamType
     *
     * @param string $type Type
     *
     * @return intger The PDO Param Type
     */
    protected function typeToPDOParamType($type)
    {
        switch ($type) {
            case "boolean":
                return PDO::PARAM_BOOL;
            case "integer":
                return PDO::PARAM_INT;
            case "NULL":
                return PDO::PARAM_NULL;
            case "resource":
                return PDO::PARAM_LOB;
            case "double":
            case "string":
            default:
                return PDO::PARAM_STR;
        }
    }

    /**
     * Perform data binding
     *
     * @param string $stm       Query need to bind params
     * @param array  $sqlParams The parameters will be bound to query
     *
     * @return null
     */
    protected function bindParams($stm, $sqlParams)
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
        $paramNum = 0;
        foreach ($sqlParams as $paName => $paValue) {
            $type = gettype($paValue);
            if ($type === 'array') {
                foreach ($paValue as $i=>$value) {
                    $paramType = $this->typeToPDOParamType(gettype($value));
                    $paramName = ":pdoParam$paramNum";
                    $paramNum++;
                    $stm->bindValue($paramName, $value, $paramType);
                }
            } else {
                $paramType = $this->typeToPDOParamType($type);
                // echo "paramType=$paramType <br>";
                // echo "paValue=$paValue <br>";
                $stm->bindValue($paName, $paValue, $paramType);
            }
        }
    }

    /**
     * Guess type
     *
     * @param string $native_type Native type of PDO
     *
     * @return string KoolReport type
     */
    protected function guessType($native_type)
    {
        $map = array(
            "character"=>"string",
            "char"=>"string",
            "string"=>"string",
            "str"=>"string",
            "text"=>"string",
            "blob"=>"string",
            "binary"=>"string",
            "enum"=>"string",
            "set"=>"string",
            "int"=>"number",
            "double"=>"number",
            "float"=>"number",
            "long"=>"number",
            "numeric"=>"number",
            "decimal"=>"number",
            "real"=>"number",
            "tinyint"=>"number",
            "bit"=>"number",
            "boolean"=>"number",
            "datetime"=>"datetime",
            "date"=>"date",
            "time"=>"time",
            "year"=>"datetime",
        );
        
        $native_type = strtolower($native_type);
        
        foreach ($map as $key=>$value) {
            if (strpos($native_type, $key)!==false) {
                return $value;
            }
        }
        return "unknown";
    }

    /**
     * Guess type from value
     *
     * @param mixed $value The value
     *
     * @return string Type of value
     */
    protected function guessTypeFromValue($value)
    {
        $map = array(
            "float"=>"number",
            "double"=>"number",
            "int"=>"number",
            "integer"=>"number",
            "bool"=>"number",
            "numeric"=>"number",
            "string"=>"string",
        );

        $type = strtolower(gettype($value));
        foreach ($map as $key=>$value) {
            if (strpos($type, $key)!==false) {
                return $value;
            }
        }
        return "unknown";
    }
    
    /**
     * Start piping data
     *
     * @return null
     */
    public function start()
    {
        // $this->connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $metaData = array("columns"=>array());

        $searchParams = Util::get($this->queryParams, 'searchParams', []);

        if ($this->countTotal) {
            $totalQuery = $this->prepareParams($this->totalQuery, $this->sqlParams);
            $stm = $this->connection->prepare($totalQuery);
            // echo "totalQuery=$totalQuery<br>";
            $this->bindParams($stm, $this->sqlParams);
            $stm->execute();
            $error = $stm->errorInfo();
            if ($error[2]!=null) {
                throw new \Exception("Query Error >> [".$error[2]."] >> $totalQuery");
                return;
            }
            $row = $stm->fetch();
            $result = $row[0];
            $metaData['totalRecords'] = $result;
        }

        if ($this->countFilter) {
            $filterQuery = $this->prepareParams($this->filterQuery, $this->sqlParams);
            // $filterQuery = $this->prepareParams($this->filterQuery, $searchParams);
            $stm = $this->connection->prepare($filterQuery);
            // echo "filterQuery=$filterQuery<br>";
            $this->bindParams($stm, $this->sqlParams);
            $this->bindParams($stm, $searchParams);
            // if (! empty($this->queryParams['searchParams']))
            //     $this->bindParams($stm, $this->queryParams['searchParams']);
            $stm->execute();
            $error = $stm->errorInfo();
            if ($error[2]!=null) {
                throw new \Exception("Query Error >> [".$error[2]."] >> $filterQuery");
                return;
            }
            $row = $stm->fetch();
            $result = $row[0];
            $metaData['filterRecords'] = $result;
        }
        $row = null;

        $query = $this->prepareParams($this->query, $this->sqlParams);
        // $query = $this->prepareParams($this->query, $searchParams);
        // echo "pdodatasource start query=$query <br>";
        $stm = $this->connection->prepare($query);
        $this->bindParams($stm, $this->sqlParams);
        $this->bindParams($stm, $searchParams);
        // if (! empty($this->queryParams['searchParams']))
        //     $this->bindParams($stm, $this->queryParams['searchParams']);
        $stm->execute();

        $error = $stm->errorInfo();
        // if($error[2]!=null)
        if ($error[0]!='00000') {
            throw new \Exception("Query Error >> [".$error[2]."] >> $query");
            return;
        }

        $driver = strtolower($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));
        $metaSupportDrivers = array('dblib', 'mysql', 'pgsql', 'sqlite');
        $metaSupport = false;
        foreach ($metaSupportDrivers as $supportDriver) {
            if (strpos($driver, $supportDriver) !== false) {
                $metaSupport = true;
            }
        }
            
        if (!$metaSupport) {
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $cNames = empty($row) ? array() : array_keys($row);
            $numcols = count($cNames);
        } else {
            $numcols = $stm->columnCount();
        }

        // $metaData = array("columns"=>array());
        for ($i=0;$i<$numcols;$i++) {
            if (! $metaSupport) {
                $cName = $cNames[$i];
                $cType = $this->guessTypeFromValue($row[$cName]);
            } else {
                $info = $stm->getColumnMeta($i);
                $cName = $info["name"];
                $cType = $this->guessType(Util::get($info, "native_type", "unknown"));
            }
            $metaData["columns"][$cName] = array(
                "type"=>$cType,
            );
            switch ($cType) {
            case "datetime":
                $metaData["columns"][$cName]["format"] = "Y-m-d H:i:s";
                break;
            case "date":
                $metaData["columns"][$cName]["format"] = "Y-m-d";
                break;
            case "time":
                $metaData["columns"][$cName]["format"] = "H:i:s";
                break;
            }
        }
                
        $this->sendMeta($metaData, $this);
        $this->startInput(null);
                                
        if (! isset($row)) {
            $row=$stm->fetch(PDO::FETCH_ASSOC);
        }
            
        while ($row) {
            $this->next($row, $this);
            $row=$stm->fetch(PDO::FETCH_ASSOC);
        }
        $this->endInput(null);
        // $stm->closeCursor();
    }

    public function fetchFields($query)
    {
        $columns = [];
        $query = $this->prepareParams($query, []);
        $stm = $this->connection->prepare($query);
        $stm->execute();
        $error = $stm->errorInfo();
        // if($error[2]!=null)
        if ($error[0]!='00000') {
            throw new \Exception("Query Error >> [".$error[2]."] >> $query");
            return;
        }
        $driver = strtolower($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));
        $metaSupportDrivers = array('dblib', 'mysql', 'pgsql', 'sqlite');
        $metaSupport = false;
        foreach ($metaSupportDrivers as $supportDriver) {
            if (strpos($driver, $supportDriver) !== false) {
                $metaSupport = true;
            }
        }
            
        if (!$metaSupport) {
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $cNames = empty($row) ? array() : array_keys($row);
            $numcols = count($cNames);
        } else {
            $numcols = $stm->columnCount();
        }

        // $metaData = array("columns"=>array());
        for ($i=0;$i<$numcols;$i++) {
            if (! $metaSupport) {
                $cName = $cNames[$i];
                $cType = $this->guessTypeFromValue($row[$cName]);
            } else {
                $info = $stm->getColumnMeta($i);
                $cName = $info["name"];
                $cType = $this->guessType(Util::get($info, "native_type", "unknown"));
            }
            $columns[$cName] = array(
                "type"=>$cType,
            );
            switch ($cType) {
            case "datetime":
                $columns[$cName]["format"] = "Y-m-d H:i:s";
                break;
            case "date":
                $columns[$cName]["format"] = "Y-m-d";
                break;
            case "time":
                $columns[$cName]["format"] = "H:i:s";
                break;
            }
        }
        return $columns;
    }

    public function fetchData($query, $queryParams = null)
    {
        if (isset($queryParams) && 
            (isset($queryParams['countTotal']) || isset($queryParams['countFilter']))) {
            $driver = strtolower($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));
            switch ($driver) {
                case 'mysql':
                    list($query, $totalQuery, $filterQuery)
                        = MySQLDataSource::processQuery($query, $queryParams);
                    break;
                case 'oci':
                    list($query, $totalQuery, $filterQuery)
                        = OracleDataSource::processQuery($query, $queryParams);
                    break;
                case 'pgsql':
                    list($query, $totalQuery, $filterQuery)
                        = PostgreSQLDataSource::processQuery($query, $queryParams);
                    break;
                case 'sqlsrv':
                    list($query, $totalQuery, $filterQuery)
                        = SQLSRVDataSource::processQuery($query, $queryParams);
                    break;
                default:
                    break;
            }
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
        // echo "fetData queries = "; Util::prettyPrint($queries);
        $result = [];
        foreach ($queries as $key => $query) {
            $query = $this->prepareParams($query, $this->sqlParams);
            $stm = $this->connection->prepare($query);
            $this->bindParams($stm, $this->sqlParams);
            $stm->execute();
    
            $error = $stm->errorInfo();
            // if($error[2]!=null)
            if ($error[0]!='00000') {
                throw new \Exception("Query Error >> [".$error[2]."] >> $query");
                return;
            }
    
            $rows = [];
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = $row;
            }

            $result[$key] = $rows;
        }

        return $result;
    }

    public function errorInfo()
    {
        return $this->errorInfo;
    }

    /**
     * General way to execute a query
     * @param mixed $sql 
     * @return boolean Whether the sql is succesfully executed 
     */
    public function execute($sql, $params=null)
    {
        if(is_array($params)) {
            //Need prepare
            $stm = $this->connection->prepare($sql);
            $success = $stm->execute($params);
            if($success===false) {
                $this->errorInfo = $stm->errorInfo();
            } else {
                $this->errorInfo = null;
            } 
        } else {
            $success = $this->connection->exec($sql);
            if($success===false) {
                $this->errorInfo = $this->connection->errorInfo();
            } else {
                $this->errorInfo = null;
            }
        }
        return $success!==false;
    }
}