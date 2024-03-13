<?php
/**
 * This file contains class to pull data from CSV file
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

/*
 * The CSV will load the CSV data, breaking down to columns and try to determine
 * the type for the columns, the precision contain number of rows to run to determine
 * the meta data for columns.
 *
 * $firstRowData: is the first row data, usually is false, first row is column name
 * if the firstRowData is true, name column as column 1, column 2
 *
 */
namespace koolreport\datasources;

use \koolreport\core\DataSource;
use \koolreport\core\Utility;

/**
 * CSVDataSource helps to load data from csv file
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class CSVDataSource extends DataSource
{
    /**
     * The path to csv file
     * 
     * @var string $filePath The path to csv file
     */
    protected $filePath;
    
    /**
     * The seperator between field in file
     * 
     * @var string $fieldSeparator The seperator between field in file
     */
    protected $fieldSeparator;

    /**
     * The number of rows used to guess type of column
     * 
     * @var integer The number of rows used to guess type of column
     */
    protected $precision;

    /**
     * Set charset
     * 
     * @var string $charset Set charset
     */
    protected $charset;

    /**
     * Whether first row is data or columnName
     * 
     * @var bool $firstRowData Whether first row is data or columnName
     */
    protected $firstRowData;

    /**
     * Init the datasource
     * 
     * @return null
     */
    protected function onInit()
    {
        $this->filePath = Utility::get($this->params, "filePath");
        if ((fopen($this->filePath, "r")) !== false) {
        } else {
            throw new \Exception('Failed to open file: ' . $this->filePath);
        }
        $fieldSeparator = Utility::get($this->params, "fieldSeparator", ",");
        $this->fieldSeparator = Utility::get($this->params, "fieldDelimiter", $fieldSeparator);
        $this->charset = Utility::get($this->params, "charset");
        $this->precision = Utility::get($this->params, "precision", 100);
        $this->firstRowData = Utility::get($this->params, "firstRowData", false);
    }

    /**
     * Guess data type
     * 
     * @param mixed $value The value
     * 
     * @return string The type of value
     */
    protected function guessType($value)
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

    /**
     * Start piping data
     * 
     * @return null
     */
    public function start()
    {
        // $offset = 0;
        // //Go to where we were when we ended the last batch
        // fseek($fileHandle, $offset);
        // fgetcsv($fileHandle)
        // $offset = ftell($fileHandle);
            
        $data = array();
        $enclosure = '"';
        if (($handle = fopen($this->filePath, "r")) !== false) {
            $row = fgetcsv($handle, 0, $this->fieldSeparator, $enclosure);
            //Convert to UTF8 if assign charset to utf8
            $row = array_map(
                function ($item) {
                    return ($this->charset=="utf8" && is_string($item))
                        ? mb_convert_encoding($item, "UTF-8", mb_detect_encoding($item)) : $item;
                },
                $row
            );

            if (is_array($row)) {
                if (!$this->firstRowData) {
                    $columnNames = $row;
                } else {
                    $columnNames = array();
                    for ($i = 0; $i < count($row); $i++) {
                        array_push($columnNames, 'Column ' . $i);
                    }

                }

                $metaData = array("columns" => array());
                for ($i = 0; $i < count($columnNames); $i++) {
                    $metaData["columns"][$columnNames[$i]] = array(
                            "type" => (isset($row))
                                ?$this->guessType($row[$i]) : "unknown"
                        );
                }
                $this->sendMeta($metaData, $this);
                $this->startInput(null);

                if ($this->firstRowData) {
                    $this->next(array_combine($columnNames, $row), $this);
                }
            }
            while (($row = fgetcsv($handle, 0, $this->fieldSeparator)) !== false) {
                $row = array_map(
                    function ($item) {
                        return ($this->charset=="utf8" && is_string($item))
                            ?utf8_encode($item):$item;
                    },
                    $row
                );    
                $this->next(array_combine($columnNames, $row), $this);
            }
        } else {
            throw new \Exception('Failed to open file: ' . $this->filePath);
        }
        $this->endInput(null);
    }
}
