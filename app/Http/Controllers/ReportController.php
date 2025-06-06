<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
class ReportController extends Controller
{
    /**
     * Display a listing of reports.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::with(['reporter', 'reportedUser']);


        if ($request->has('status')) {
            $query->where('status', $request->status);
        }


        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }


        if ($request->has('reported_user_id')) {
            $query->where('reported_user_id', $request->reported_user_id);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(15);

        return Response::json($reports);
    }

    /**
     * Store a newly created report in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reporter_id' => 'required|exists:users,id',
            'reported_user_id' => 'required|exists:users,id',
            'entity_type' => 'required|string',
            'entity_id' => 'required|string',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 400);
        }

        $report = Report::create([
            'reporter_id' => $request->reporter_id,
            'reported_user_id' => $request->reported_user_id,
            'entity_type' => $request->entity_type,
            'entity_id' => $request->entity_id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return Response::json(['message' => 'Report submitted successfully', 'data' => $report], 201);
    }

    /**
     * Display the specified report.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $report = Report::with(['reporter', 'reportedUser'])->findOrFail($id);
        return Response::json($report);
    }

    /**
     * Update the specified report in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,reviewed,rejected,resolved',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 400);
        }

        $report = Report::findOrFail($id);
        $report->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return Response::json(['message' => 'Report updated successfully', 'data' => $report]);
    }
}