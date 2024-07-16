<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Asset\Http\Requests\PaymentRequest;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\Comment;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;

class CommentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @param $assetId
     * @return JsonResponse
     */
    public function index(Request $request, $assetId)
    {
        $comments = Comment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['investor' => function ($query) {
                $query->select('id', 'name', 'surname');
            }])
            ->where('asset_id', $assetId)->get();

        return ServiceResponse::jsonNotification('', 200, $comments);
    }


    /**
     * @param Request $request
     * @param $assetId
     * @return JsonResponse
     */
    public function store(Request $request, $assetId)
    {
        $path = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('uploads', 'public');
        }

        Comment::create([
            'comment' => $request->comment,
            'asset_id' => $assetId,
            'admin_id' => Auth::user()->getAuthIdentifier(),
            'investor_id' => null,
            'attachment' => $path ? Storage::url($path) : null
        ]);

        $comments = Comment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['investor' => function ($query) {
                $query->select('id', 'name', 'surname');
            }])
            ->where('asset_id', $assetId)->get();

        return ServiceResponse::jsonNotification('Comment Added successfully', 200, $comments);
    }


    /**
     * @param Request $request
     * @param $assetId
     * @return JsonResponse
     */
    public function investorStore(Request $request, $assetId)
    {
        $path = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('uploads', 'public');
        }

        Comment::create([
            'comment' => $request->comment,
            'asset_id' => $assetId,
            'admin_id' => null,
            'investor_id' => Auth::user()->getAuthIdentifier(),
            'attachment' => $path ? Storage::url($path) : null
        ]);

        $comments = Comment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['investor' => function ($query) {
                $query->select('id', 'name', 'surname');
            }])
            ->where('asset_id', $assetId)->get();

        return ServiceResponse::jsonNotification('Comment Added successfully', 200, $comments);
    }

    /**
     * @param Request $request
     * @param $assetId
     * @param $commentId
     * @return JsonResponse
     */
    public function destroy(Request $request, $assetId, $commentId)
    {
        try {
            Comment::findOrFail($commentId)->delete();


        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
        $comments = Comment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['investor' => function ($query) {
                $query->select('id', 'name', 'surname');
            }])
            ->where('asset_id', $assetId)->get();

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $comments);
    }

    /**
     * @param $commentId
     * @return Application|RedirectResponse|Redirector
     */
    public function read($commentId)
    {
        $comment = Comment::where('id', $commentId)->first();
        $assetId = $comment->asset_id;
        $user = auth('investor')->user();

        if (!$user) {
            $user = auth('admin')->user();
        }

        $comments = Comment::where('asset_id', $assetId);
        $route = route('asset.view', [$assetId]);
        if (Auth::guard('investor')->check()) {
            $comments = $comments->where('investor_id', '!=', $user->getAuthIdentifier())
                ->orWhereNull('investor_id');
            $route = route('asset.details', [$assetId]);
        } elseif (Auth::guard('admin')->check()) {
            $comments = $comments->where('admin_id', '!=', $user->getAuthIdentifier())
                ->orWhereNull('admin_id');
        }

        $comments->update([
            'read' => 1
        ]);

        return redirect($route . '#comments');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function unread(Request $request)
    {
        $user = auth('investor')->user();

        if (!$user) {
            $user = auth('admin')->user();
        }

        $managers = ['Asset Manager', 'AssetManager', 'Sales Manager', 'SalesManager'];
        $assets = null;

        if (Auth::guard('investor')->check()) {
            $assets = Asset::where('investor_id', $user->getAuthIdentifier())->get();
        } elseif (Auth::guard('admin')->check()) {
            if (in_array($user->getRolesNameAttribute(), $managers)) {
                $assets = Asset::where('admin_id', $user->getAuthIdentifier())->get();
            } else if ($user->getRolesNameAttribute() === 'administrator') {
                $assets = Asset::all();
            }
        }

        $assetIds = [];
        foreach ($assets as $asset) {
            $assetIds[] = $asset->id;
        }

        $comments = Comment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name', 'surname');
            }])
            ->with(['investor' => function ($query) {
                $query->select('id', 'name', 'surname');
            }])
            ->whereIn('asset_id', $assetIds);


        if (Auth::guard('admin')->check()) {
            $comments = $comments->where(function ($query) use ($user) {
                $query->where('admin_id', '!=', $user->getAuthIdentifier())
                    ->orWhereNull('admin_id');
            })->get();
        } else if (Auth::guard('investor')->check()) {
            $comments = $comments->where(function ($query) use ($user) {
                $query->where('investor_id', '!=', $user->getAuthIdentifier())
                    ->orWhereNull('investor_id');
            })->get();

        }

        return ServiceResponse::jsonNotification('', 200, $comments);
    }
}
