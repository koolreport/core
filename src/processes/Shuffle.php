<?php
/**
 * This file contains class to handle data sorting
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

/* Usage
 * ->pipe(new Sort(array(
 *         "amount"=>"desc",
 *         "id"=>"asc"
 * )))
 *  */
namespace koolreport\processes;

use \koolreport\core\Process;
use \koolreport\core\Utility as Util;

/**
 * This file contains class to handle data sorting
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class Shuffle extends Process
{
    protected $data = array();

    /**
     * Handle on data input
     *
     * @param array $row The input data row
     *
     * @return null
     */
    protected function onInput($row)
    {
        array_push($this->data, $row);
    }

    /**
     * Do sorting
     * 
     * @return null
     */
    public function shuffle()
    {
        shuffle($this->data);
    }

    /**
     * Handle on input end
     * 
     * @return null
     */
    public function onInputEnd()
    {
        $this->shuffle();
        foreach ($this->data as $row) {
            $this->next($row);
        }
        unset($this->data);
    }
}
