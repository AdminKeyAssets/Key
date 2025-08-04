<?php

namespace App\Modules\News\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\News\Helper\NewsHelper;
use App\Modules\News\Http\Requests\SaveNewsRequest;
use App\Modules\News\Models\News;
use App\Modules\News\Repositories\Contracts\INewsRepository;
use App\Utilities\ServiceResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NewsController extends BaseController
{
    /**
     * @var string
     */
    protected $viewFolderName = 'news';
    protected $baseModuleName = 'news::';

    /**
     * @var INewsRepository
     */
    protected $newsRepository;

    /**
     * NewsController constructor.
     * @param INewsRepository $newsRepository
     */
    public function __construct(INewsRepository $newsRepository)
    {
        parent::__construct();
        $this->newsRepository = $newsRepository;
        $this->baseData['moduleKey'] = 'news';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
        $this->baseData['trans_text'] = NewsHelper::getLang();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $query = $this->newsRepository->filterData($request->all());
            $this->baseData['allData'] = $this->newsRepository->sortData($query, $request->get('sort'))->paginate(25);
        } catch (\Exception $ex) {
            Log::error('Error during news index page', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($id = '')
    {
        try {
            $this->baseData['model'] = $id ? $this->newsRepository->find($id) : new News();
            $this->baseData['model']->load(['investors']);
            
            // Prepare data for the view in the expected format
            $data = [
                'news' => $this->baseData['model'],
                'trans_text' => $this->baseData['trans_text'],
                'routes' => [
                    'create_form_data' => route($this->baseData['baseRouteName'] . 'create_form_data')
                ]
            ];
            
        } catch (\Exception $ex) {
            Log::error('Error during news create page', ['message' => $ex->getMessage(), 'id' => $id]);
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', compact('data'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreateData(Request $request)
    {
        try {
            $user = auth()->user();
            $userRole = method_exists($user, 'getRolesNameAttribute') ? $user->getRolesNameAttribute() : '';

            $data = [
                'trans_text' => $this->baseData['trans_text'],
                'userRole' => $userRole,
            ];

            if ($userRole === 'administrator') {
                $data['managers'] = \App\Modules\Admin\Models\User\Admin::all();
            }

            $data['investors'] = Investor::all();

            $id = $request->input('id');
            if ($id) {
                $data['model'] = $this->newsRepository->find($id);
                $data['model']->load(['investors']);
            }

            return response()->json([
                'status' => 200,
                'data' => $data
            ]);

        } catch (\Exception $ex) {
            Log::error('Error during news getCreateData', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return response()->json([
                'status' => 500,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * @param SaveNewsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(SaveNewsRequest $request)
    {
        try {
            $data = $request->all();

            // Handle file upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('news', 'public');
                $data['image'] = $imagePath;
            }

            // Set admin_id
            $data['admin_id'] = auth()->user()->getAuthIdentifier();

            // Save or update news
            if (isset($data['id']) && $data['id']) {
                $news = $this->newsRepository->find($data['id']);
                if (!$news) {
                    return ServiceResponse::jsonNotification('News not found', 404, []);
                }
                $news = $this->newsRepository->update($data, $data['id']);
            } else {
                $news = $this->newsRepository->save($data);
            }

            // Sync investors
            if (isset($data['investor_ids']) && is_array($data['investor_ids'])) {
                $news->investors()->sync($data['investor_ids']);
            } else {
                $news->investors()->detach();
            }

        } catch (\Exception $ex) {
            Log::error('Error during news save', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification($this->baseData['trans_text']['save_successfully'], 200, $this->baseData);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->input('id');
            $news = $this->newsRepository->find($id);

            if (!$news) {
                return ServiceResponse::jsonNotification('News not found', 404, []);
            }

            // Delete associated image if exists
            if ($news->image && Storage::disk('public')->exists($news->image)) {
                Storage::disk('public')->delete($news->image);
            }

            // Delete news
            $news->delete();

        } catch (\Exception $ex) {
            Log::error('Error during news delete', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return ServiceResponse::jsonNotification($ex->getMessage(), $ex->getCode(), []);
        }

        return ServiceResponse::jsonNotification($this->baseData['trans_text']['delete_successfully'], 200, $this->baseData);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view(Request $request, $id = '')
    {
        try {
            $news = $this->newsRepository->find($id);
            if (!$news) {
                return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::error(__('News not found')));
            }
            // Load relationships
            $news->load(['admin', 'investors']);

            $this->baseData['news'] = $news;
            $this->baseData['trans_text'] = [
                'view' => __('View News'),
                'details' => __('Details'),
                'manager' => __('Manager'),
                'created_at' => __('Created At'),
                'updated_at' => __('Updated At'),
                'investors' => __('Investors'),
                'all_investors' => __('Available to all investors'),
                'back_to_list' => __('Back to List'),
                'news_information' => __('News Information'),
                'title' => __('Title'),
                'content' => __('Content'),
                'status' => __('Status'),
                'image' => __('Image'),
                'published' => __('Published'),
                'draft' => __('Draft'),
                'archived' => __('Archived'),
                'edit' => __('Edit News'),
                'back' => __('Back to List'),
            ];
        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::error($ex->getMessage()));
        }
        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::success($this->baseData));
    }

    /**
     * Get data for News view page (for Vue component)
     */
    public function getSaveData(Request $request)
    {
        $id = $request->input('id');
        $news = $this->newsRepository->find($id);
        if (!$news) {
            return response()->json([
                'status' => 404,
                'message' => __('News not found'),
            ], 404);
        }
        $news->load(['admin', 'investors']);

        $user = auth()->user();
        $userRole = method_exists($user, 'getRolesNameAttribute') ? $user->getRolesNameAttribute() : '';

        return response()->json([
            'status' => 200,
            'data' => [
                'item' => $news,
                'userRole' => $userRole,
                'userId' => $user ? $user->getAuthIdentifier() : null,
                'trans_text' => $this->baseData['trans_text'],
                'routes' => [
                    'index' => route($this->baseData['baseRouteName'] . 'index'),
                    'create' => route($this->baseData['baseRouteName'] . 'create_form', [$news->id]),
                ],
            ]
        ]);
    }
}
