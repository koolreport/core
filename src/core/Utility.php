<?php

/**
 * This file contains the most common used functions for KoolReport.
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
 * Utility class contain utility static function supporting report
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class Utility
{
    /**
     * The unique id that use to generate unique name of widget
     *
     * @var integer $_uniqueId The unique id that use to generate
     *                         unique name of widget
     */
    public static $_uniqueId;

    /**
     * Return unique id each time is called
     *
     * @return string Unqiue id generated
     */
    public static function getUniqueId()
    {
        Utility::$_uniqueId++;
        return uniqid() . Utility::$_uniqueId;
    }

    /**
     * Try to get type of a value
     *
     * @param mixed $value A value needed to guess type
     *
     * @return string Possible type of a value
     */
    public static function guessType($value)
    {
        $map = array(
            "float" => "number",
            "double" => "number",
            "int" => "number",
            "integer" => "number",
            "bool" => "number",
            "numeric" => "number",
            "string" => "string",
            "array" => "array",
        );

        $type = strtolower(gettype($value));
        foreach ($map as $key => $value) {
            if (strpos($type, $key) !== false) {
                return $value;
            }
        }
        return "unknown";
    }

    /**
     * Recursive copy content of folder to destination
     *
     * @param string $src Path to source
     * @param string $dst The destination you want to copy to
     *
     * @return null
     */
    public static function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    Utility::recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Format the value with provided format settings
     *
     * @param mixed $value  Value we want to format
     * @param array $format The format settings of the value
     *
     * @return string Formatted value in string
     */
    public static function format($value, $format)
    {
        $f = Utility::get($format, "format", true);
        if ($f === false) {
            return $value;
        }

        $type = Utility::get($format, "type", "unknown");
        switch ($type) {
            case "number":
                $decimals = Utility::get($format, "decimals", 0);
                $dec_point = Utility::get(
                    $format,
                    "decPoint",
                    Utility::get(
                        $format,
                        "decimalPoint",
                        Utility::get(
                            $format,
                            "dec_point",
                            "."
                        )
                    )
                );

                $thousand_sep = Utility::get(
                    $format,
                    "thousandSep",
                    Utility::get(
                        $format,
                        "thousandSeparator",
                        Utility::get(
                            $format,
                            "thousand_sep",
                            ","
                        )
                    )
                );
                $prefix = Utility::get($format, "prefix", "");
                $suffix = Utility::get($format, "suffix", "");
                return $prefix
                    . number_format((float)$value, $decimals, $dec_point, $thousand_sep)
                    . $suffix;
                break;
            case "string":
                $prefix = Utility::get($format, "prefix", "");
                $suffix = Utility::get($format, "suffix", "");
                return $prefix . $value . $suffix;
                break;
            case "datetime":
                $dateFormat = Utility::get($format, "format", "Y-m-d H:i:s");
                // no break
            case "date":
                $dateFormat = isset($dateFormat)
                    ? $dateFormat
                    : Utility::get($format, "format", "Y-m-d");
                // no break
            case "time":
                $dateFormat = isset($dateFormat)
                    ? $dateFormat
                    : Utility::get($format, "format", "H:i:s");
                $displayFormat = Utility::get($format, "displayFormat");
                if ($displayFormat && $value) {
                    if ($fvalue = \DateTime::createFromFormat($dateFormat, $value)) {
                        return $fvalue->format($displayFormat);
                    }
                }
                break;
            case "array":
                return json_encode($value);
                break;
        }

        if (gettype($value) === "array") {
            return json_encode($value);
        }

        return $value;
    }

    /**
     * Get the short name of a class
     *
     * The method will return only the name of the class and ignore its namespace
     *
     * @param object $obj The object you want to get classname
     *
     * @return string The shortname of class
     */
    public static function getClassName($obj)
    {
        $reflection = new \ReflectionClass($obj);
        return $reflection->getShortName();
    }

    /**
     * Traverse through the structure of object and find the js function
     *
     * @param object $obj   The object
     * @param array  $marks The mark
     *
     * @return array The new marks
     */
    public static function markJsFunction(&$obj, &$marks = array())
    {
        foreach ($obj as $k => &$v) {
            switch (gettype($v)) {
                case "object":
                case "array":
                    Utility::markJsFunction($v, $marks);
                    break;
                case "string":
                    $tsv = trim(strtolower($v));
                    $isJsFunction = false;
                    if (
                        strpos($tsv, "function") === 0
                        && (strrpos($tsv, "}") === strlen($tsv) - 1
                            || strrpos($tsv, "()") === strlen($tsv) - 2)
                    ) $isJsFunction = true;
                    if (
                        strpos($tsv, "(") === 0
                        && strpos($tsv, "=>") !== false
                    ) {
                        $isJsFunction = true;
                    }
                    if ($isJsFunction) {
                        $marks[] = trim($v);
                        $obj[$k] = "--js(" . (count($marks) - 1) . ")";
                    }
                    break;
            }
        }
        return $marks;
    }

    /**
     * Get the json of an object
     *
     * @param object $object The object needs to be encoded
     * @param int    $option The json_encode() additional option
     *
     * @return string The json string of objects
     */
    public static function jsonEncode($object, $option = 0)
    {
        $marks = Utility::markJsFunction($object);
        $text = json_encode($object, $option);
        foreach ($marks as $i => $js) {
            $text = str_replace("\"--js($i)\"", (string)$js, (string)$text);
        }
        return $text;
    }

    public static function jsObjToArray($jsonStr)
    {
        $controlChars = ["{", "}", "[", "]", ":", ","];
        $blankChars = [" ", "\t", "\r", "\n"];

        function subStringMatchesJsKeyWords($str, $pos)
        {
            $jsKeyWords = ["undefined", "null", "true", "false"];
            for ($i = 0; $i < count($jsKeyWords); $i++) {
                $word = $jsKeyWords[$i];
                if (substr($str, $pos, strlen($word)) === $word) return [true, $word];
            }
            return [false, null];
        }
        function subStringMachesNumber($str, $pos)
        {
            $char = $str[$pos];
            if ("0" <= $char && $char <= "9") return true;
            if ($str[$pos] === "-") {
                $nextChar = $str[$pos + 1] ?? null;
                if ("0" <= $nextChar && $nextChar <= "9") return true;
            }
            return false;
        }
        function subStringMachesFunction($str, $pos)
        {
            $char = $str[$pos];
            if ($char === "(" || substr($str, $pos, 8) === "function") return true;
            return false;
        }
        function subStringMachesComment($str, $pos)
        {
            $char = $str[$pos];
            if ($char === "/" && substr($str, $pos, 2) === "//") return [true, "//"];
            if ($char === "/" && substr($str, $pos, 2) === "/*") return [true, "/*"];
            return [false, null];
        }
        function skipComment($jsonStr, $i)
        {
            list($commentMatch, $commentSequence) = subStringMachesComment($jsonStr, $i);
            if ($commentMatch) {
                // echo "commentSequence = $commentSequence<br>";
                if ($commentSequence === "//") {
                    for ($j = $i; $j < strlen($jsonStr); $j++) {
                        $curChar = $jsonStr[$j];
                        if ($curChar === "\n" || $curChar === "\r") {
                            $i = $j + 1;
                            break;
                        }
                    }
                } else if ($commentSequence === "/*") {
                    for ($j = $i; $j < strlen($jsonStr); $j++) {
                        $curChar = $jsonStr[$j];
                        $curNextChar = $jsonStr[$j + 1] ?? null;
                        // echo "curChar = $curChar || curNextChar = $curNextChar<br>";
                        if ($curChar === "*" && $curNextChar === "/") {
                            // echo "end of block comment<br>";
                            $i = $j + 2;
                            break;
                        }
                    }
                }
            }
            return [$jsonStr[$i], $i];
        }
        $rightNextControlChar = function ($str, $pos) use ($controlChars, $blankChars) {
            for ($i = $pos + 1; $i < strLen($str); $i++) {
                list($char, $i) = skipComment($str, $i);
                if (in_array($char, $controlChars)) return $str[$i];
                if (!in_array($char, $blankChars)) return null;
            }
            return null;
        };
        $updateLastOpenBrackets = function ($char) use (&$lastOpenBrackets) {
            if ($char === "[") {
                $lastOpenBrackets[] = "[";
            } else if ($char === "{") {
                $lastOpenBrackets[] = "{";
            } else if ($char === "]") {
                if (end($lastOpenBrackets) !== "[") {
                    echo "Issue: lastOpenBracket = " . end($lastOpenBrackets) . "<br><br>";
                    throw new \Exception('Wrong syntax in JS object string: wrong matching []');
                }
                array_pop($lastOpenBrackets);
            } else if ($char === "}") {
                if (end($lastOpenBrackets) !== "{") {
                    echo "Issue: lastOpenBracket = " . end($lastOpenBrackets) . "<br><br>";
                    throw new \Exception('Wrong syntax in JS object string: wrong matching {}');
                }
                array_pop($lastOpenBrackets);
            }
        };

        $lastControlChar = $nextControlChar = $lastChar = null;
        $inValue = $inKey = false;
        $keywordValue = $numericValue = $functionValue = $expressionValue = false;
        $currentQuote = null;
        $lastOpenBrackets = [];
        $funcValueCurlyBrackets = $expressionBrackets = [];
        $funcValueHadCurly = false;

        $newStr = "";

        for ($i = 0; $i < strlen($jsonStr); $i++) {
            $char = $jsonStr[$i];
            list($char, $i) = skipComment($jsonStr, $i);
            // echo "$char";
            // echo "char = $char * ";
            // echo "inKey = $inKey ** inValue = $inValue<br>";
            if ($inKey || $inValue) {
                if ($functionValue) {
                    if ($char === "{") {
                        $funcValueCurlyBrackets[] = "{";
                        $funcValueHadCurly = true;
                        $newStr .= $char;
                    } else if (!$funcValueHadCurly) {
                        $newStr .= $char;
                    } else if ($char === "}") {
                        array_pop($funcValueCurlyBrackets);
                        if (empty($funcValueCurlyBrackets)) {
                            $newStr .= $char . '"';
                            $inValue = $functionValue = $funcValueHadCurly = false;
                        }
                    } else if ($char === "\n" || $char === "\r") {
                        $newStr .= " ";
                    } else if ($char === '"' && $lastChar !== "\\") {
                        $newStr .= "\\" . $char;
                    } else {
                        $newStr .= $char;
                    }
                } else if ($expressionValue) {
                    if (empty($expressionBrackets) && ($char === "}" || $char === "]" || $char === ",")) {
                        $newStr .= '; }" ' . $char;
                        $inKey = $inValue = $expressionValue = false;
                        // echo "<br>end of expression value <br>";
                    } else if ($char === "{" || $char === "[") {
                        $expressionBrackets[] = "[";
                        $newStr .= $char;
                    } else if ($char === "}" || $char === "}") {
                        array_pop($expressionBrackets);
                        $newStr .= $char;
                    } else if ($char === "\n" || $char === "\r") {
                        $newStr .= " ";
                    } else if ($char === '"' && $lastChar !== "\\") {
                        $newStr .= "\\" . $char;
                    } else {
                        $newStr .= $char;
                    }

                    $updateLastOpenBrackets($char);
                    if ($char === ",") {
                        $nextControlChar = $rightNextControlChar($jsonStr, $i);
                        if ($nextControlChar === "}" || $nextControlChar === "]") {
                            // echo "remove last comma before: newStr = $newStr<br>";
                            $newStr = substr($newStr, 0, -1);
                            // echo "remove last comma after: newStr = $newStr<br>";
                        }
                    }
                } else if (in_array($char, $blankChars)) {
                    if ($currentQuote === null) {
                        if ($keywordValue) {
                            if ($keywordValue === "undefined") {
                                $newStr = substr($newStr, 0, -strlen("undefined")) . "null";
                            }
                            $keywordValue = false;
                            $newStr .= $char;
                        } else if ($numericValue) {
                            $numericValue = false;
                            $newStr .= $char;
                        } else {
                            $newStr .= '"' . $char;
                        }
                        $inKey = $inValue = false;
                    } else {
                        $newStr .= $char;
                    }
                } else {

                    if ($currentQuote === "'" && $char === '"') {
                        if ($lastChar !== "\\") {
                            // echo "add escape slash<br>";
                            $newStr .= "\\" . $char;
                        } else {
                            $newStr .= $char;
                        }
                    } else if ($currentQuote !== null) {
                        if ($char === $currentQuote) {
                            if ($lastChar !== "\\") {
                                $newStr .= '"';
                                $currentQuote = null;
                                $inKey = $inValue = false;
                            } else if ($lastChar === "\\") {
                                if ($currentQuote === "'") {
                                    $newStr = substr($newStr, 0, -1) . $char;
                                } else {
                                    $newStr .= $char;
                                }
                            }
                        } else {
                            $newStr .= $char;
                            // echo "newStr = $newStr<br>";
                        }
                    } else if ($currentQuote === null) {
                        if ($char === ":" || $char === "}" || $char === "]" || $char === ",") {
                            if ($keywordValue) {
                                if ($keywordValue === "undefined") {
                                    $newStr = substr($newStr, 0, -strlen("undefined")) . "null";
                                }
                                $keywordValue = false;
                                $newStr .= $char;
                                // echo "newStr = $newStr<br>";
                            } else if ($numericValue) {
                                $newStr .= $char;
                            } else {
                                $newStr .= '"' . $char;
                            }
                            $inKey = $inValue = false;
                        } else {
                            $newStr .= $char;
                            // echo "2 newStr = $newStr<br>";
                        }

                        if ($char === ",") {
                            $nextControlChar = $rightNextControlChar($jsonStr, $i);
                            if ($nextControlChar === "}" || $nextControlChar === "]") {
                                // echo "remove last comma before: newStr = $newStr<br>";
                                $newStr = substr($newStr, 0, -1);
                                // echo "remove last comma after: newStr = $newStr<br>";
                            }
                        }
                    }
                    $updateLastOpenBrackets($char);
                }
            } else {
                $keywordValue = $numericValue = $functionValue = $funcValueHadCurly = false;
                if (in_array($char, $controlChars)) {
                    if ($char === ",") {
                        $nextControlChar = $rightNextControlChar($jsonStr, $i);
                        if ($nextControlChar !== "}" && $nextControlChar !== "]") {
                            $newStr .= $char;
                        }
                    } else {
                        $newStr .= $char;
                    }
                    $updateLastOpenBrackets($char);
                } else if (in_array($char, $blankChars)) {
                    $newStr .= $char;
                } else { // normal char
                    if ($lastControlChar === "{") {
                        // echo "1<br>";
                        if (in_array($lastChar, $blankChars) || $lastChar === "{") {
                            // echo "2<br>";
                            $inKey = true;
                            if ($char === "'" || $char === '"') {
                                $currentQuote = $char;
                                $newStr .= '"';
                            } else {
                                // echo "3<br>";
                                $currentQuote = null;
                                $newStr .= '"' . $char;
                            }
                        } else {
                            $newStr .= $char;
                        }
                    }
                    if ($lastControlChar === "[" || $lastControlChar === ":" || $lastControlChar === ",") {
                        // echo "1<br>";
                        if (in_array($lastChar, $blankChars) || $lastChar === $lastControlChar) {
                            // echo "2<br>";
                            if ($lastControlChar === "[" || $lastControlChar === ":") $inValue = true;
                            else if (end($lastOpenBrackets) === "[") $inValue = true;
                            else $inKey = true;

                            if ($char === "'" || $char === '"') {
                                $currentQuote = $char;
                                $newStr .= '"';
                            } else {
                                $currentQuote = null;
                                list($keywordMatch, $word) = subStringMatchesJsKeyWords($jsonStr, $i);
                                $numericMatch = subStringMachesNumber($jsonStr, $i);
                                $functionMatch = subStringMachesFunction($jsonStr, $i);
                                if ($keywordMatch && $inValue) {
                                    $keywordValue = $word;
                                    $newStr .= $char;
                                } else if ($numericMatch && $inValue) {
                                    $numericValue = true;
                                    $newStr .= $char;
                                } else if ($functionMatch && $inValue) {
                                    $functionValue = true;
                                    $newStr .= '"' . $char;
                                } else if ($inValue) {
                                    $keywordValue = $numericValue = $functionValue = false;
                                    $expressionValue = true;
                                    $newStr .= '"function() { return ' . $char;
                                } else {
                                    $keywordValue = $numericValue = $functionValue = $expressionValue = false;
                                    $newStr .= '"' . $char;
                                }
                            }
                        } else {
                            $newStr .= $char;
                        }
                    }
                }
            }
            $lastChar = $char;
            if (in_array($char, $controlChars)) $lastControlChar = $char;
        }

        // echo str_replace(" ", "&nbsp;", str_replace("\n", "<br>", $newStr));
        // var_dump($jsonStr); echo "<br>";
        // var_dump($newStr); echo "<br>";

        $arr = json_decode($newStr, true);
        // echo '<br>';
        // echo 'json_decode last error number: ', json_last_error(), '<br>';
        // echo 'json_decode last error message: ', json_last_error_msg(), '<br>';
        if (json_last_error() !== 0) {
            throw new \Exception('Wrong syntax in JS object string: ' . json_last_error_msg());
        }

        return $arr;
    }

    /**
     * Get wether an array is an associate array
     *
     * @param array $arr The array that you want to test
     *
     * @return bool Whether the array is an associate array
     */
    public static function isAssoc($arr)
    {
        if (gettype($arr) != "array") {
            return false;
        }
        if ($arr === null || $arr === array()) {
            return false;
        }
        if (array_keys($arr) === range(0, count($arr) - 1)) {
            return false;
        }
        return true;
    }

    /**
     * Get value from array with keys, return default if not found
     *
     * The function support the list of keys in order as well
     *
     * @param array $arr     The array that you want to test
     * @param mixed $keys    Could be name of key or an array containing list of key path
     * @param mixed $default Default value if no value for key is found
     *
     * @return mixed Value at key path
     */
    public static function get($arr, $keys, $default = null)
    {
        // if (! is_array($arr)) {
        //     return $default;
        // }
        // return isset($arr[$keys]) ? $arr[$keys] : $default;
        if (is_string($keys) || is_int($keys)) {
            return isset($arr[$keys]) ? $arr[$keys] : $default;
        }
        if (is_array($keys) and count($keys) > 0) {
            foreach ($keys as $key) {
                $arr = self::get($arr, $key, $default);
            }
            return $arr;
        }
        return $default;
    }
    /**
     * Init an key value inside an array
     *
     * @param array  $arr     The array
     * @param string $keys    The key
     * @param mixed  $default The default value to fill if key is not found
     *
     * @return array The array
     */
    public static function init(&$arr, $keys, $default = null)
    {
        if (is_array($keys)) {
            if (count($keys) === 0) {
                return $default;
            }
            $fKey = $keys[0];
            if (count($keys) === 1) {
                return self::init($arr, $fKey, $default);
            }
            if (!isset($arr[$fKey]) || !is_array($arr[$fKey])) {
                $arr[$fKey] = [];
            }
            $restKeys = array_slice($keys, 1);
            return self::init($arr[$fKey], $restKeys, $default);
        } else {
            if (!isset($arr[$keys])) {
                $arr[$keys] = $default;
            }
            return $arr[$keys];
        }
    }

    /**
     * Set an key value inside an array
     *
     * @param array  $arr     The array
     * @param string $keys    The key
     * @param mixed  $value   The  value to fill
     *
     * @return array The array
     */
    public static function set(&$arr, $keys, $value = null)
    {
        if (is_array($keys)) {
            if (count($keys) === 0) {
                return $value;
            }
            $fKey = $keys[0];
            if (count($keys) === 1) {
                return self::set($arr, $fKey, $value);
            }
            if (!isset($arr[$fKey]) || !is_array($arr[$fKey])) {
                $arr[$fKey] = [];
            }
            $restKeys = array_slice($keys, 1);
            return self::set($arr[$fKey], $restKeys, $value);
        } else {
            $arr[$keys] = $value;
            return $arr[$keys];
        }
    }

    /**
     * Get array if the value inside an array is a string
     *
     * @param array  $arr     The array
     * @param string $key     The key
     * @param mixed  $default The default value
     *
     * @return array Return array result
     */
    public static function getArray($arr, $key, $default = array())
    {
        $value = Utility::get($arr, $key);
        return ($value != null) ? explode(',', $value) : $default;
    }

    /**
     * Get only some of the keys from an array
     *
     * @param array  $arr  The array
     * @param string $keys List of keys in string and separate with comma(,)
     *
     * @return array The filtered array with only specified keys
     */
    public static function filterIn($arr, $keys)
    {
        $keys = explode(",", $keys);
        $result = array();
        foreach ($arr as $key => $value) {
            if (in_array($key, $keys)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    /**
     * Remove some specific keys from an array
     *
     * @param array  $arr  The array
     * @param string $keys The keys in string format seperated by comma
     *
     * @return array New array excluding selected keys
     */
    public static function filterOut($arr, $keys)
    {
        $keys = explode(",", $keys);
        $result = array();
        foreach ($arr as $key => $value) {
            if (!in_array($key, $keys)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * A mass string replace with parameters
     *
     * @param string $str    The template string
     * @param array  $params An associate array containing key value to replace
     *
     * @return string The string that is replaced key with value
     */
    public static function strReplace($str, $params)
    {
        foreach ($params as $k => $v) {
            $str = str_replace($k, $v, (string)$str);
        }
        return $str;
    }

    /**
     * Return the full path to class of an object
     *
     * @param object $obj The object
     *
     * @return string The full path to the class of an object
     */
    public static function getClassPath($obj)
    {
        $class_info = new \ReflectionClass($obj);
        return $class_info->getFileName();
    }

    /**
     * Print nicely value of an array. This method is useful for debugging
     *
     * @param array $arr The array
     *
     * @return null
     */
    public static function prettyPrint($arr)
    {
        echo '<pre>';
        echo json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
        echo '</pre>';
    }

    public static function prettyPrint2($arr)
    {
        $arr2 = [];
        foreach ($arr as $i => $row) {
            $arr2[$i] = str_replace('"', "'", json_encode($row, JSON_UNESCAPED_SLASHES));
        }
        self::prettyPrint($arr2);
    }

    /**
     * Return string with replaced first occurerence only
     *
     * @param string $from    The needle
     * @param string $to      The replacement
     * @param string $content The haystack
     *
     * @return string String with replaced first occurerence only
     */
    public static function strReplaceFirst($from, $to, $content)
    {
        $from = '/' . preg_quote($from, '/') . '/';
        return preg_replace($from, $to, $content, 1);
    }

    /**
     * Get the doument root
     *
     * @return string the document root path
     */
    public static function getDocumentRoot()
    {
        //The old method is to use the document_root from $_SERVER
        //Howerver in some hosting the document root is not the same
        //with the root folder of the website, so we add backup with
        //second way  to calculate the document root with script_name
        //and script_filename

        // if (isset($_SERVER["DOCUMENT_ROOT"])) return $_SERVER["DOCUMENT_ROOT"];

        //In case of using virtual host, method of using difference between script_name and script_filename
        //might not work
        // if (isset($_SERVER["DOCUMENT_ROOT"])) return $_SERVER["DOCUMENT_ROOT"];

        $script_filename = str_replace(
            "\\",
            "/",
            realpath($_SERVER["SCRIPT_FILENAME"])
        );
        $script_name = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"]);
        $endPoint = strpos(strtolower($script_filename), strtolower($script_name));
        $documentRoot = substr($script_filename, 0, $endPoint);
        return $documentRoot;
    }

    /**
     * Convert path to use forward slash format
     *
     * @param string $path The path you want to covnvert
     *
     * @return string The converted path which use forward slash as standard
     */
    public static function standardizePathSeparator($path)
    {
        //We use "/" for all system
        return str_replace("\\", "/", $path);
    }

    /**
     * Get the dirname
     *
     * @param string $path The path of file or folder
     *
     * @return string The parent folder
     */
    public static function getDir($path)
    {
        return substr($path, 0, strrpos($path, '/'));
    }

    /**
     * Merge array recursively
     *
     * @param array $array1 Array 1
     * @param array $array2 Array 2
     *
     * @return array The merged array
     */
    public static function arrayMergeRecursive($array1, $array2)
    {
        $merged = $array1;

        if ($array2 != null) {
            foreach ($array2 as $key => &$value) {
                if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                    $merged[$key] = self::arrayMergeRecursive($merged[$key], $value);
                } elseif (is_numeric($key)) {
                    if (!in_array($value, $merged)) {
                        $merged[] = $value;
                    }
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    public static function arrayRemoveEmpty(&$haystack)
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                self::arrayRemoveEmpty($haystack[$key]);
            }

            if (is_array($haystack[$key]) && empty($haystack[$key]) || is_null($haystack[$key])) {
                unset($haystack[$key]);
            }
        }
        return $haystack;
    }

    /**
     * Map value
     *
     * @param mixed $funcOrArray  A function or array
     * @param array $args         Arguments
     * @param mixed $defaultValue Optional default value
     *
     * @return array New mapped value
     */
    public static function map($funcOrArray, $args, $defaultValue = null)
    {
        // if ($defaultValue === "{{identical}}") $defaultValue = $args;
        if (is_array($funcOrArray)) {
            return self::get($funcOrArray, $args, $defaultValue);
        } elseif (is_callable($funcOrArray)) {
            if (!is_array($args)) {
                $args = [$args];
            }
            return call_user_func_array($funcOrArray, $args);
        }
        return $defaultValue;
    }

    /**
     * Format value
     *
     * @param mixed $value The value
     * @param array $meta  Column meta
     * @param mixed $row   The optional data row
     *
     * @return array Formatted value
     */
    public static function formatValue($value, $meta, $row = null)
    {
        $formatValue = self::get($meta, "formatValue", null);

        if (is_string($formatValue)) {
            eval('$fv="' . str_replace('@value', '$value', $formatValue) . '";');
            return $fv;
        } elseif (is_callable($formatValue)) {
            return $formatValue($value, $row);
        } else {
            return self::format($value, $meta);
        }
    }

    public static function transpose($array)
    {
        return array_map(null, ...$array);
    }

    public static function array_is_list(array $arr)
    {
        // if (count($arr) === 0) return true;
        // $keys = array_keys($arr[0]);
        // foreach ($keys as $i => $key) {
        //     if ($i !== $key) return false;
        // }
        // return true;
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    public static function array_is_2D_list($arr2D)
    {
        if ($arr2D === []) {
            return true;
        }
        foreach ($arr2D as $arr) {
            if (!self::array_is_list($arr)) return false;
        }
        return true;
    }

    public static function tableToAssociate($data)
    {        
        $newData = [];
        $columns = $data[0];
        foreach ($data as $i => $row) {
            if ($i === 0) continue;
            $newRow = [];
            foreach ($row as $j => $value) {
                $newRow[$columns[$j]] = $value;
            }
            $newData[] = $newRow;
        }
        return $newData;
    }

    public static function associateToTable($data)
    {        
        $newData = [
            []
        ];
        foreach ($data as $row) {
            $rowKeys = array_keys($row);
            foreach ($rowKeys as $rowKey) {
                if (!isset($newData[0][$rowKey])) $newData[0][$rowKey] = true;
            }
        }
        $newData[0] = array_keys($newData[0]);
        foreach ($data as $row) {
            $newRow = [];
            foreach ($newData[0] as $col) {
                $newRow[$col] = array_key_exists($col, $row) ? $row[$col] : null;
            }
            $newData[] = array_values($newRow);
        }
        return $newData;
    }
}
