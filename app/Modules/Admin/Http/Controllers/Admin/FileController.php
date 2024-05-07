<?php

namespace App\Modules\Admin\Http\Controllers\Admin;

use App\Modules\Admin\Helper\FileHelper;
use App\Modules\Admin\Helper\TextHelper;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Repositories\Contracts\IFileRepository;
use App\Modules\Admin\Repositories\Contracts\ITextRepository;
use App\Utilities\ServiceResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class FileController extends BaseController
{

    /**
     * @var string
     */
    public $viewFolderName = 'file';

    /**
     * DashboardController constructor.
     * @param IFileRepository $fileRepository
     */
    public function __construct(
        IFileRepository $fileRepository
    )
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'file';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
        $this->baseData['trans_text'] = FileHelper::getLang();
        $this->repository = $fileRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        try {
            if (!$request->get('file_type')) {
                $request->request->add(['file_type' => 'image']);
            }

            // Upload file..
            $this->repository->setFile($request->file('file'))->setRequest($request)
                ->uploadFile();
            // Set file data.
            $file = $this->repository->getFile();

            $this->baseData['file'] = $file;

            return ServiceResponse::jsonNotification($this->baseData['trans_text']['upload_successfully'], 200,  $this->baseData);
        } catch (\Exception $ex) {
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadForEditor(Request $request)
    {
        try {
            $request->request->add(['type' => 'ck_editor']);
            $request->request->add(['file_type' => 'image']);

            // Upload file.
            $this->repository->setFile($request->file('upload'))->setRequest($request)
                ->uploadFile();

            // Set file data.
            $file = $this->repository->getFile();

            // Set original image url.
            $originalUrl = $file->fullUrl;

            return response()->json(['urls' =>
                [
                    'default'   => $originalUrl,
                    '600'       => str_replace('original', '600', $originalUrl),
                    '1200'      => str_replace('original', '1200', $originalUrl)
                ]]);
        } catch (\Exception $ex) {
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }
    }

}
