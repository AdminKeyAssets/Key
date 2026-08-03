<?php

use Carbon\Carbon;


/**
 * @param $module
 * @param $type
 * @param bool $default
 * @return bool|\Illuminate\Config\Repository|mixed|null
 */
function getPermissionKey($module, $type ,$default = true){

    if ($default) {
        return str_replace('{module_name}', $module, config('permission_list.' .$module. '.default.'.$type.'.key'));
    }

    return str_replace('{module_name}', $module, config('permission_list.' .$module. '.custom.'.$type.'.key'));
}

/**
 * Build a sortable column URL that preserves the current query string.
 *
 * Previously declared inline in two Blade templates (asset index and developer
 * asset index). A function declared in a Blade view is redeclared every time the
 * compiled view is included, which fatals with "Cannot redeclare" as soon as more
 * than one of those views is rendered in the same PHP process.
 *
 * @param  string  $sortBy            column the link sorts by
 * @param  string  $currentSortBy     column currently being sorted
 * @param  string  $currentSortOrder  current direction, 'asc' or 'desc'
 * @return string
 */
function getUrlWithSortParams($sortBy, $currentSortBy, $currentSortOrder)
{
    $params = request()->except(['sort_by', 'sort_order']);

    $params['sort_by'] = $sortBy;
    $params['sort_order'] = ($currentSortBy == $sortBy && $currentSortOrder == 'asc') ? 'desc' : 'asc';

    return request()->url() . '?' . http_build_query($params);
}

/**
 * Determine whether a column is empty for every row, so the table can hide it.
 *
 * Previously declared inline in the same two Blade templates as
 * getUrlWithSortParams(), with the same redeclaration fatal.
 *
 * @param  \Illuminate\Support\Collection  $collection
 * @param  callable  $checkFunction  extracts the column value from a row
 * @return bool
 */
function hasEmptyColumn($collection, callable $checkFunction)
{
    return $collection->every(function ($item) use ($checkFunction) {
        return empty($checkFunction($item));
    });
}

/**
 * @param $model
 *
 * @return string
 */
function getModelName($model){
    $className = get_class($model);
    return strtolower(class_basename($className));
}

/**
 * @param $text
 * @param string $delimiter
 * @param int $segment
 *
 * @return mixed|string
 */
function getSomeSegmentText($text, $delimiter = '.', $segment = 0){
    return explode($delimiter,$text)[$segment];
}

function calculateRentalCost($price, $fromDate, $toDate)
{
    $from = Carbon::parse($fromDate);
    $to = Carbon::parse($toDate);

    $totalMonths = ($to->year - $from->year) * 12 + ($to->month - $from->month);

    $dayDifference = $to->day - $from->day;

    $daysInFromMonth = $from->daysInMonth;

    if ($dayDifference < 0) {
        $totalMonths -= 1;
        $fractionOfMonth = ($dayDifference + $daysInFromMonth) / $daysInFromMonth;
    } else {
        $fractionOfMonth = $dayDifference / $to->daysInMonth;
    }

    $exactMonthDifference = $totalMonths + $fractionOfMonth;

    return $price * $exactMonthDifference;
}
