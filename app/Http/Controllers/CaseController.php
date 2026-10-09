<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Models\MedicalCase;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CaseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of medical cases with pagination, search, filter, and sorting.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MedicalCase::class);

        $search = $request->query('search');
        $status = $request->query('status');
        $priority = $request->query('priority');
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('sort_dir', 'desc');

        $allowedSorts = ['case_number', 'patient_reference', 'surgeon_name', 'status', 'priority', 'surgery_date', 'id', 'created_at'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $cases = MedicalCase::with('creator')
            ->search($search)
            ->status($status)
            ->priority($priority)
            ->orderBy($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return view('cases.index', [
            'cases' => $cases,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedPriority' => $priority,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'statuses' => MedicalCase::STATUSES,
            'priorities' => MedicalCase::PRIORITIES,
        ]);
    }

    /**
     * Show the form for creating a new medical case.
     */
    public function create(): View
    {
        $this->authorize('create', MedicalCase::class);

        return view('cases.form', [
            'case' => new MedicalCase,
            'isEdit' => false,
            'statuses' => MedicalCase::STATUSES,
            'priorities' => MedicalCase::PRIORITIES,
        ]);
    }

    /**
     * Store a newly created medical case in storage.
     */
    public function store(StoreCaseRequest $request): RedirectResponse
    {
        $this->authorize('create', MedicalCase::class);

        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;

        $case = MedicalCase::create($validated);

        return redirect()
            ->route('cases.show', $case)
            ->with('success', "Medical case {$case->case_number} created successfully.");
    }

    /**
     * Display the specified medical case.
     */
    public function show(MedicalCase $case): View
    {
        $this->authorize('view', $case);

        $case->load('creator');

        $holidayNotice = null;
        if ($case->surgery_date) {
            $holidayNotice = $this->checkPublicHoliday($case->surgery_date);
        }

        return view('cases.show', [
            'case' => $case,
            'holidayNotice' => $holidayNotice,
        ]);
    }

    /**
     * Show the form for editing the specified medical case.
     */
    public function edit(MedicalCase $case): View
    {
        $this->authorize('update', $case);

        return view('cases.form', [
            'case' => $case,
            'isEdit' => true,
            'statuses' => MedicalCase::STATUSES,
            'priorities' => MedicalCase::PRIORITIES,
        ]);
    }

    /**
     * Update the specified medical case in storage.
     */
    public function update(UpdateCaseRequest $request, MedicalCase $case): RedirectResponse
    {
        $this->authorize('update', $case);

        $case->update($request->validated());

        return redirect()
            ->route('cases.show', $case)
            ->with('success', "Medical case {$case->case_number} updated successfully.");
    }

    /**
     * Remove the specified medical case from storage.
     * Strictly restricted to Admin users via CasePolicy.
     */
    public function destroy(MedicalCase $case): RedirectResponse
    {
        $this->authorize('delete', $case);

        $caseNumber = $case->case_number;
        $case->delete();

        return redirect()
            ->route('cases.index')
            ->with('success', "Medical case {$caseNumber} has been deleted.");
    }

    /**
     * Check if a surgery date falls on a public holiday (Bonus Task - Nager.Date API).
     */
    protected function checkPublicHoliday(Carbon|\DateTimeInterface|string $date): ?string
    {
        try {
            $carbonDate = Carbon::parse($date);
            $year = $carbonDate->year;
            $country = config('services.nager_date.country', 'US');
            $apiUrl = config('services.nager_date.url', "https://date.nager.at/api/v3/PublicHolidays/{$year}/{$country}");

            $response = Http::timeout(3)->get($apiUrl);

            if ($response->successful()) {
                $holidays = $response->json();
                $dateString = $carbonDate->format('Y-m-d');

                foreach ($holidays as $holiday) {
                    if (isset($holiday['date']) && $holiday['date'] === $dateString) {
                        return "Public Holiday Alert: {$holiday['name']} ({$holiday['localName']}) falls on this surgery date.";
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Nager.Date public holiday lookup skipped: '.$e->getMessage());
        }

        return null;
    }
}
