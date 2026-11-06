<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ConsultationController extends Controller
{
    // SHOW FACULTY MEMBERS
   
        public function faculty()
        {
            $faculty = User::where('role', 'faculty')
                ->where('approval_status', 'approved')
                ->get(['id', 'name']);

            return response()->json($faculty);
        }


    // SHOW SUBJECTS OF A FACULTY MEMBER
public function subjects(int $facultyId)    {
        $subjects = DB::table('faculty_subjects')
            ->where('faculty_id', $facultyId)
            ->select('id', 'subject_name')
            ->get();

        return response()->json(['data' => $subjects]);
    }

    // SHOW MY APPOINTMENTS
    public function index(Request $request)
    {
        $user = $request->user();

        $query = DB::table('consultations as c')
            ->join('users as s', 'c.student_id', '=', 's.id')
            ->join('users as f', 'c.faculty_id', '=', 'f.id')
            ->join('faculty_subjects as fs',
                'c.faculty_subject_id', '=', 'fs.id')
            ->select(
                'c.*',
                's.name as student_name',
                'f.name as faculty_name',
                'fs.subject_name'
            );

        if ($user->role == 'student') {
            $query->where('c.student_id', $user->id);
        } elseif ($user->role == 'faculty') {
            $query->where('c.faculty_id', $user->id);
        } else {
            return response()->json([
                'message' => 'Access denied.'
            ], 403);
        }

        return response()->json([
            'data' => $query->orderByDesc('c.id')->get()
        ]);
    }

    // STUDENT BOOKS A CONSULTATION
    public function store(Request $request)
    {
        if ($request->user()->role != 'student') {
            return response()->json([
                'message' => 'Only students can book.'
            ], 403);
        }

        $data = $request->validate([
            'faculty_id' => 'required|exists:users,id',
            'faculty_subject_id' => 'required|exists:faculty_subjects,id',
            'concern' => [
                'required',
                Rule::in([
                    'Missing Project',
                    'Missing Assignment',
                    'Grade Concern',
                    'Missed Quiz / Activity',
                    'Project Consultation',
                    'Other Academic Concern',
                ]),
            ],
            'details' => 'required|string|max:2000',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => [
                'required',
                Rule::in([
                    '09:00', '10:00', '11:00',
                    '13:00', '14:00', '15:00',
                ]),
            ],
        ]);

        $faculty = User::find($data['faculty_id']);

        if (!$faculty || $faculty->role != 'faculty') {
            return response()->json([
                'message' => 'Invalid faculty member.'
            ], 422);
        }

        $subjectExists = DB::table('faculty_subjects')
            ->where('id', $data['faculty_subject_id'])
            ->where('faculty_id', $data['faculty_id'])
            ->exists();

        if (!$subjectExists) {
            return response()->json([
                'message' => 'Subject does not belong to this faculty.'
            ], 422);
        }

        $day = date('N', strtotime($data['appointment_date']));

        if ($day >= 6) {
            return response()->json([
                'message' => 'Please choose a weekday.'
            ], 422);
        }

        $occupied = Consultation::where('faculty_id', $data['faculty_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->whereTime('appointment_time', $data['appointment_time'])
            ->whereIn('status', ['Pending', 'Approved'])
            ->exists();

        if ($occupied) {
            return response()->json([
                'message' => 'This time slot is already requested.'
            ], 422);
        }

        $data['student_id'] = $request->user()->id;

        $consultation = Consultation::create($data);

        return response()->json([
            'message' => 'Appointment submitted successfully.',
            'data' => $consultation
        ], 201);
    }

    // APPROVE, REJECT, COMPLETE, OR CANCEL
public function updateStatus(Request $request, int $id)    {
        $consultation = Consultation::findOrFail($id);
        $user = $request->user();

        $data = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'Approved',
                    'Rejected',
                    'Completed',
                    'Cancelled',
                ]),
            ],
            'remarks' => 'nullable|string|max:1000',
        ]);

        $status = $data['status'];

        if ($user->role == 'student') {
            if ($consultation->student_id != $user->id ||
                $consultation->status != 'Pending' ||
                $status != 'Cancelled') {
                return response()->json([
                    'message' => 'You cannot make this change.'
                ], 403);
            }
        } elseif ($user->role == 'faculty') {
            if ($consultation->faculty_id != $user->id) {
                return response()->json([
                    'message' => 'This is not your appointment.'
                ], 403);
            }

            if ($consultation->status == 'Pending') {
                if (!in_array($status, ['Approved', 'Rejected'])) {
                    return response()->json([
                        'message' => 'Invalid status change.'
                    ], 422);
                }
            } elseif ($consultation->status == 'Approved') {
                if ($status != 'Completed') {
                    return response()->json([
                        'message' => 'Invalid status change.'
                    ], 422);
                }
            } else {
                return response()->json([
                    'message' => 'This appointment is already closed.'
                ], 422);
            }

            if ($status == 'Rejected' &&
                empty(trim($data['remarks'] ?? ''))) {
                return response()->json([
                    'message' => 'A rejection reason is required.'
                ], 422);
            }
        } else {
            return response()->json([
                'message' => 'Access denied.'
            ], 403);
        }

        $consultation->status = $status;
        $consultation->remarks = $data['remarks'] ?? null;
        $consultation->save();

        return response()->json([
            'message' => 'Appointment status updated.',
            'data' => $consultation
        ]);
    }
}