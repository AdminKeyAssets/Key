<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Repositories\IBaseRepository;
use App\Traits\ExportTrait;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{

    use ExportTrait;

    /**
     * @var string
     */
    protected $successCreateText = 'Added Successfully';

    /**
     * @var string
     */
    protected $successUpdateText = 'Updated Successfully';

    /**
     * @var string
     */
    protected $successDeleteText = 'Deleted Successfully';

    /**
     * @var int
     */
    protected $perPage = 20;

    /**
     * @var $repository IBaseRepository
     */
    public $repository;

    /**
     * @var array
     */
    protected $baseData = [];

    /**
     * @var string
     */
    protected $baseModuleName = 'admin::';

    /**
     * @var string
     */
    protected $baseAdminViewName = 'admin.';

    /**
     * @var string
     */
    protected $baseInvestorViewName = 'investor.';

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        $this->baseData['langFolderName'] = 'admin';
        $this->baseData['baseRouteName'] = 'admin';
        $this->baseData['locales'] = config('language_manager.locales');
        $this->baseData['default_locale'] = config('language_manager.default_locale');
        $this->baseData['editor_config'] = config('editor.config');
        $this->baseData['editor_config']['upload_editor'] = route('admin.file.upload_editor');
        $this->baseData['file_upload_url'] = route('admin.file.upload');
        $this->baseData['per_page'] = 25;
    }

}
