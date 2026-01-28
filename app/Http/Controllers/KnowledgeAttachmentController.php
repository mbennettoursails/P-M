<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KnowledgeAttachmentController extends Controller
{
    /**
     * Download an attachment
     */
    public function download(KnowledgeAttachment $attachment): StreamedResponse
    {
        // Increment download count
        $attachment->incrementDownloadCount();

        // Stream the file download
        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->filename,
            [
                'Content-Type' => $attachment->mime_type,
            ]
        );
    }
}
