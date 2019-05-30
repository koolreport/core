<?php
/**
 * This file will autoload KoolReport class when included
 * 
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

$packageFolders = glob(dirname(__FILE__)."/../*", GLOB_ONLYDIR);
foreach ($packageFolders as $folder) {
    $packageVendorAutoLoadFile = $folder."/vendor/autoload.php";
    if (is_file($packageVendorAutoLoadFile)) {
        include_once $packageVendorAutoLoadFile;
    }
}

spl_autoload_register(
    function ($classname) {
        if (strpos($classname, "koolreport\\")!==false) {
            $dir = str_replace("\\", "/", dirname(__FILE__));
            $classname = str_replace("\\", "/", $classname);
            $filePath = $dir."/".str_replace("koolreport/", "src/", $classname).".php";
            //try to load in file
            if (is_file($filePath)) {
                include_once $filePath; 
            } else {
                //try to load in packages in the same level with core
                $dir = str_replace("\\", "/", dirname(dirname(__FILE__)));
                $filePath = $dir."/".str_replace("koolreport/", "", $classname).".php";
                if (is_file($filePath)) {
                    include_once $filePath;
                } else {
                    //Try to load pakages in packages folder inside core
                    $dir = str_replace("\\", "/", dirname(__FILE__));
                    $filePath = $dir."/".str_replace("koolreport/", "packages/", $classname).".php";
                    if (is_file($filePath)) {
                        include_once $filePath;
                    }    
                }
            }
        }
    }
);
