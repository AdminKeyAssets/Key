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

    return config('permission_list.' .$module. '.'.$type.'.key');
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
