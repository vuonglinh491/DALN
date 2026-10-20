<?php

namespace App\Http\Controllers;

use App\Models\Download;
use Illuminate\Http\Request;

class DownloadHistoryController extends Controller
{
    public function index(Request $request)
    {
        $downloads = Download::where('user_id', $request->user()->id)->with('document')->latest()->paginate(15);

        return view('downloads.index', compact('downloads'));
    }
}
