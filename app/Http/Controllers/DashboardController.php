<?php

namespace App\Http\Controllers;

use App\Models\MedicalCase;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the clinical case dashboard with KPI counts.
     */
    public function index(): View
    {
        $metrics = [
            'total' => MedicalCase::count(),
            'planning' => MedicalCase::where('status', 'Planning')->count(),
            'in_review' => MedicalCase::where('status', 'In Review')->count(),
            'approved' => MedicalCase::where('status', 'Approved')->count(),
            'completed' => MedicalCase::where('status', 'Completed')->count(),
            'cancelled' => MedicalCase::where('status', 'Cancelled')->count(),
        ];

        $recentCases = MedicalCase::with('creator')
            ->latest('id')
            ->take(5)
            ->get();

        return view('dashboard', [
            'metrics' => $metrics,
            'recentCases' => $recentCases,
        ]);
    }
}
