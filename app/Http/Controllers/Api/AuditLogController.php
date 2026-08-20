<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        return response()->json(
            AuditLog::with('user:id,name,email')->latest()->paginate(20)
        );
    }
}