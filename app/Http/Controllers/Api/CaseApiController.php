<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CaseApiController extends Controller
{
    /**
     * Display a paginated listing of cases.
     * Allowed: Admin, Viewer, Engineer.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! Gate::forUser($user)->allows('viewAny', MedicalCase::class)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $search = $request->query('search');
        $status = $request->query('status');
        $priority = $request->query('priority');

        $cases = MedicalCase::with('creator:id,name,email,role')
            ->search($search)
            ->status($status)
            ->priority($priority)
            ->latest('id')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $cases,
            'message' => 'Medical cases retrieved successfully.',
        ], 200);
    }

    /**
     * Create a new medical case.
     * Allowed: Admin, Engineer.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! Gate::forUser($user)->allows('create', MedicalCase::class)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Unauthorized: Only Admins (and Engineers) can create cases.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'case_number' => ['required', 'string', 'max:50', 'unique:cases,case_number'],
            'patient_reference' => ['required', 'string', 'max:100'],
            'surgeon_name' => ['required', 'string', 'max:150'],
            'implant_type' => ['required', 'string', 'max:200'],
            'status' => ['required', 'string', Rule::in(MedicalCase::STATUSES)],
            'priority' => ['required', 'string', Rule::in(MedicalCase::PRIORITIES)],
            'surgery_date' => ['nullable', 'date'],
        ], [
            'case_number.unique' => 'The Case Number must be unique.',
            'status.in' => 'Status must be one of: '.implode(', ', MedicalCase::STATUSES),
            'priority.in' => 'Priority must be one of: '.implode(', ', MedicalCase::PRIORITIES),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => $validator->errors(),
                ],
                'message' => 'Validation error.',
            ], 422);
        }

        $data = $validator->validated();
        $data['created_by'] = $user->id;

        $case = MedicalCase::create($data);
        $case->load('creator:id,name,email,role');

        return response()->json([
            'success' => true,
            'data' => $case,
            'message' => 'Medical case created successfully.',
        ], 201);
    }

    /**
     * Get details of a single medical case.
     * Allowed: Admin, Viewer, Engineer.
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $case = MedicalCase::with('creator:id,name,email,role')->find($id);

        if (! $case) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Medical case not found.',
            ], 404);
        }

        $user = $request->user();
        if (! Gate::forUser($user)->allows('view', $case)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $case,
            'message' => 'Medical case retrieved successfully.',
        ], 200);
    }

    /**
     * Update an existing medical case.
     * Allowed: Admin, Engineer.
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $case = MedicalCase::find($id);

        if (! $case) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Medical case not found.',
            ], 404);
        }

        $user = $request->user();
        if (! Gate::forUser($user)->allows('update', $case)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Unauthorized: Only Admins (and Engineers) can update cases.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'case_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cases', 'case_number')->ignore($case->id),
            ],
            'patient_reference' => ['required', 'string', 'max:100'],
            'surgeon_name' => ['required', 'string', 'max:150'],
            'implant_type' => ['required', 'string', 'max:200'],
            'status' => ['required', 'string', Rule::in(MedicalCase::STATUSES)],
            'priority' => ['required', 'string', Rule::in(MedicalCase::PRIORITIES)],
            'surgery_date' => ['nullable', 'date'],
        ], [
            'case_number.unique' => 'The Case Number must be unique.',
            'status.in' => 'Status must be one of: '.implode(', ', MedicalCase::STATUSES),
            'priority.in' => 'Priority must be one of: '.implode(', ', MedicalCase::PRIORITIES),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => $validator->errors(),
                ],
                'message' => 'Validation error.',
            ], 422);
        }

        $case->update($validator->validated());
        $case->load('creator:id,name,email,role');

        return response()->json([
            'success' => true,
            'data' => $case,
            'message' => 'Medical case updated successfully.',
        ], 200);
    }

    /**
     * Delete a medical case.
     * Strictly restricted to Admin users.
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $case = MedicalCase::find($id);

        if (! $case) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Medical case not found.',
            ], 404);
        }

        $user = $request->user();
        if (! Gate::forUser($user)->allows('delete', $case)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Unauthorized: Only Admins can delete cases.',
            ], 403);
        }

        $case->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Medical case deleted successfully.',
        ], 200);
    }
}
