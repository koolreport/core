<?php
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

/* Usage
 * ->pipe(new JsonColumn(array(
 *      "column1","column2"
 * )))
 *
 * It will turn the column1 and column2 which contains json string into array type
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
class JsonColumn extends Process
{
    protected $jsonColumns;


    /**
     * Handle on initiation
     *
     * @return null
     */
    protected function onInit()
    {
        $this->jsonColumns = $this->params;
    }

    protected function onMetaReceived($metaData)
    {
        foreach ($this->jsonColumns as $key) {
            if (isset($metaData["columns"][$key])) {
                $metaData["columns"][$key]["type"] = "array";
            }
        }
        return $metaData;
    }

    /**
     * Handle on data input
     *
     * @param array $data The input data row
     *
     * @return null
     */
    protected function onInput($data)
    {
        foreach ($this->jsonColumns as $key) {
            if (isset($data[$key])) {
                if (gettype($data[$key])!=="array")
                {
                    $arr = json_decode($data[$key], true);
                    $data[$key] = $arr===null?[]:$arr;    
                }
            }
        }
        $this->next($data);
    }
}
