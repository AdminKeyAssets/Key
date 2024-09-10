<?php

namespace App\Modules\Lead\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Asset\Models\Comment;
use App\Modules\Lead\Models\LeadComment;
use App\Utilities\ServiceResponse;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;

class LeadCommentController extends BaseController
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
        $comments = LeadComment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->where('lead_id', $assetId)->get();

        return ServiceResponse::jsonNotification('', 200, $comments);
    }

    /**
     * @param Request $request
     * @param $leadId
     * @return JsonResponse
     */
    public function store(Request $request, $leadId)
    {
        $path = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $originalFilename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $originalFilename, 'public');
        }

        LeadComment::create([
            'comment' => $request->comment,
            'lead_id' => $leadId,
            'admin_id' => Auth::user()->getAuthIdentifier(),
            'attachment' => $path ? Storage::url($path) : null
        ]);

        $comments = LeadComment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->where('lead_id', $leadId)->get();

        return ServiceResponse::jsonNotification('Comment Added successfully', 200, $comments);
    }

    /**
     * @param Request $request
     * @param $leadId
     * @param $commentId
     * @return JsonResponse
     */
    public function destroy(Request $request, $leadId, $commentId)
    {
        try {
            LeadComment::findOrFail($commentId)->delete();
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
        $comments = LeadComment::query()
            ->with(['admin' => function ($query) {
                $query->select('id', 'name');
            }])
            ->where('lead_id', $leadId)->get();

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $comments);
    }
}
