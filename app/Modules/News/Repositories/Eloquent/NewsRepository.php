<?php

namespace App\Modules\News\Repositories\Eloquent;

use App\Modules\News\Models\News;
use App\Modules\News\Repositories\Contracts\INewsRepository;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NewsRepository extends BaseRepository implements INewsRepository
{
    /**
     * @var News
     */
    protected $news;

    /**
     * @var Request
     */
    protected $request;

    /**
     * NewsRepository constructor.
     * @param News $model
     */
    public function __construct(News $model)
    {
        parent::__construct($model);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function filterData($params)
    {
        $query = News::query();

        // Filter by status
        if (isset($params['status']) && $params['status'] !== 'all') {
            $query->where('status', $params['status']);
        }

        // Filter by admin (manager) if not administrator
        if (auth()->user()->getRolesNameAttribute() != 'administrator') {
            $query->where('admin_id', auth()->user()->getAuthIdentifier());
        }

        // Search in title and content
        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function ($q) use ($params) {
                $q->where('title', 'LIKE', '%' . $params['search'] . '%')
                  ->orWhere('content', 'LIKE', '%' . $params['search'] . '%');
            });
        }

        return $query;
    }

    /**
     * @param $data
     * @param $sort
     * @return mixed
     */
    public function sortData($data, $sort)
    {
        return $data->orderBy('created_at', 'desc');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function saveData(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->request = $request;

            $updateInfo = $request->only(['title', 'content', 'status']);
            
            // Set admin_id - use provided admin_id if user is administrator, otherwise use current user
            if ($request->filled('admin_id') && auth()->user()->getRolesNameAttribute() == 'administrator') {
                $updateInfo['admin_id'] = $request->get('admin_id');
            } else {
                $updateInfo['admin_id'] = auth()->user()->getAuthIdentifier();
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('news/images', 'public');
                $updateInfo['image'] = $imagePath;
            }

            if (!empty($request->get('id'))) {
                $this->news = $this->find($request->get('id'));
                
                // Delete old image if new one is uploaded
                if ($request->hasFile('image') && $this->news->image) {
                    Storage::disk('public')->delete($this->news->image);
                }
                
                $this->news->update($updateInfo);
            } else {
                $this->news = $this->create($updateInfo);
            }

            // Sync investors
            if (!empty($request->get('investor_ids'))) {
                $this->news->investors()->sync($request->get('investor_ids'));
            } else {
                $this->news->investors()->detach();
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }
}
