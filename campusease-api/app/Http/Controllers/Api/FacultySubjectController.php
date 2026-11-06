<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacultySubjectController extends Controller
{
    // CHECK FACULTY ACCOUNT
    private function allowed(Request $request)
    {
        return $request->user()->role === 'faculty'
            && $request->user()->approval_status === 'approved';
    }

    // VIEW MY SUBJECTS
    public function index(Request $request)
    {
        if (!$this->allowed($request)) {
            return response()->json([
                'message' => 'Faculty access only.'
            ], 403);
        }

        $subjects = DB::table('faculty_subjects')
            ->where('faculty_id', $request->user()->id)
            ->orderBy('subject_name')
            ->get(['id', 'subject_name']);

        return response()->json($subjects);
    }

    // ADD SUBJECT
    public function store(Request $request)
    {
        if (!$this->allowed($request)) {
            return response()->json([
                'message' => 'Faculty access only.'
            ], 403);
        }

        $data = $request->validate([
            'subject_name' => 'required|string|max:100',
        ]);

        $name = trim($data['subject_name']);
        $facultyId = $request->user()->id;

        $exists = DB::table('faculty_subjects')
            ->where('faculty_id', $facultyId)
            ->where('subject_name', $name)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Subject already exists.'
            ], 422);
        }

        DB::table('faculty_subjects')->insert([
            'faculty_id' => $facultyId,
            'subject_name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Subject added successfully!'
        ], 201);
    }

    // EDIT SUBJECT
    public function update(Request $request, int $id)
    {
        if (!$this->allowed($request)) {
            return response()->json([
                'message' => 'Faculty access only.'
            ], 403);
        }

        $data = $request->validate([
            'subject_name' => 'required|string|max:100',
        ]);

        $facultyId = $request->user()->id;
        $name = trim($data['subject_name']);

        $subject = DB::table('faculty_subjects')
            ->where('id', $id)
            ->where('faculty_id', $facultyId)
            ->first();

        if (!$subject) {
            return response()->json([
                'message' => 'Subject not found.'
            ], 404);
        }

        $exists = DB::table('faculty_subjects')
            ->where('faculty_id', $facultyId)
            ->where('subject_name', $name)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Subject already exists.'
            ], 422);
        }

        DB::table('faculty_subjects')
            ->where('id', $id)
            ->where('faculty_id', $facultyId)
            ->update([
                'subject_name' => $name,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Subject updated successfully!'
        ]);
    }

    // DELETE SUBJECT
    public function destroy(Request $request, int $id)
    {
        if (!$this->allowed($request)) {
            return response()->json([
                'message' => 'Faculty access only.'
            ], 403);
        }

        return DB::transaction(function () use ($request, $id) {
            $facultyId = $request->user()->id;

            // Lock faculty row to protect the 3-subject rule.
            DB::table('users')
                ->where('id', $facultyId)
                ->lockForUpdate()
                ->first();

            $subject = DB::table('faculty_subjects')
                ->where('id', $id)
                ->where('faculty_id', $facultyId)
                ->first();

            if (!$subject) {
                return response()->json([
                    'message' => 'Subject not found.'
                ], 404);
            }

            $count = DB::table('faculty_subjects')
                ->where('faculty_id', $facultyId)
                ->count();

            if ($count <= 3) {
                return response()->json([
                    'message' => 'Faculty must keep at least 3 subjects.'
                ], 422);
            }

            $used = DB::table('consultations')
                ->where('faculty_subject_id', $id)
                ->exists();

            if ($used) {
                return response()->json([
                    'message' =>
                        'Cannot delete a subject with consultation records.'
                ], 422);
            }

            DB::table('faculty_subjects')
                ->where('id', $id)
                ->where('faculty_id', $facultyId)
                ->delete();

            return response()->json([
                'message' => 'Subject deleted successfully!'
            ]);
        });
    }
}
