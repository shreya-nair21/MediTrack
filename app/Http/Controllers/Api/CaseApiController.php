<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Models\MedicalCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
    public function store(StoreCaseRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
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
    public function update(UpdateCaseRequest $request, int|string $id): JsonResponse
    {
        $case = MedicalCase::find($id);

        if (! $case) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Medical case not found.',
            ], 404);
        }

        $case->update($request->validated());
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
