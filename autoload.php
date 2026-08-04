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
            // echo "filePath: $filePath<br>";
            //try to load in file
            if (is_file($filePath)) {
                include_once $filePath; 
            } else {
                //try to load in packages in the same level with core
                // $dirSrc = str_replace("\\", "/", dirname(__FILE__));
                $dir = str_replace("\\", "/", dirname(dirname(__FILE__)));
                $noKoolreportClassName = str_replace("koolreport/", "", $classname);
                $filePath = $dir."/".$noKoolreportClassName.".php";
                // $packageName = strstr($classname, '/', true);
                $packageName = substr($noKoolreportClassName, 0, strpos($noKoolreportClassName, '/'));
                $restClassName = substr($noKoolreportClassName, strpos($noKoolreportClassName, '/') + 1);
                $filePathSrc = $dir."/". $packageName . "/src/" . $restClassName.".php";
            // echo "noKoolreportClassname: $noKoolreportClassname<br>";
            // echo "packageName: $packageName<br>";
            // echo "restClassName: $restClassName<br>";
            // echo "dir: $dir<br>";
            // echo "dirSrc: $dirSrc<br>";
            // echo "filePath: $filePath<br>";
            // echo "filePathSrc: $filePathSrc<br>";
                if (is_file($filePath)) {
                    include_once $filePath;
                } else if (is_file($filePathSrc)) {
                    include_once $filePathSrc;
                } else {
                    //Try to load pakages in packages folder inside core
                    $dir = str_replace("\\", "/", dirname(__FILE__));
                    $filePath = $dir."/".str_replace("koolreport/", "packages/", $classname).".php";
            // echo "filePath: $filePath<br>";
                    if (is_file($filePath)) {
                        include_once $filePath;
                    }   
                }
            }
        }
    }
);

// Per-package back-compat alias bootstrap.
foreach ($packageFolders as $folder) {
    $packageAliasesFile = $folder . "/aliases.php";
    if (is_file($packageAliasesFile)) {
        include_once $packageAliasesFile;
    }
}
