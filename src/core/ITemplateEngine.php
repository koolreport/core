<?php
/**
 * The template engine interaface, this will be used to extend
 * capability to render report's view with different engine.
 * 
 * 
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

namespace koolreport\core;

/**
 * Theme class to handle theme settings for report
 * 
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
interface ITemplateEngine
{
    /**
     * Process the content view template
     * 
     * @param string $view   The path to view
     * @param array  $params The parameters passed to the view
     * 
     * @return string The rendered content
     */
    public function render($view, $params);
}