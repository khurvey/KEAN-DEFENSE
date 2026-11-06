<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // VIEW PENDING FACULTY REGISTRATIONS
    public function pendingFaculty(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Access denied.',
            ], 403);
        }

        $faculty = User::where('role', 'faculty')
            ->where('approval_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'department', 'created_at']);

        return response()->json($faculty);
    }

    // APPROVE OR REJECT FACULTY
    public function reviewFaculty(Request $request, int $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Access denied.',
            ], 403);
        }

        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $faculty = User::where('role', 'faculty')
            ->where('approval_status', 'pending')
            ->find($id);

        if (!$faculty) {
            return response()->json([
                'message' => 'Pending faculty account not found.',
            ], 404);
        }

        $faculty->approval_status = $data['status'];
        $faculty->save();

        // Prevent rejected accounts from using existing sessions.
        if ($data['status'] === 'rejected') {
            $faculty->tokens()->delete();
        }

        return response()->json([
            'message' => 'Faculty account ' . $data['status'] . ' successfully.',
        ]);
    }
}
