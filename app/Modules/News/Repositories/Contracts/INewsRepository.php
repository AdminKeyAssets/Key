<?php

namespace App\Modules\News\Repositories\Contracts;

use Illuminate\Http\Request;

interface INewsRepository
{
    public function filterData($params);
    public function sortData($data, $sort);
    public function saveData(Request $request);
}
