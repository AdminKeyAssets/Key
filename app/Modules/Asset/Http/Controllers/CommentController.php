<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Asset\Http\Requests\PaymentRequest;
use App\Modules\Asset\Models\Comment;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;

class CommentController extends BaseController
{
    /**
     * SlugController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $assetId)
    {
        $comments = Comment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->where('asset_id', $assetId)->get();

        return ServiceResponse::jsonNotification('', 200, $comments);;
    }

    public function store(Request $request, $assetId)
    {
        $path = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('uploads', 'public');
        }

//        dd($path);
//        dd(Storage::url($path));
        Comment::create([
            'comment' => $request->comment,
            'asset_id' => $assetId,
            'admin_id' => Auth::user()->getAuthIdentifier(),
            'attachment' => Storage::url($path)
        ]);

        $comments = Comment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->where('asset_id', $assetId)->get();

        return ServiceResponse::jsonNotification('Comment Added successfully', 200, $comments);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
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
            ->where('asset_id', $assetId)->get();

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $comments);
    }

    public function read($commentId)
    {
        $comment = Comment::where('id',$commentId)->first();

        $assetId = $comment->asset_id;

        Comment::where('asset_id', $assetId)->update([
            'read' => 1
        ]);

        return redirect(route('asset.view', [$assetId]));
    }
    public function unread(Request $request)
    {
        $comments = Comment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->where('read', 0)->get();

        return ServiceResponse::jsonNotification('', 200, $comments);;
    }
}
