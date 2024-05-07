<?php

namespace App\Modules\Admin\Http\Controllers\Admin;

use App\Modules\Admin\Http\Controllers\BaseController;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class DashboardController extends BaseController
{

    /**
     * @var string
     */
    public $viewFolderName = 'dashboard';

    /**
     * DashboardController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'dashboard';
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view($this->baseModuleName   . $this->baseAdminViewName . $this->viewFolderName.'.index', $this->baseData);
    }

}
