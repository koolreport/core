<?php

/**
 * This file contains process to filter rows based on condition.
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */

/* Usage
->pipe(new Filter(array(
    'or',
    array('age','>',4),
    array('name','contain','Tuan'),
    'and',
    array('time','<=','2010-12-31')),
))
 */

namespace koolreport\processes;

use \koolreport\core\Process;

/**
 * This file contains process to filter rows based on condition.
 *
 * @category  Core
 * @package   KoolReport
 * @author    KoolPHP Inc <support@koolphp.net>
 * @copyright 2017-2028 KoolPHP Inc
 * @license   MIT License https://www.koolreport.com/license#mit-license
 * @link      https://www.koolphp.net
 */
class Filter extends Process
{
    protected $conditions;
    protected $logicalOperator;

    /**
     * Process initiation
     * 
     * @return null
     */
    public function onInit()
    {
        $this->filters = isset($this->params) ? $this->params : array();
    }

    /**
     * Return true if result is filtered
     * 
     * @param array  $condition Condition
     * @param mixed  $value     The value
     * @param string $type      The type of value
     * 
     * @return bool Whether result is filtered
     */
    public function isFiltered($condition, $value, $type)
    {
        $isFiltered = true;
        $operator = $condition[1];
        $filterValue = $condition[2];
        switch ($operator) {
            case '=':
            case '==':
            case 'equal':
                if ($type === 'string' && is_string($value) && is_string($filterValue)) {
                    $isFiltered = strcmp($value, $filterValue) == 0;
                } else {
                    $isFiltered = $value == $filterValue;
                }

                break;
            case '<>':
            case '!=':
            case 'notEqual':
                if ($type === 'string' && is_string($value) && is_string($filterValue)) {
                    $isFiltered = strcmp($value, $filterValue) != 0;
                } else {
                    $isFiltered = $value != $filterValue;
                }

                break;
            case '>':
            case 'gt':
                if ($type === 'string' && is_string($value) && is_string($filterValue)) {
                    $isFiltered = strcmp($value, $filterValue) > 0;
                } else {
                    $isFiltered = $value > $filterValue;
                }

                break;
            case '>=':
                if ($type === 'string' && is_string($value) && is_string($filterValue)) {
                    $isFiltered = strcmp($value, $filterValue) >= 0;
                } else {
                    $isFiltered = $value >= $filterValue;
                }

                break;
            case '<':
            case 'lt':
                if ($type === 'string' && is_string($value) && is_string($filterValue)) {
                    $isFiltered = strcmp($value, $filterValue) < 0;
                } else {
                    $isFiltered = $value < $filterValue;
                }

                break;
            case '<=':
                if ($type === 'string' && is_string($value) && is_string($filterValue)) {
                    $isFiltered = strcmp($value, $filterValue) <= 0;
                } else {
                    $isFiltered = $value <= $filterValue;
                }

                break;
            case 'contain':
            case 'contains':
                $isFiltered = strpos(strtolower($value), strtolower($filterValue)) !== false;
                break;
            case 'notContain':
            case 'notContains':
                $isFiltered = strpos(strtolower($value), strtolower($filterValue)) === false;
                break;
            case 'startWith':
            case 'startsWith':
                $isFiltered = strpos(strtolower($value), strtolower($filterValue)) === 0;
                break;
            case 'notStartWith':
            case 'notStartsWith':
                $isFiltered = strpos(strtolower($value), strtolower($filterValue)) !== 0;
                break;
            case 'endWith':
            case 'endsWith':
                $isFiltered = strpos(strrev(strtolower($value)), strrev(strtolower($filterValue))) === 0;
                break;
            case 'notEndWith':
            case 'notEndsWith':
                $isFiltered = strpos(strrev(strtolower($value)), strrev(strtolower($filterValue))) !== 0;
                break;
            case 'between':
                $filterValue2 = $condition[3];
                $isFiltered = $value > $filterValue && $value < $filterValue2;
                break;
            case 'notBetween':
                $filterValue2 = $condition[3];
                $isFiltered = !($value > $filterValue && $value < $filterValue2);
                break;
            case "in":
                if (!is_array($filterValue)) {
                    $filterValue = array($filterValue);
                }
                $isFiltered = in_array($value, $filterValue);
                break;
            case "notIn":
                if (!is_array($filterValue)) {
                    $filterValue = array($filterValue);
                }
                $isFiltered = !in_array($value, $filterValue);
                break;
            case "like":
                $isFiltered = $this->preg_sql_like($value, $filterValue);
                break;
            case "not like":
                $isFiltered = !$this->preg_sql_like($value, $filterValue);
                break;
            default:
                break;
        }
        return $isFiltered;
    }

    protected function preg_sql_like($input, $pattern, $escape = '\\')
    {
        // escape = \
        // pattern_split_regex = /((?:\\)?(?:\\|%|_))/
        // pattern = _%%ab\%%\_cd_%%
        // parts = Array ( [0] => _ [1] => % [2] => % [3] => ab [4] => \% [5] => % [6] => \_ [7] => cd [8] => _ [9] => % [10] => % )
        // regex = /^..*?ab%.*?_cd..*?$/i

        // Split the pattern into special sequences and the rest
        $pattern_split_regex = '/((?:' . preg_quote($escape, '/') . ')?(?:' . preg_quote($escape, '/') . '|%|_))/';
        $parts = preg_split($pattern_split_regex, $pattern, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        // Loop the split parts and convert/escape as necessary to build regex
        $regex = '/^';
        $lastWasPercent = FALSE;
        foreach ($parts as $part) {
            switch ($part) {
                case $escape . $escape:
                    $regex .= preg_quote($escape, '/');
                    break;
                case $escape . '%':
                    $regex .= '%';
                    break;
                case $escape . '_':
                    $regex .= '_';
                    break;
                case '%':
                    if (!$lastWasPercent) {
                        $regex .= '.*?';
                    }
                    break;
                case '_':
                    $regex .= '.';
                    break;
                default:
                    $regex .= preg_quote($part, '/');
                    break;
            }
            $lastWasPercent = $part === '%';
        }
        $regex .= '$/i';

        // Look for a match and return bool
        return (bool) preg_match($regex, $input);
    }

    /**
     * Handle on data input
     * 
     * @param array $data The input data row 
     * 
     * @return null
     */
    protected function onInput($data)
    {
        $columnsMeta = $this->metaData['columns'];
        $filters = $this->filters;
        $logicalOperator = 'and';
        $isFiltered = true;
        foreach ($filters as $i => $filter) {
            if (is_array($filter)) {
                $field = $filter[0];
                $type = $columnsMeta[$field]['type'];
                if (!isset($data[$field])) {
                    continue;
                }

                $filterResult = $this->isFiltered($filter, $data[$field], $type);
                if ($logicalOperator === 'and') {
                    $isFiltered = $isFiltered && $filterResult;
                }
                if ($logicalOperator === 'or') {
                    $isFiltered = $isFiltered || $filterResult;
                }
            } else if ($filter === 'and' || $filter === 'or') {
                $logicalOperator = $filter;
                if ($filter === 'or' && $i === 0) {
                    $isFiltered = false;
                }
            }
        }
        if ($isFiltered) {
            $this->next($data);
        }
    }
}
