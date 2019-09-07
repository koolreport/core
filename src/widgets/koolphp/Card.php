<?php
/**
 * This file contains Card widget
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */


// Card::create(array(
//     "value"=>1249,
//     "baseValue"=>1500,
//     "indicator"=>"different",
//     "format"=>array(
//         "value"=>array(
//             "prefix"=>"$"
//         ),
//         "indicator"=>array(
//             "suffix"=>""
//         )
//     ),
//     "cssStyle"=>array(
//         "negative"=>"color:#ddd",
//         "positive"=>"color:#0f0",
//         "indicator"=>"font-size:16px",
//         "card"=>"border-color:#999;background:yellow;",
//         "value"=>"color:blue",
//         "title"=>"color:green",
//     ),
//     "cssClass"=>array(
//         "negative"=>"test-negative",
//         "positive"=>"test-positive",
//         "indicator"=>"test-indicator",
//         "card"=>"test-card",
//         "value"=>"test-value",
//         "title"=>"test-title",
//         "upIcon"=>"glyphicon glyphicon-heart",
//         "downIcon"=>"glyphicon glyphicon-remove"
//     ),
//     "title"=>"Sale Amount",
//     "indicatorTitle"=>"Compared to {baseValue}"
// ));

namespace koolreport\widgets\koolphp;

use \koolreport\core\Utility;
use \koolreport\core\Widget;

/**
 * This file contains Card widget
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class Card extends Widget
{
    protected $title;
    protected $value;
    protected $valueFormat;
    protected $indicatorFormat;
    protected $baseValue;
    protected $indicator;
    protected $cssStyle;
    protected $cssClass;
    protected $indicatorTitle;
    protected $href;

    /**
     * OnInit
     *
     * @return null
     */
    protected function onInit()
    {
        $this->useAutoName("kcard");
        $this->value = Utility::get($this->params, "value");
        $this->baseValue = Utility::get($this->params, "baseValue");

        $this->value = $this->processScalar($this->value);
        $this->baseValue = $this->processScalar($this->baseValue);

        $title = Utility::get($this->params, "title");
        if (is_callable($title) && gettype($title)!="string") {
            $this->title = $title($this->value);
        } else {
            $this->title = $title;
        }
        
        $this->indicator = Utility::get($this->params, "indicator", "percent");

        $this->indicatorTitle = Utility::get($this->params, "indicatorTitle", "Compared to previous {baseValue}");

        $format = Utility::get($this->params, "format", array());
        $this->valueFormat = Utility::get($format, "value", array());
        $this->indicatorFormat = array_merge(
            array(
                "decimals"=>2,
                "suffix"=>"%",
            ),
            Utility::get(
                $format,
                "indicator", 
                array()
            )
        );

        $this->cssStyle = Utility::get($this->params, "cssStyle", array());
        $this->cssClass = Utility::get($this->params, "cssClass", array());
        $this->href = Utility::get($this->params, "href");
    }

    /**
     * Return scalar value from object like query
     * 
     * @param mixed $value The value which may be type of float or datastore, datasource, process object.
     * 
     * @return float The value in float
     */
    protected function processScalar($value)
    {
        if (gettype($value)=="object") {
            $store = $this->standardizeDataSource($value, array());
            if ($store->count()>0) {
                $row = $store->get(0);
                $keys = array_keys($row);
                if (count($keys)>0) {
                    return $row[$keys[0]];
                }
                return 0;
            }
        }
        return $value;
    }

    /**
     * Return the resource settings for table
     *
     * @return array The resource settings of table widget
     */
    protected function resourceSettings()
    {
        return array(
            "library" => array("jQuery","font-awesome"),
            "folder" => "card",
            "css" => array("card.css"),
        );
    }

    /**
     * Format value
     *
     * @param mixed $value  The value need to format
     * @param array $format Format will be applied
     *
     * @return string Formatted value
     */
    protected function formatValue($value, $format)
    {
        if (is_callable($format)) {
            return $format($value);
        } else {
            $format["type"] = "number";
            return Utility::format($value, $format);
        }
    }

    /**
     * Return the percentage increase or decrease to previous Value
     *
     * @return float The percentage increase/decrease
     */
    protected function calculateIndicator($value, $baseValue, $indicator)
    {
        if ($indicator == "percent") {
            if ($baseValue !== null && $baseValue !== 0) {
                return ($value - $baseValue) * 100 / $baseValue;
            }
        } else if ($indicator=="different") {
            return $value - $baseValue;
        } else if (is_callable($indicator)) {
            return $indicator($value, $baseValue);
        }
        return false;
    }
    /**
     * Return the formatted href
     * 
     * @return string Formatted href
     */
    protected function getHref()
    {
        if ($this->href) {
            $href = trim($this->href);
            if (strpos($href, "function") === 0) {
                return 'onclick="javascript: var __cardclick='.$href.';__cardclick();" ';
            } else {
                return 'onclick="window.location.href=\''.$href.'\';" ';
            }
        }
        return null;
    }

}
