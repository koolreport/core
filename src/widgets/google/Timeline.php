<?php
/**
 * This file is wrapper class for Google Timeline
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

namespace koolreport\widgets\google;

use \koolreport\core\Utility;

/**
 * Google Timeline
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class Timeline extends Chart
{
    protected $stability = "current";
    protected $package = "timeline";

    /**
     * Handle on widget init
     * 
     * @return null
     */
    protected function onInit()
    {
        parent::onInit();
    }

    /**
     * Convert server-side date to client-side format
     * 
     * @param string $value The datetime
     * @param array  $meta  The meta data
     * 
     * @return string The client-side date format
     */
    // protected function newClientDate($value, $meta)
    // {
    //     $format = Utility::get($meta, "format");
    //     $type = Utility::get($meta, "type");
    //     switch ($type) {
    //     case "date":
    //         $format = $format?$format:"Y-m-d";
    //         $toFormat = "Y,(n-1),d";
    //         break;
    //     case "time":
    //         $format = $format?$format:"H:i:s";
    //         $toFormat = "0,0,0,H,i,s";
    //         break;
    //     case "datetime":
    //     default:
    //         $format = $format?$format:"Y-m-d H:i:s";
    //         $toFormat = "Y,(n-1),d,H,i,s";
    //         break;
    //     }
    //     //The (n-1) above is because in Javascript, month start from 0 to 11
    //     $date = \DateTime::createFromFormat($format, $value);

    //     if ($date) {
    //         // return "new Date(" . \DateTime::createFromFormat($format, $value)->format($toFormat) . ")";
    //         return "function() {return new Date(" . \DateTime::createFromFormat($format, $value)->format($toFormat) . ");}()";
    //     }
    //     return "null";
    // }

    /**
     * Handle on render
     * 
     * @return null
     */
    protected function onRender()
    {

        $columns = $this->getColumnSettings();

        //Update options
        $options = $this->options;
        if ($this->title) {
            $options["title"] = $this->title;
        }
        if ($this->colorScheme) {
            $options["colors"] = $this->colorScheme;
        }

        $this->template(
            'Timeline',
            array(
                "options" => $options,
                "columns" => $columns,
                "loader"=>array(
                    "package"=>$this->package,
                    "stability"=>$this->stability,
                    "language"=>$this->language,
                    "mapsApiKey"=>$this->mapsApiKey
                )
            ),
            // "Chart",
            // array(
            //     "chartType" => $this->type,
            //     "options" => $options,
            //     "data" => $this->prepareData(),
            //     "cKeys" => array_keys($columns),
            //     "loader"=>array(
            //         "package"=>$this->package,
            //         "stability"=>$this->stability,
            //         "language"=>$this->language,
            //         "mapsApiKey"=>$this->mapsApiKey
            //     )
            // )
        );
    }
}
