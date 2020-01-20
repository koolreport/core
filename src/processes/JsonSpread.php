<?php
/**
 * This file contains process to spread a column data to multiple column
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

/* Usage
 * ->pipe(new JsonSpread(array(
 *      "column1",
 *      "column2"=>array("name","age") 
 * )))
 *
 *
 */
namespace koolreport\processes;

use \koolreport\core\Process;
use \koolreport\core\Utility;

/**
 * This file contains process to define JSON column
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class JsonSpread extends Process
{
    protected $jsonColumns;
    protected $metaSent = false;

    /**
     * Handle on initiation
     *
     * @return null
     */
    protected function onInit()
    {
        $this->jsonColumns = array();
        foreach($this->params as $k=>$v)
        {
            if(gettype($v)==="array")
            {
                $this->jsonColumns[$k] = $v;
            }
            else
            {
                $this->jsonColumns[$v] = false;
            }
            
        }
        $this->metaSent = false;
    }

    /**
     * Handle on data input
     *
     * @param array $data The input data row
     *
     * @return null
     */
    protected function onInput($row)
    {
        if (!$this->metaSent) {
            $this->pushMeta($row);
            $this->metaSent = true;
        }

        foreach($this->jsonColumns as $cKey=>$cCols)
        {
            $cArray = json_decode($row[$cKey],true);
            if ($cArray!=null) {
                $listSubCols = ($cCols===false)?array_keys($cArray):$cCols;
                foreach($listSubCols as $subCol) {
                    $row[$cKey.".".$subCol] = $cArray[$subCol];
                }
            }
        }

        $this->next($row);
    }

    /**
     * Process and send data after receiving first row
     */
    protected function pushMeta($row)
    {
        $columns = array();
        foreach($this->jsonColumns as $cKey=>$cCols)
        {
            $cArray = json_decode($row[$cKey],true);
            if($cArray!=null)
            {
                $listSubCols = ($cCols===false)?array_keys($cArray):$cCols;
                foreach($listSubCols as $subCol) {
                    $columns[$cKey.".".$subCol] = array(
                        "type"=>Utility::guessType($cArray[$subCol])
                    );
                }

            }
        }
        $meta = $this->metaData;
        $meta["columns"] = array_merge($meta["columns"],$columns);
        $this->sendMeta($meta);
    }

}
