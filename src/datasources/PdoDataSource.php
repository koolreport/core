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

    public $originalQuery;
    
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

    protected $stm;

    protected $firstRow;

    protected $builtMetaData;

    protected $endOfStm;

    protected $totalQuery;

    protected $filterQuery;

    protected $aggregates;


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

        $key = md5($connectionString . $username . $password);
        if (PdoDataSource::$connections == null) {
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

        if (isset($this->params["MysqlBufferedQuery"])) {
            $mysqlBuffer = $this->params["MysqlBufferedQuery"];
            $this->connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $mysqlBuffer);
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
    public function query($query, $sqlParams = null)
    {
        $this->originalQuery = $this->query =  (string)$query;
        if ($sqlParams != null) {
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
                list($this->query, $this->totalQuery, $this->filterQuery, $this->aggregates)
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
     * @return PdoDataSource This datasource
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
    protected function prepareAndBind($query, $params)
    {
        if (empty($params)) {
            $params = [];
        }
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

        // Spread array parameters
        $query = $query;
        foreach ($paNames as $paName) {
            $paValue = $params[$paName];
            if (gettype($paValue) === "array") {
                $numValues = strlen((string)count($paValue));
                $paramList = [];
                foreach ($paValue as $i => $value) {
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
        // echo "params = "; print_r($params); echo "<br><br>";

        $newParams = [];
        $hashedPaNames = [];
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
                    // $hashedPaName = $newPaName;
                    $hashedPaName = md5($newPaName);
                    $hashedPaNames[$newPaName] = $hashedPaName;
                    $query = substr_replace($query, $hashedPaName, $pos, strlen($paName));
                }
                $count++;
            }
        }
        foreach ($newParams as $newPaName => $value) {
            $hashedPaName = $hashedPaNames[$newPaName];
            $query = str_replace($hashedPaName, $newPaName, $query);
        }

        // echo "query = $query<br><br>";
        // echo "newParams = "; print_r($newParams); echo "<br><br>";

        $stm = $this->connection->prepare($query);

        // $paramNum = 0;
        // $newParams[":a"] = 1;
        foreach ($newParams as $paName => $paValue) {
            $type = gettype($paValue);
            $paramType = $this->typeToPDOParamType($type);
            // echo "paramType=$paramType <br>";
            // echo "paValue=$paValue <br>";
            $stm->bindValue($paName, $paValue, $paramType);
        }
        
        return $stm;
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
     * Guess type
     *
     * @param string $native_type Native type of PDO
     *
     * @return string KoolReport type
     */
    protected function guessType($native_type)
    {
        $map = array(
            "character" => "string",
            "char" => "string",
            "string" => "string",
            "str" => "string",
            "text" => "string",
            "blob" => "string",
            "binary" => "string",
            "enum" => "string",
            "set" => "string",
            "int" => "number",
            "double" => "number",
            "float" => "number",
            "long" => "number",
            "numeric" => "number",
            "decimal" => "number",
            "real" => "number",
            "tinyint" => "number",
            "bit" => "number",
            "boolean" => "number",
            "datetime" => "datetime",
            "date" => "date",
            "time" => "time",
            "year" => "datetime",
        );

        $native_type = strtolower($native_type);

        foreach ($map as $key => $value) {
            if (strpos($native_type, $key) !== false) {
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
            "float" => "number",
            "double" => "number",
            "int" => "number",
            "integer" => "number",
            "bool" => "number",
            "numeric" => "number",
            "string" => "string",
        );

        $type = strtolower(gettype($value));
        foreach ($map as $key => $value) {
            if (strpos($type, $key) !== false) {
                return $value;
            }
        }
        return "unknown";
    }

    protected function buildMetaData()
    {
        // echo "pdodatasource buildMetaData<br>";
        $metaData = array("columns" => array());

        if (empty($this->sqlParams)) $this->sqlParams = [];

        $searchParams = Util::get($this->queryParams, 'searchParams', []);
        if (empty($searchParams)) $searchParams = [];

        if ($this->countTotal) {
            $stm = $this->prepareAndBind($this->totalQuery, $this->sqlParams);
            $stm->execute();
            $error = $stm->errorInfo();
            if ($error[2] != null) {
                throw new \Exception(
                    "Query Error >> " . json_encode($error) . " >> $this->totalQuery"
                        . " || Sql params = " . json_encode($this->sqlParams)
                );
                return;
            }
            $row = $stm->fetch();
            $stm->closeCursor();
            $result = $row[0];
            $metaData['totalRecords'] = $result;
        }

        if ($this->countFilter) {
            $stm = $this->prepareAndBind($this->filterQuery, array_merge($this->sqlParams, $searchParams));
            $stm->execute();
            $error = $stm->errorInfo();
            if ($error[2] != null) {
                throw new \Exception(
                    "Query Error >> " . json_encode($error) . " >> $this->filterQuery"
                        . " || Sql params = " . json_encode($this->sqlParams)
                        . " || Search params = " . json_encode($searchParams)
                );
            }
            $row = $stm->fetch();
            $stm->closeCursor();
            $result = $row[0];
            $metaData['filterRecords'] = $result;
        }

        if (!empty($this->aggregates)) {
            foreach ($this->aggregates as $aggregate) {
                $operator = $aggregate["operator"];
                $field = $aggregate["field"];
                $aggQuery = $aggregate["aggQuery"];
                // $aggQuery = $this->prepareParams($aggQuery, $this->sqlParams);
                // $stm = $this->connection->prepare($aggQuery);
                // $this->bindParams($stm, $this->sqlParams);
                // $this->bindParams($stm, $searchParams);
                $stm = $this->prepareAndBind($aggQuery, array_merge($this->sqlParams, $searchParams));
                $stm->execute();
                $error = $stm->errorInfo();
                if ($error[2] != null) {
                    throw new \Exception(
                        "Query Error >> " . json_encode($error) . " >> $aggQuery"
                            . " || Sql params = " . json_encode($this->sqlParams)
                            . " || Search params = " . json_encode($searchParams)
                    );
                }
                $row = $stm->fetch();
                $stm->closeCursor();
                $result = $row[0];
                Util::set($metaData, ['aggregates', $operator, $field], $result);
            }
        }

        $driver = strtolower($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME));
        $metaSupportDrivers = array('dblib', 'mysql', 'pgsql', 'sqlite');
        $metaSupport = false;
        foreach ($metaSupportDrivers as $supportDriver) {
            if (strpos($driver, $supportDriver) !== false) {
                $metaSupport = true;
            }
        }

        $query = $this->query;
        $this->stm = $this->prepareAndBind($query, array_merge($this->sqlParams, $searchParams));
        $this->stm->execute();

        $error = $this->stm->errorInfo();
        // if($error[2]!=null)
        if ($error[0] != '00000') {
            throw new \Exception(
                "Query Error >> " . json_encode($error) . " >> $query"
                    . " || Sql params = " . json_encode($this->sqlParams)
                    . " || Search params = " . json_encode($searchParams)
            );
        }

        $this->firstRow = null;
        if (!$metaSupport) {
            $this->firstRow = $row = $this->stm->fetch(PDO::FETCH_ASSOC);
            $cNames = empty($row) ? array() : array_keys($row);
            $numcols = count($cNames);
        } else {
            $numcols = $this->stm->columnCount();
        }

        for ($i = 0; $i < $numcols; $i++) {
            if (!$metaSupport) {
                $cName = $cNames[$i];
                $cType = $this->guessTypeFromValue($row[$cName]);
            } else {
                $info = $this->stm->getColumnMeta($i);
                $cName = $info["name"];
                $cType = $this->guessType(Util::get($info, "native_type", "unknown"));
            }
            $metaData["columns"][$cName] = array(
                "type" => $cType,
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

        $this->builtMetaData = $metaData;
    }

    /**
     * Start piping data
     *
     * @return null
     */
    public function start()
    {
        // echo "pdodatasource start()<br>";

        $this->buildMetaData();
        $this->sendMeta($this->builtMetaData, $this);

        $this->startInput(null);

        if (!isset($this->firstRow)) {
            $row = $this->stm->fetch(PDO::FETCH_ASSOC);
        } else {
            $row = $this->firstRow;
        }

        while ($row) {
            $this->next($row, $this);
            $row = $this->stm->fetch(PDO::FETCH_ASSOC);
        }
        $this->endInput(null);
        $this->stm->closeCursor();
        $this->endOfStm = true;
    }

    public function startMetaOnly()
    {
        // echo "pdodatasource startMetaOnly<br>";
        if (!isset($this->builtMetaData)) {
            $this->buildMetaData();
            $this->sendMeta($this->builtMetaData, $this);
        }
    }

    public function startGenerator($genName)
    {
        // echo "pdodatasource startGenerator<br>";
        $this->startInput(null);

        $report = $this->getReport();
        $report->saveDataGenRow = $genName;

        if (isset($this->endOfStm) && $this->endOfStm === true) {
            $this->stm->execute();
            $this->firstRow = $this->stm->fetch(PDO::FETCH_ASSOC);
            $this->endOfStm = false;            
        }

        if (!isset($this->firstRow)) {
            $row = $this->stm->fetch(PDO::FETCH_ASSOC);
        } else {
            $row = $this->firstRow;
        }

        if (!isset($report->dataGenRow)) $report->dataGenRow = [];
        while ($row) {
            $this->next($row, $this);
            foreach ($report->dataGenRow as $outGenName => $rows) {
                if ($outGenName !== $genName) {
                    $report->dataGenRow[$outGenName] = [];
                    continue;
                }
                if (!empty($rows)) {
                    foreach ($rows as $row) yield $genName => $row;
                    $report->dataGenRow[$genName] = [];
                }
            }
            $row = $this->stm->fetch(PDO::FETCH_ASSOC);
        }
        $report->saveDataGenRow = null;
        $this->endInput(null);
        $this->endOfStm = true;
        $this->stm->closeCursor();
    }

    public function fetchFields($query)
    {
        $columns = [];
        // $query = $this->prepareParams($query, []);
        // $stm = $this->connection->prepare($query);
        $stm = $this->prepareAndBind($query, $this->sqlParams);
        $stm->execute();
        $error = $stm->errorInfo();
        // if($error[2]!=null)
        if ($error[0] != '00000') {
            throw new \Exception(
                "Query Error >> " . json_encode($error) . " >> $query"
            );
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
        for ($i = 0; $i < $numcols; $i++) {
            if (!$metaSupport) {
                $cName = $cNames[$i];
                $cType = $this->guessTypeFromValue($row[$cName]);
            } else {
                $info = $stm->getColumnMeta($i);
                $cName = $info["name"];
                $cType = $this->guessType(Util::get($info, "native_type", "unknown"));
            }
            $columns[$cName] = array(
                "type" => $cType,
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
        $stm->closeCursor();
        return $columns;
    }

    public function fetchData($query, $queryParams = null)
    {
        if (
            isset($queryParams) &&
            (isset($queryParams['countTotal']) || isset($queryParams['countFilter']))
        ) {
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
            // $query = $this->prepareParams($query, $this->sqlParams);
            // $stm = $this->connection->prepare($query);
            // $this->bindParams($stm, $this->sqlParams);
            $stm = $this->prepareAndBind($query, $this->sqlParams);
            $stm->execute();

            $error = $stm->errorInfo();
            // if($error[2]!=null)
            if ($error[0] != '00000') {
                throw new \Exception(
                    "Query Error >> " . json_encode($error) . " >> $query"
                        . " || Sql params = " . json_encode($this->sqlParams)
                );
            }

            $rows = [];
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = $row;
            }

            $result[$key] = $rows;
        }
        $stm->closeCursor();
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
    public function execute($sql, $params = null)
    {
        if (is_array($params)) {
            //Need prepare
            // $stm = $this->connection->prepare($sql);
            // $success = $stm->execute($params);
            $stm = $this->prepareAndBind($sql, $params);
            $success = $stm->execute();
            if ($success === false) {
                $this->errorInfo = $stm->errorInfo();
            } else {
                $this->errorInfo = null;
            }
            $stm->closeCursor();
        } else {
            $success = $this->connection->exec($sql);
            if ($success === false) {
                $this->errorInfo = $this->connection->errorInfo();
            } else {
                $this->errorInfo = null;
            }
        }
        return $success !== false;
    }
}
