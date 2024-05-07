<?php

namespace App\Modules\Admin\Repositories\Contracts;

use App\Repositories\IBaseRepository;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

interface IFileRepository extends IBaseRepository
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function setRequest(Request $request);

    /**
     * @param UploadedFile $file
     * @return mixed
     */
    public function setFile(UploadedFile $file);

    /**
     * @param string $disk
     * @return mixed
     */
    public function setFileSystemMode($disk = 'public');

    /**
     * @return mixed
     */
    public function getFile();

    /**
     * @return mixed
     */
    public function uploadFile();

}
