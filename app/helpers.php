<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

if (! function_exists('module_path')) {
    function module_path($slug = null, $file = '', $location = null)
    {
        $basePath = app_path('Modules');

        if (! is_null($slug)) {
            $basePath .= '/' . Str::studly($slug);
        }

        return $file === '' ? $basePath : $basePath . '/' . ltrim($file, '/');
    }
}


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

function getUrlWithSortParams($sortBy, $currentSortBy, $currentSortOrder)
{
    $params = request()->except(['sort_by', 'sort_order']);

    $params['sort_by'] = $sortBy;
    $params['sort_order'] = ($currentSortBy == $sortBy && $currentSortOrder == 'asc') ? 'desc' : 'asc';

    return request()->url() . '?' . http_build_query($params);
}

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
