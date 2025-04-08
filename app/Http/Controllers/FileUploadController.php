<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');

        $response = Http::attach(
            'file',
            fopen($file->getRealPath(), 'r'),
            $file->getClientOriginalName()
        )->post('https://n8n.redalerts.tn/webhook/upload-file');

        return response()->json([
            'message' => 'Upload sent to n8n',
            'n8n_response' => $response->json()
        ]);
    }
}
