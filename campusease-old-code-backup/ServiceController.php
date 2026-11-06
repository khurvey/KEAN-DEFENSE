<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        $services = $query->orderBy('name')->get();

        return response()->json([
            'message' => 'Services retrieved successfully',
            'data' => $services
        ]);
    }
}