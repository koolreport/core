<?php
/**
 * This file contains class to join two data flow on a condition.
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

/* Usage
 * (new Join(array($source1,source2,array("id1"=>"id2"))))
 *
 *
 */
namespace koolreport\processes;

use \koolreport\core\Process;

/**
 * This file contains class to join two data flow on a condition.
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class LeftJoin extends Join
{
    /**
     * Handle on input end
     * 
     * @return null
     */
    protected function onInputEnd()
    {
        foreach ($this->container[0]["data"] as $key => $rows) {
            if (isset($this->container[1]["data"][$key])) {
                foreach ($rows as $first) {
                    foreach ($this->container[1]["data"][$key] as $second) {
                        $this->next(array_merge($first, $second));
                    }
                }
                unset($this->container[1]["data"][$key]);
            } else {
                foreach ($rows as $first) {
                    foreach ($rows as $first) {
                        $mergedRow = $first;
                        foreach ($this->secondSideKeys as $k) {
                            if (!isset($mergedRow[$k])) $mergedRow[$k] = null;
                        }
                        $this->next($mergedRow);
                    }
                }
            }
            unset($this->container[0]["data"][$key]);
        }
    }

}
