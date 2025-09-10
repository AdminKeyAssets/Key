<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Modules\Admin\Http\Requests\News\SaveNewsRequest;
use App\Modules\Admin\Models\News;
use App\Modules\Admin\Models\NewsImage;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Developer;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Asset;
use App\Utilities\ServiceResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NewsController extends BaseController
{
    /**
     * @var string
     */
    public $viewFolderName = 'news';

    /**
     * NewsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'news';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }

    /**
     * Display a listing of the news.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = News::with(['admin', 'manager', 'developer', 'investors']);

        // Filter by user type
        if (Auth::user()->hasRole('developer')) {
            // Developers can only see news for investors attached to their assets
            $developer = Developer::where('admin_id', Auth::id())->first();
            if ($developer) {
                $developerAssetNames = $developer->assets()->pluck('asset_name')->toArray();
                $investorIds = Asset::whereIn('project_name', $developerAssetNames)
                    ->where('developer_access', 1)
                    ->whereNotNull('investor_id')
                    ->pluck('investor_id')
                    ->unique();

                // Also get investors from many-to-many relationship
                $additionalInvestorIds = Asset::whereIn('project_name', $developerAssetNames)
                    ->where('developer_access', 1)
                    ->with('investors')
                    ->get()
                    ->pluck('investors')
                    ->flatten()
                    ->pluck('id')
                    ->unique();

                $allInvestorIds = $investorIds->merge($additionalInvestorIds)->unique();

                $query->whereHas('investors', function ($q) use ($allInvestorIds) {
                    $q->whereIn('investor_id', $allInvestorIds);
                });
            } else {
                // No assets means no news access
                $query->where('id', null);
            }
        } elseif (!Auth::user()->hasRole('administrator')) {
            // Regular managers can only see news they created or are assigned to
            $query->where(function ($q) {
                $q->where('admin_id', Auth::id())
                  ->orWhere('manager_id', Auth::id());
            });
        }

        // Search functionality
        if ($request->search && $request->search != 'all' && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('content', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->create_date) {
            $dates = explode(',', $request->create_date);
            if (count($dates) == 2) {
                $startDate = $dates[0];
                $endDate = $dates[1];
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
        }

        // Filter by manager (only for administrators)
        if ($request->manager && $request->manager != 'all' && Auth::user()->hasRole('administrator')) {
            $parts = explode(' ', $request->manager);
            $first = array_shift($parts);
            $last = implode(' ', $parts);
            $managerUser = Admin::where('name', $first)
                ->where('surname', $last)
                ->first();
            if ($managerUser) {
                $query->where(function ($q) use ($managerUser) {
                    $q->where('admin_id', $managerUser->id)
                      ->orWhere('manager_id', $managerUser->id);
                });
            }
        }

        // Filter by investor
        if ($request->investor && $request->investor != 'all') {
            $parts = explode(' ', $request->investor);
            $first = array_shift($parts);
            $last = implode(' ', $parts);
            $investor = Investor::where('name', $first)
                ->where('surname', $last)
                ->first();
            if ($investor) {
                $query->whereHas('investors', function ($q) use ($investor) {
                    $q->where('investor_id', $investor->id);
                });
            }
        }

        $this->baseData['allData'] = $query->orderBy('created_at', 'desc')->paginate();

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * Show the form for creating a new news.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        try {
            $this->baseData['routes'] = [
                'create_form_data' => route('admin.news.create_form_data'),
                'create' => route('admin.news.create_form'),
                'save' => route('admin.news.save'),
                'delete' => route('admin.news.delete')
            ];

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.create', ServiceResponse::success($this->baseData));
    }

    /**
     * Show the form for editing the specified news.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('admin.news.create_form_data');
            $this->baseData['routes']['save'] = route('admin.news.save');
            $this->baseData['id'] = $id;

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.edit', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.edit', ServiceResponse::success($this->baseData));
    }

    /**
     * Display the specified news.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('admin.news.create_form_data');
            $this->baseData['id'] = $id;

            // Load news to get the title for the header
            $news = News::findOrFail($id);
            
            // Check permissions
            if (!$this->canAccessNews($news)) {
                abort(403, 'Unauthorized');
            }
            
            $this->baseData['news_title'] = $news->title;

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::success($this->baseData));
    }

    /**
     * Get Create/Update form data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreateData(Request $request)
    {
        try {
            $this->baseData['routes'] = [
                'create_form_data' => route('admin.news.create_form_data'),
                'create' => route('admin.news.create_form'),
                'save' => route('admin.news.save'),
                'delete' => route('admin.news.delete')
            ];

            if ($request->get('id')) {
                $news = News::with(['investors', 'images'])->findOrFail($request->get('id'));

                // Check permissions
                if (!$this->canAccessNews($news)) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }

                $this->baseData['item'] = $news;
                $this->baseData['item']['investor_ids'] = $news->investors->pluck('id')->toArray();

                // Check if user can edit this news
                $this->baseData['can_edit'] = Auth::user()->hasRole('administrator') ||
                                              $news->admin_id == Auth::id() ||
                                              $news->manager_id == Auth::id();

                // Format images for frontend
                $gallery = $news->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image' => $image->image,
                        'name' => $image->name,
                        'preview' => $image->image,
                        'fileName' => $image->name,
                        'is_thumbnail' => $image->is_thumbnail
                    ];
                });
                $this->baseData['item']['gallery'] = $gallery;
            }

            // Get available investors based on user role
            $investors = $this->getAvailableInvestors();
            $this->baseData['investors'] = $investors->map(function ($investor) {
                return [
                    'id' => $investor->id,
                    'name' => $investor->name,
                    'surname' => $investor->surname,
                    'full_name' => $investor->full_name ?: ($investor->name . ' ' . $investor->surname)
                ];
            })->toArray();

            // Get available managers (only for admins)
            if (Auth::user()->hasRole('administrator')) {
                $managers = Admin::where('id', '!=', Auth::id())->get(['id', 'name', 'surname', 'full_name']);
                $this->baseData['managers'] = $managers->map(function ($manager) {
                    return [
                        'id' => $manager->id,
                        'name' => $manager->name,
                        'surname' => $manager->surname,
                        'full_name' => $manager->full_name ?: ($manager->name . ' ' . $manager->surname)
                    ];
                })->toArray();

                // Get available developers
                $developers = Developer::get(['id', 'name']);
                $this->baseData['developers'] = $developers->map(function ($developer) {
                    return [
                        'id' => $developer->id,
                        'name' => $developer->name,
                        'surname' => '', // Developers don't have surname
                        'full_name' => $developer->name // Use name as full_name for developers
                    ];
                })->toArray();
            } else {
                $this->baseData['managers'] = [];
                $this->baseData['developers'] = [];
            }

        } catch (\Exception $ex) {
            Log::error('Error during news create data', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return response()->json(ServiceResponse::error($ex->getMessage()));
        }

        return response()->json(ServiceResponse::success($this->baseData));
    }

    /**
     * Save news.
     *
     * @param SaveNewsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(SaveNewsRequest $request)
    {
        try {
            $newsData = [
                'title' => $request->title,
                'content' => $request->get('content'),
                'status' => $request->status ?? 'draft',
                'admin_id' => Auth::id(),
            ];

            // Handle manager assignment (only for admins)
            if (Auth::user()->hasRole('administrator') && $request->manager_id) {
                $newsData['manager_id'] = $request->manager_id;
            }

            // Handle developer assignment (only for admins)
            if (Auth::user()->hasRole('administrator') && $request->developer_id) {
                $newsData['developer_id'] = $request->developer_id;
            }

            // Set created_by_type
            if (Auth::user()->hasRole('administrator')) {
                $newsData['created_by_type'] = 'admin';
            } else {
                $newsData['created_by_type'] = 'manager';
            }

            // Set published_at if status is published
            if ($request->status === 'published') {
                $newsData['published_at'] = now();
            }

            if ($request->id) {
                $news = News::findOrFail($request->id);

                // Check permissions
                if (!$this->canEditNews($news)) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }

                $news->update($newsData);
            } else {
                $news = News::create($newsData);
            }

            // Handle thumbnail from gallery
            $this->handleNewsImages($news, $request);

            // Sync investors
            if ($request->investor_ids) {
                $investorIds = is_array($request->investor_ids) ? $request->investor_ids : explode(',', $request->investor_ids);

                // Filter investors based on user permissions
                $availableInvestorIds = $this->getAvailableInvestors()->pluck('id')->toArray();
                $validInvestorIds = array_intersect($investorIds, $availableInvestorIds);

                $news->investors()->sync($validInvestorIds);
            } else {
                $news->investors()->detach();
            }

            return response()->json(ServiceResponse::success([
                'item' => $news,
                'message' => $request->id ? $this->successUpdateText : $this->successCreateText
            ]));

        } catch (\Exception $ex) {
            Log::error('Error during news save', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return response()->json(ServiceResponse::error($ex->getMessage()));
        }
    }

    /**
     * Delete news.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            $news = News::findOrFail($request->id);

            // Check permissions
            if (!$this->canDeleteNews($news)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Delete associated images
            $news->images()->each(function ($image) {
                if ($image->image && Storage::disk('public')->exists(str_replace('/storage/', '', $image->image))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $image->image));
                }
                $image->delete();
            });

            $news->delete();

            return response()->json(ServiceResponse::success(['message' => $this->successDeleteText]));

        } catch (\Exception $ex) {
            Log::error('Error during news delete', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return response()->json(ServiceResponse::error($ex->getMessage()));
        }
    }

    /**
     * Handle news images upload and management.
     *
     * @param News $news
     * @param Request $request
     */
    private function handleNewsImages(News $news, Request $request)
    {
        if (!$request->has('gallery')) {
            // No gallery data provided, keep existing images
            return;
        }

        // Get existing images for comparison
        $existingImages = $news->images->keyBy('image');
        $imagesToKeep = [];
        $newImages = [];

        // Process gallery data to determine what to keep and what's new
        foreach ($request->gallery as $index => $file) {
            if (gettype($file) == 'string') {
                // This is an existing image URL - keep it
                $imagesToKeep[] = $file;
            } else {
                // This is a new file upload
                $newImages[$index] = $file;
            }
        }

        // Delete images that are no longer in the gallery
        $imagesToDelete = $existingImages->reject(function ($image) use ($imagesToKeep) {
            return in_array($image->image, $imagesToKeep);
        });

        foreach ($imagesToDelete as $image) {
            if ($image->image && Storage::disk('public')->exists(str_replace('/storage/', '', $image->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->image));
            }
            $image->delete();
        }

        // Update order for existing images and add new images
        foreach ($request->gallery as $index => $file) {
            $imagePath = null;
            $fileName = null;

            if (gettype($file) == 'string') {
                // Existing image URL - update order only
                $existingImage = $existingImages->get($file);
                if ($existingImage) {
                    $existingImage->update([
                        'order' => $index,
                        'is_thumbnail' => $index === 0
                    ]);
                    $imagePath = $file;
                }
            } else {
                // New file upload
                $originalFileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
                $imagePath = $file->storeAs('uploads/news', $originalFileName, 'public');
                $imagePath = Storage::url($imagePath);
                $fileName = $originalFileName;

                NewsImage::create([
                    'news_id' => $news->id,
                    'image' => $imagePath,
                    'name' => $fileName,
                    'order' => $index,
                    'is_thumbnail' => $index === 0
                ]);
            }

            // Set thumbnail on news model (first image)
            if ($index === 0 && $imagePath) {
                $news->update(['thumbnail' => $imagePath]);
            }
        }
    }

    /**
     * Get available investors for developers (from their assets)
     *
     * @param int $developerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getDeveloperAvailableInvestors($developerId)
    {
        // Get developer's asset names from developer_assets table
        $developer = Developer::find($developerId);
        $assetNames = $developer->assets()->pluck('asset_name');

        // Get all unique investors from assets that:
        // 1. Have developer_access = true
        // 2. Are managed by this developer (project_name in asset_names)
        $investors = Investor::whereHas('assets', function ($query) use ($assetNames) {
            $query->where('developer_access', 1)
                  ->whereIn('project_name', $assetNames);
        })->get();

        return $investors;
    }

    /**
     * Get available investors for all news management
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getAvailableInvestors()
    {
        if (Auth::user()->hasRole('administrator')) {
            return Investor::select(['id', 'name', 'surname', 'full_name'])->get();
        } else {
            // Regular managers can access investors attached to their assets
            return Investor::where('admin_id', Auth::id())->select(['id', 'name', 'surname', 'full_name'])->get();
        }
    }

    /**
     * Get filter options for news.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterOptions()
    {
        try {
            $this->baseData = [];

            // Get available investors based on user role
            $investors = $this->getAvailableInvestors();
            $this->baseData['investors'] = $investors->map(function ($investor) {
                return [
                    'id' => $investor->id,
                    'name' => $investor->name,
                    'surname' => $investor->surname,
                    'full_name' => $investor->full_name ?: ($investor->name . ' ' . $investor->surname)
                ];
            })->toArray();

            // Get available managers (only for admins)
            if (Auth::user()->hasRole('administrator')) {
                $managers = Admin::where('id', '!=', Auth::id())->get(['id', 'name', 'surname', 'full_name']);
                $this->baseData['managers'] = $managers->map(function ($manager) {
                    return [
                        'id' => $manager->id,
                        'name' => $manager->name,
                        'surname' => $manager->surname,
                        'full_name' => $manager->full_name ?: ($manager->name . ' ' . $manager->surname)
                    ];
                })->toArray();

                // Get available developers
                $developers = Developer::get(['id', 'name']);
                $this->baseData['developers'] = $developers->map(function ($developer) {
                    return [
                        'id' => $developer->id,
                        'name' => $developer->name,
                        'surname' => '', // Developers don't have surname
                        'full_name' => $developer->name // Use name as full_name for developers
                    ];
                })->toArray();
            } else {
                $this->baseData['managers'] = [];
                $this->baseData['developers'] = [];
            }

        } catch (\Exception $ex) {
            return response()->json(ServiceResponse::error($ex->getMessage()));
        }

        return response()->json(ServiceResponse::success($this->baseData));
    }
    private function canAccessNews(News $news)
    {
        if (Auth::user()->hasRole('administrator')) {
            return true;
        }

        return $news->admin_id === Auth::id() || $news->manager_id === Auth::id();
    }

    /**
     * Check if user can edit news.
     *
     * @param News $news
     * @return bool
     */
    private function canEditNews(News $news)
    {
        if (Auth::user()->hasRole('administrator')) {
            return true;
        }

        // Only creators can edit their news
        return $news->admin_id === Auth::id();
    }

    /**
     * Check if user can delete news.
     *
     * @param News $news
     * @return bool
     */
    private function canDeleteNews(News $news)
    {
        if (Auth::user()->hasRole('administrator')) {
            return true;
        }

        // Only creators can delete their news
        return $news->admin_id === Auth::id();
    }

    /**
     * News index page for developers (only their own news)
     * 
     * @return mixed
     */
    public function developerIndex()
    {
        $this->baseData['moduleKey'] = 'developer_news';
        $this->baseData['baseRouteName'] = 'developer.news.';

        $developerId = auth('developer')->id();
        
        // Get all news assigned to this developer (created by developer or admin)
        $this->baseData['allData'] = News::forDeveloper($developerId)
            ->with(['admin', 'developer', 'manager', 'images', 'investors'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('admin::admin.news.developer_index', $this->baseData);
    }

    /**
     * News view page for developers (only their own news)
     * 
     * @param int $id
     * @return mixed
     */
    public function developerView($id)
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('developer.news.create_form_data');
            $this->baseData['id'] = $id;
            $this->baseData['moduleKey'] = 'developer_news';
            $this->baseData['baseRouteName'] = 'developer.news.';

            // Load news to get the title for the header
            $developerId = auth('developer')->id();
            $news = News::forDeveloper($developerId)->findOrFail($id);
            $this->baseData['news_title'] = $news->title;

        } catch (\Exception $ex) {
            return view('admin::admin.news.developer_view', ServiceResponse::error($ex->getMessage()));
        }

        return view('admin::admin.news.developer_view', ServiceResponse::success($this->baseData));
    }

    /**
     * Developer news create form
     * 
     * @return mixed
     */
    public function developerCreate()
    {
        try {
            $this->baseData['moduleKey'] = 'developer_news';
            $this->baseData['baseRouteName'] = 'developer.news.';
            $this->baseData['routes']['create_form_data'] = route('developer.news.create_form_data');
            $this->baseData['routes']['save'] = route('developer.news.save');

            return view('admin::admin.news.developer_create', ServiceResponse::success($this->baseData));

        } catch (\Exception $ex) {
            return view('admin::admin.news.developer_create', ServiceResponse::error($ex->getMessage()));
        }
    }

    /**
     * Developer news edit form
     * 
     * @param int $id
     * @return mixed
     */
    public function developerEdit($id)
    {
        try {
            $this->baseData['moduleKey'] = 'developer_news';
            $this->baseData['baseRouteName'] = 'developer.news.';
            $this->baseData['routes']['create_form_data'] = route('developer.news.create_form_data');
            $this->baseData['routes']['save'] = route('developer.news.save');
            $this->baseData['id'] = $id;

            $developerId = auth('developer')->id();
            
            // Ensure developer can only edit their own news
            $news = News::forDeveloper($developerId)
                ->findOrFail($id);

            // Check if developer can edit this news (only if they created it)
            if ($news->created_by_type !== 'developer') {
                abort(403, 'You can only edit news that you created yourself.');
            }

            return view('admin::admin.news.developer_edit', ServiceResponse::success($this->baseData));

        } catch (\Exception $ex) {
            return view('admin::admin.news.developer_edit', ServiceResponse::error($ex->getMessage()));
        }
    }

    /**
     * Get Create/Update form data for developers
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function developerGetCreateData(Request $request)
    {
        try {
            $developerId = auth('developer')->id();
            $this->baseData['routes'] = [
                'create_form_data' => route('developer.news.create_form_data'),
                'save' => route('developer.news.save'),
                'delete' => route('developer.news.delete')
            ];

            if ($request->get('id')) {
                $news = News::forDeveloper($developerId)
                    ->with(['investors', 'images'])
                    ->findOrFail($request->get('id'));

                $this->baseData['item'] = $news;
                $this->baseData['item']['investor_ids'] = $news->investors->pluck('id')->toArray();
                
                // Developers can only edit news they created themselves
                $this->baseData['can_edit'] = $news->created_by_type === 'developer';

                // Format images for frontend
                $gallery = $news->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image' => $image->image,
                        'name' => $image->name,
                        'preview' => $image->image,
                        'fileName' => $image->name,
                        'is_thumbnail' => $image->is_thumbnail
                    ];
                });
                $this->baseData['item']['gallery'] = $gallery;
            }

            // Get available investors for this developer (from their assets)
            $investors = $this->getDeveloperAvailableInvestors($developerId);
            $this->baseData['investors'] = $investors->map(function ($investor) {
                return [
                    'id' => $investor->id,
                    'name' => $investor->name,
                    'surname' => $investor->surname,
                    'full_name' => $investor->full_name ?: ($investor->name . ' ' . $investor->surname)
                ];
            })->toArray();

            // Developers don't assign managers
            $this->baseData['managers'] = [];

        } catch (\Exception $ex) {
            Log::error('Error during developer news create data', ['message' => $ex->getMessage(), 'data' => $request->all()]);
            return response()->json(ServiceResponse::error($ex->getMessage()));
        }

        return response()->json(ServiceResponse::success($this->baseData));
    }

    /**
     * Save developer news
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function developerSave()
    {
        $input = request()->all();
        $developerId = auth('developer')->id();

        $validator = Validator::make($input, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'thumbnail' => 'nullable|string',
            'investor_ids' => 'nullable|string', // Comma-separated investor IDs
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            $news = new News();
            if (!empty($input['id'])) {
                // Editing existing news - ensure developer owns it and created it
                $existingNews = News::forDeveloper($developerId)
                    ->findOrFail($input['id']);
                
                // Check if developer can edit this news (only if they created it)
                if ($existingNews->created_by_type !== 'developer') {
                    return response()->json([
                        'status' => false,
                        'message' => 'You can only edit news that you created yourself.',
                    ]);
                }
                
                $news = $existingNews;
            }

            $news->title = $input['title'];
            $news->content = $input['content'];
            $news->status = $input['status'];
            $news->thumbnail = $input['thumbnail'] ?? null;
            $news->developer_id = $developerId;
            $news->created_by_type = 'developer';
            
            if ($input['status'] === 'published' && !$news->published_at) {
                $news->published_at = now();
            }

            $news->save();

            // Handle image uploads for developers
            $this->handleNewsImages($news, request());

            // Handle investor attachments for developers
            if (!empty($input['investor_ids'])) {
                $investorIds = is_string($input['investor_ids']) 
                    ? explode(',', $input['investor_ids']) 
                    : $input['investor_ids'];
                $investorIds = array_filter($investorIds); // Remove empty values

                // Get available investors for this developer
                $availableInvestorIds = $this->getDeveloperAvailableInvestors($developerId)->pluck('id')->toArray();
                
                // Only attach investors that are available to this developer
                $validInvestorIds = array_intersect($investorIds, $availableInvestorIds);

                $news->investors()->sync($validInvestorIds);
            } else {
                $news->investors()->detach();
            }

            return response()->json([
                'status' => true,
                'message' => 'News saved successfully!',
                'redirect' => route('developer.news.index')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error saving news: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete developer news
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function developerDelete()
    {
        $input = request()->all();
        $developerId = auth('developer')->id();

        try {
            $news = News::forDeveloper($developerId)
                ->findOrFail($input['id']);

            // Check if developer can delete this news (only if they created it)
            if ($news->created_by_type !== 'developer') {
                return response()->json([
                    'status' => false,
                    'message' => 'You can only delete news that you created yourself.',
                ]);
            }

            $news->delete();

            return response()->json([
                'status' => true,
                'message' => 'News deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting news: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * News index page for investors (only news attached to them)
     * 
     * @return mixed
     */
    public function investorIndex()
    {
        $this->baseData['moduleKey'] = 'investor_news';
        $this->baseData['baseRouteName'] = 'investor.news.';

        $investorId = auth('investor')->id();
        
        // Get only published news attached to this investor
        $this->baseData['allData'] = News::published()
            ->with(['admin', 'manager', 'images'])
            ->forInvestor($investorId)
            ->orderBy('published_at', 'desc')
            ->paginate();

        return view('admin::admin.news.investor_index', $this->baseData);
    }

    /**
     * News view page for investors
     * 
     * @param int $id
     * @return mixed
     */
    public function investorView($id)
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('investor.news.create_form_data');
            $this->baseData['id'] = $id;
            $this->baseData['moduleKey'] = 'investor_news';
            $this->baseData['baseRouteName'] = 'investor.news.';

            // Load news to get the title for the header
            $investorId = auth('investor')->id();
            $news = News::published()->forInvestor($investorId)->findOrFail($id);
            $this->baseData['news_title'] = $news->title;

        } catch (\Exception $ex) {
            return view('admin::admin.news.investor_view', ServiceResponse::error($ex->getMessage()));
        }

        return view('admin::admin.news.investor_view', ServiceResponse::success($this->baseData));
    }

    /**
     * Get news data for investor viewing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function investorGetCreateData(Request $request)
    {
        $investorId = auth('investor')->id();
        $id = $request->input('id');

        try {
            if ($id) {
                // Load existing news for viewing (only published news attached to investor)
                $news = News::published()
                    ->with(['admin', 'manager', 'images', 'investors'])
                    ->forInvestor($investorId)
                    ->findOrFail($id);

                // Mark news as read by this investor
                $news->markAsReadByInvestor($investorId);

                $item = $news->toArray();
                
                // Format images for frontend
                if ($news->images) {
                    $item['images'] = $news->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'image' => $image->image,
                            'preview' => $image->image,
                            'name' => $image->name,
                            'fileName' => $image->name,
                            'is_thumbnail' => $image->is_thumbnail
                        ];
                    })->toArray();
                }

                // Format investors for frontend
                if ($news->investors) {
                    $item['investor_ids'] = $news->investors->pluck('id')->toArray();
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'item' => $item,
                        'investors' => [],
                        'managers' => []
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'News not found'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading news: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get unread news count for investor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount()
    {
        $investorId = auth('investor')->id();
        $unreadCount = News::getUnreadCountForInvestor($investorId);
        
        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }
}
