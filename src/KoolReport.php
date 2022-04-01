<?php
/**
 * This file contain definition for KoolReport
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

namespace koolreport;

use \koolreport\core\DataStore;
use \koolreport\core\ResourceManager;
use \koolreport\core\Utility;

/**
 * KoolReport class
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class KoolReport
{
    protected $params;
    protected $dataSources;
    protected $dataStores;
    protected $resourceManager;
    protected $theme;
    protected $templateEngine;
    protected $events;
    protected $reportSettings;
    public $generatorUsed = false;

    /**
     * Get the version of KoolReport
     *
     * @return string Version of KoolReport
     */
    public function version()
    {
        return "5.1.0";
    }

    /**
     * Return the javascript of KoolReport
     *
     * @return string Javascript of KoolReport
     */
    public static function js()
    {
        $jsPath = dirname(__FILE__) . "/clients/core/KoolReport.js";
        if (is_file($jsPath)) {
            return "<script type='text/javascript'>"
                .preg_replace('/\s+/S', " ", file_get_contents($jsPath)) 
                ."</script>";
        } else {
            throw new \Exception("Could not find KoolReport.js");
        }
    }

    /**
     * Should return the url of KoolReport.js in clients/core
     * Be written later
     *
     * @return string the url of Koolreport.js
     */
    public static function jsUrl()
    {

    }

    /**
     * KoolReport construction
     *
     * @param array $params The report parameters
     */
    public function __construct($params = array())
    {
        $this->params = $params;
        $this->events = array();
        $this->dataSources = array();
        $this->dataStores = array();
        $this->reportSettings = $this->settings();

        if ($this->fireEvent("OnBeforeServicesInit")) {
            foreach ($this->getServiceConstructs() as $serviceConstruct) {
                $this->$serviceConstruct();
            }
        }
        $this->fireEvent("OnServicesInit");

        $this->fireEvent("OnInit");
        if ($this->fireEvent("OnBeforeSetup")) {
            $this->setup();
        }
        $this->fireEvent("OnSetup");
        $this->fireEvent("OnInitDone");
    }

    /**
     * Register callback function to be called on certain events
     *
     * @param string   $name       Name of event
     * @param function $methodName A anonymous function to be called
     * @param bool     $prepend    Whether the event should be prepended.
     * 
     * @return KoolReport the report object
     */
    public function registerEvent($name, $methodName, $prepend = false)
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = array();
        }
        if (!in_array($methodName, $this->events[$name])) {
            if ($prepend) {
                array_unshift($this->events[$name], $methodName);
            } else {
                array_push($this->events[$name], $methodName);
            }
        }
        return $this;
    }

    /**
     * Fire an event with parameters in array form
     *
     * @param string $name   Name of event
     * @param array  $params Parameters going with the event
     * 
     * @return bool Approve or disapprove action
     */
    public function fireEvent($name, $params = null)
    {
        $handleList = Utility::get($this->events, $name, null);
        $result = true;
        if ($handleList) {
            foreach ($handleList as $methodName) {
                if (gettype($methodName) == "string") {
                    $return = $this->$methodName($params);
                } else {
                    $return = $methodName($params);
                }
                $result &= ($return !== null) ? $return : true;
            }
        }
        //Allow to write event handle in the report
        if (method_exists($this, $name)) {
            $return = $this->$name($params);
            $result &= ($return !== null) ? $return : true;
        }
        return $result;
    }

    /**
     * Return list of contruction methods of services
     *
     * @return array List of construction methods
     */
    protected function getServiceConstructs()
    {
        $serviceConstructs = array();
        $public_methods = get_class_methods($this);
        foreach ($public_methods as $method) {
            if (strpos($method, "__construct") === 0 && strlen($method) > 11) {
                array_push($serviceConstructs, $method);
            }
        }
        return $serviceConstructs;
    }

    /**
     * Get the resource manager
     *
     * @return ResourceManager the resource manager object
     */
    public function getResourceManager()
    {
        if (!$this->resourceManager) {
            $this->resourceManager = new ResourceManager($this);
        }
        return $this->resourceManager;
    }

    /**
     * Get theme if any. If there is no theme, null will be returned.
     *
     * @return Theme Theme object
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * This function will be override by decendant to define 
     * how data will be executed.
     * 
     * @return null
     */
    protected function setup()
    {
    }

    /**
     * This function will be override by decendant to define list of settings 
     * including dataSources.
     * 
     * @return array Settings in array
     */
    protected function settings()
    {
        return array();
    }

    /**
     * Get the report settings
     *
     * @return array Report settings
     */
    public function getSettings()
    {
        return $this->reportSettings;
    }

    /**
     * Get a new source
     *
     * @param string $name Name of the source, if no name specified
     *                     then get the first one
     * 
     * @return DataSource The data source object
     */
    protected function src($name = null)
    {
        $dataSources = Utility::get($this->reportSettings, "dataSources", array());

        if (count($dataSources) == 0) {
            throw new \Exception("There is no source available, please add at least one in the settings()");
            return false;
        }

        if (!$name) {
            $name = Utility::get(array_keys($dataSources), 0);
        }

        $dataSourceSetting = Utility::get($dataSources, $name);

        if (!$dataSourceSetting) {
            throw new \Exception("Datasource not found '$name'");
            return false;
        }
        $dataSourceClass = Utility::get(
            $dataSourceSetting,
            "class",
            '\koolreport\datasources\PdoDataSource'
        );
        $dataSourceClass = str_replace("/", "\\", $dataSourceClass);
        $dataSource = new $dataSourceClass($dataSourceSetting, $this);
        array_push($this->dataSources, $dataSource);
        return $dataSource;
    }

    /**
     * Get the data store with a name, if not found, create a new one
     *
     * @param string $name The name of data store
     * 
     * @return DataStore The datastore object
     */
    public function dataStore($name)
    {
        if (gettype($name) == "string") {
            if (!isset($this->dataStores[$name])) {
                $this->dataStores[$name] = new DataStore;
                $this->dataStores[$name]->name = $name;
            }
            return $this->dataStores[$name];
        } else {
            //$name's type is different from string
            //return everything for further process
            return $name;
        }
    }

    public function buildDataGenerator($genName)
    {
        // echo "KoolReport -> buildDataGenerator() genName = $genName <br>";
        $dataGens = [];
        foreach ($this->dataSources as $src) {
            // echo "src->startGenerator<br>";
            $dataGen = $src->startGenerator($genName);
            $dataGens[] = $dataGen;
        }
        $combinedGen = $this->combineGeneratorsSequentially($dataGens);
        return $combinedGen;
    }

    /**
        * Yield all values from $generator1, then all values from $generator2
        * Keys are preserved
        */
    protected function combineGeneratorsSequentially($generators)
    {
        foreach ($generators as $generator) {
            yield from $generator;
        }
    }

    /**
     * Yield a value from $generator1, then a value from $generator2, and so on
     * Keys are preserved
     */
    protected function combine_alternatively($generator1, $generator2)
    {
        while ($generator1->valid() || $generator2->valid()) {
            if ($generator1->valid()) {
                yield $generator1->key() => $generator1->current();
                $generator1->next();
            }
            if ($generator2->valid()) {
                yield $generator2->key() => $generator2->current();
                $generator2->next();
            }
        }
    }

    public function runGenerator($genName = null)
    {
        // echo "KoolReport -> runGenerator() <br>";
        foreach ($this->dataSources as $src) {
            $src->startMetaOnly();
        }
        if (!isset($genName)) {
            foreach ($this->dataStores as $genName => $v) {
                // echo "genName = $genName<br>";
                $this->dataStores[$genName]->rowGenerator = $this->buildDataGenerator($genName);
            }
        } else {
            $this->dataStores[$genName]->rowGenerator = $this->buildDataGenerator($genName);
        }
    }

    public function useGenerator($generatorUsed)
    {
        $this->generatorUsed = $generatorUsed;
        return $this;
    }

    /**
     * Get the data store with a name, if not found, create a new one,
     * This is alias/short name for dataStore() method
     *
     * @param string $name The name of data store
     * 
     * @return DataStore The datastore object
     */
    public function store($name)
    {
        return $this->dataStore($name);
    }


    /**
     * Run the report
     *
     * @return $this Return this report object
     */
    public function run()
    {
        if ($this->fireEvent("OnBeforeRun")) {
            if ($this->dataSources != null) {
                if ($this->generatorUsed) {
                    $this->runGenerator();
                } else {
                    foreach ($this->dataSources as $dataSource) {
                        if (!$dataSource->isEnded()) {
                            $dataSource->start();
                        }
                    }
                }
            }
        }
        $this->fireEvent("OnRunEnd");
        return $this;
    }

    /**
     * Return all datastore in array form
     * 
     * @return array Return all meta and data of all datastores in array form
     */
    public function toArray()
    {
        $stores = array();
        foreach($this->dataStores as $name=>$store)
        {
            $stores[$name] = $store->toArray();
        }
        return $stores;
    }

    /**
     * Return all datastore in json form
     * 
     * @return array Return all meta and data of all datastores in json form
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Return all datastore in xml form
     * 
     * @return array Return all meta and data of all datastores in xml form
     */
    public function getXml()
    {
        $xmlStores = "";
        foreach($this->dataStores as $name=>$store)
        {
            $reportName = Utility::getClassName($this);

            $xmlStore = "<datastore name=\"$name\">";
            $xmlStore .= "<meta>";
            $xmlStore .= "<columns>";
                $metaColumns = $store->meta()["columns"];
                foreach($metaColumns as $cName=>$cSettings)
                {
                    $xmlColumn = "<column type=\"".Utility::get($cSettings,"type","unknown")."\" >$cName</column>";
                    $xmlStore .= $xmlColumn; //Add column to store
                }
            $xmlStore .= "</columns>";
            $xmlStore .= "</meta>";
            
            $xmlStore .= "<data>";
                foreach($store as $row)
                {
                    $xmlRow = "<row>";
                    foreach($row as $k=>$v)
                    {
                        $xmlRow .= "<$k>$v</$k>";
                    }
                    $xmlRow .= "</row>";
                    $xmlStore .= $xmlRow; // Add row to store
                }
            $xmlStore .= "</data>";
            $xmlStore.= "</datastore>";
            $xmlStores.=$xmlStore; // Add each store to main stores
        }
        return "<report name=\"$reportName\"><datastores>$xmlStores</datastores></report>";
    }


    /**
     * Return debug view
     * 
     * @return null
     */
    public function debug()
    {
        $oldActiveReport = (isset($GLOBALS["__ACTIVE_KOOLREPORT__"])) 
            ? $GLOBALS["__ACTIVE_KOOLREPORT__"] : null;
        $GLOBALS["__ACTIVE_KOOLREPORT__"] = $this;
        include dirname(__FILE__) . "/debug.view.php";
        if ($oldActiveReport === null) {
            unset($GLOBALS["__ACTIVE_KOOLREPORT__"]);
        } else {
            $GLOBALS["__ACTIVE_KOOLREPORT__"] = $oldActiveReport;
        }
    }

    /**
     * Render inner view
     *
     * @param string $view   The view name
     * @param array  $params Parameters for the view
     * @param bool   $return Wher the string should be return of be rendered
     *
     * @return string If the $return is set to true then string representing 
     *                the view will be return.
     */
    public function innerView($view, $params = null, $return = false)
    {
        $currentDir = dirname(Utility::getClassPath($this));
        ob_start();
        if ($params) {
            foreach ($params as $key => $value) {
                $$key = $value;
            }
        }
        include $currentDir . "/" . $view . ".view.php";
        $content = ob_get_clean();
        if ($return) {
            return $content;
        } else {
            echo $content;
        }
    }

    /**
     * Reder the view, if no view specified then try to look for the 
     * view with name {report_class}.view.php
     * If not found, render debug view
     *
     * @param string $view   The view name
     * @param bool   $return Whether return the view intead of rendering
     * 
     * @return bool If true the view will be returned 
     *                     instead of being rendered
     */
    public function render($view = null, $return = false)
    {

        $currentDir = dirname(Utility::getClassPath($this));
        if (!$this->templateEngine) {
            if ($view === null) {
                $view = Utility::getClassName($this);
            } else {
                if (gettype($view) == "boolean") {
                    $return = $view;
                    $view = Utility::getClassName($this);
                }
            }
            if (!is_file($currentDir . "/" . $view . ".view.php")) {
                $this->debug();
                return;
            }
        }


        $content = "";
        if ($this->fireEvent("OnBeforeRender")) {
            
            if (!isset($_POST["@subReport"])) {
                //If this is subreport request, we dont want to render 
                //KoolReport.widget.js again
                $this->registerEvent(
                    "OnResourceInit",
                    function () {
                        $this->getResourceManager()->addScriptFileOnBegin(
                            $this->getResourceManager()->publishAssetFolder(
                                realpath(dirname(__FILE__) . "/clients/core")
                            ) . "/KoolReport.js"
                        );
                    },
                    true
                ); //Register on top
            }
            $this->getResourceManager()->init();
            $oldActiveReport = (isset($GLOBALS["__ACTIVE_KOOLREPORT__"])) 
                ? $GLOBALS["__ACTIVE_KOOLREPORT__"] : null;
            $GLOBALS["__ACTIVE_KOOLREPORT__"] = $this;
            
            if ($this->templateEngine) {
                $content = $this->templateEngine->render(
                    $view,
                    array(
                        "report"=>$this
                    )
                );
            } else {
                ob_start();
                $report = $this;
                include $currentDir . "/" . $view . ".view.php";
                $content = ob_get_clean();    
            }


            //This will help to solve issue of report inside report
            if ($oldActiveReport === null) {
                unset($GLOBALS["__ACTIVE_KOOLREPORT__"]);
            } else {
                $GLOBALS["__ACTIVE_KOOLREPORT__"] = $oldActiveReport;
            }
            //Adding resource to content
            if ($this->fireEvent("OnBeforeResourceAttached")) {
                $this->getResourceManager()->process($content);
                $this->fireEvent("OnResourceAttached");
            }

            $this->fireEvent("OnRenderEnd", array('content' => &$content));
            if ($return) {
                return $content;
            } else {
                echo $content;
            }
        }
    }

    public function getParams()
    {
        return $this->params;
    }
}