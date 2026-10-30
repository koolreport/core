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

/**
 * Packages whose dependencies cannot run on this PHP, as package name => required
 * PHP_VERSION_ID. Populated below and read by the autoloader to explain, rather than
 * merely fail, when one of their classes is requested.
 */
$GLOBALS["__koolreport_unavailable_packages"] = array();

foreach ($packageFolders as $folder) {
    $packageVendorAutoLoadFile = $folder."/vendor/autoload.php";
    if (!is_file($packageVendorAutoLoadFile)) {
        continue;
    }

    // Composer generates vendor/composer/platform_check.php and runs it EAGERLY from
    // vendor/autoload.php. When the running PHP is below the floor its dependencies
    // require, it calls trigger_error(E_USER_ERROR) -- a fatal that cannot be caught,
    // and that happens while merely loading the autoloader. Because this loop includes
    // EVERY sibling package, one such package took down every page in the application,
    // not just its own. So read the floor first and skip the package instead.
    //
    // Deliberately fails toward the old behaviour: if the floor cannot be determined,
    // the package is included exactly as before. This can only turn a fatal into a
    // skip, never turn a working package into a missing one.
    $platformCheckFile = $folder."/vendor/composer/platform_check.php";
    if (is_file($platformCheckFile)) {
        $platformCheckSource = file_get_contents($platformCheckFile);
        $matches = array();
        if ($platformCheckSource !== false
            && preg_match('/PHP_VERSION_ID\s*>=\s*(\d+)/', $platformCheckSource, $matches)
        ) {
            $requiredVersionId = (int)$matches[1];
            if ($requiredVersionId > 0 && PHP_VERSION_ID < $requiredVersionId) {
                $GLOBALS["__koolreport_unavailable_packages"][basename($folder)]
                    = $requiredVersionId;
                continue;
            }
        }
    }

    include_once $packageVendorAutoLoadFile;
}

/**
 * Turn a PHP_VERSION_ID such as 80200 into a readable "8.2.0".
 *
 * @param int $versionId Composer's PHP_VERSION_ID form
 *
 * @return string
 */
if (!function_exists("koolreport_format_version_id")) {
    function koolreport_format_version_id($versionId)
    {
        return (int)($versionId / 10000)
            . "." . (int)(($versionId % 10000) / 100)
            . "." . ($versionId % 100);
    }
}

spl_autoload_register(
    function ($classname) {
        if (strpos($classname, "koolreport\\")!==false) {
            // If this class belongs to a package skipped above, say why once. The
            // request still fails with "Class not found", but the reason is no longer
            // invisible. A warning rather than an exception, so class_exists() keeps
            // returning false instead of throwing.
            $unavailable = $GLOBALS["__koolreport_unavailable_packages"];
            if (!empty($unavailable)) {
                $parts = explode("\\", $classname);
                if (isset($parts[1]) && isset($unavailable[$parts[1]])) {
                    if ($unavailable[$parts[1]] > 0) {
                        trigger_error(
                            "KoolReport package '" . $parts[1] . "' was not loaded: the "
                            . "libraries it bundles require PHP >= "
                            . koolreport_format_version_id($unavailable[$parts[1]])
                            . ", this server runs " . PHP_VERSION . ". The .zip download "
                            . "is built on PHP 8.2, so its bundled dependencies cannot run "
                            . "on older PHP. Install KoolReport with Composer instead: "
                            . "Composer resolves every dependency to a version your PHP "
                            . "supports, so this package will work on " . PHP_VERSION . ".",
                            E_USER_WARNING
                        );
                        // report once per package, not once per class lookup
                        $GLOBALS["__koolreport_unavailable_packages"][$parts[1]] = 0;
                    }
                    return;
                }
            }
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
