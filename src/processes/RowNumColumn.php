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
use \koolreport\core\Utility as Util;

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
class RowNumColumn extends Process
{
    protected $order = 0;

    public function __construct($params = null, $firstRowNumber = 0)
    {
        parent::__construct($params);
        $this->order = $firstRowNumber;
    }

    protected function onInit()
    {
        if (is_string($this->params)) {
            $this->params = [ $this->params ];
        }
    }

    protected function onMetaReceived($metaData)
    {
        Util::init($metaData, "columns", []);
        foreach ($this->params as $rowNumCol) {
            $metaData["columns"][$rowNumCol]["type"] = "number";
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
    protected function onInput($row)
    {
        if ($row === null) $row = [];
        foreach ($this->params as $rowNumCol) {
            $row[$rowNumCol] = $this->order;
        }
        $this->order++;
        $this->next($row);
    }
}
