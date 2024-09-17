<?php

namespace App\Modules\Asset\Services;

use App\Modules\Asset\Models\Comment;

class CommentService
{
    /**
     * Create a comment for the updated asset field
     *
     * @param string $message
     * @param int $assetId
     * @param int $adminId
     * @param int $investorId
     */
    public function logChange($message, $assetId, $adminId)
    {
        Comment::create([
            'comment' => $message,
            'read' => 0, // Mark comment as unread
            'attachment' => null, // Optional: You can set an attachment path if needed
            'admin_id' => $adminId,
            'asset_id' => $assetId,
            'investor_id' => null,
        ]);
    }
}
