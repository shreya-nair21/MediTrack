@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="margin-bottom: 6px;">Clinical Case Dashboard</h1>
        <p style="color: var(--color-muted);">Real-time monitoring of medical implant cases from draft to surgical completion.</p>
    </div>

    <div style="display: flex; gap: 12px;">
        <a href="{{ route('cases.index') }}" class="btn btn-secondary" id="btn-view-all-cases">
            View All Cases
        </a>
        @if(Auth::user()->isAdmin() || Auth::user()->isEngineer())
            <a href="{{ route('cases.create') }}" class="btn btn-primary" id="btn-dashboard-new-case">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>New Case</span>
            </a>
        @endif
    </div>
</div>

<!-- Metric KPI Cards per specification -->
<div class="metric-grid">
    <!-- Total Cases -->
    <a href="{{ route('cases.index') }}" style="text-decoration: none; color: inherit;">
        <div class="metric-card" id="card-metric-total">
            <div class="metric-label">Total Cases</div>
            <div class="metric-value">{{ $metrics['total'] }}</div>
            <div style="margin-top: 10px; font-size: 12px; color: var(--color-muted);">All logged implant cases</div>
        </div>
    </a>

    <!-- Planning -->
    <a href="{{ route('cases.index', ['status' => 'Planning']) }}" style="text-decoration: none; color: inherit;">
        <div class="metric-card" id="card-metric-planning">
            <div class="metric-label">Planning</div>
            <div class="metric-value" style="color: #b07e00;">{{ $metrics['planning'] }}</div>
            <div style="margin-top: 10px; font-size: 12px; color: var(--color-muted);">Surgical planning & CAD</div>
        </div>
    </a>

    <!-- In Review -->
    <a href="{{ route('cases.index', ['status' => 'In Review']) }}" style="text-decoration: none; color: inherit;">
        <div class="metric-card" id="card-metric-review">
            <div class="metric-label">In Review</div>
            <div class="metric-value" style="color: #1971c2;">{{ $metrics['in_review'] }}</div>
            <div style="margin-top: 10px; font-size: 12px; color: var(--color-muted);">Clinical peer review</div>
        </div>
    </a>

    <!-- Approved -->
    <a href="{{ route('cases.index', ['status' => 'Approved']) }}" style="text-decoration: none; color: inherit;">
        <div class="metric-card" id="card-metric-approved">
            <div class="metric-label">Approved</div>
            <div class="metric-value" style="color: #2b8a3e;">{{ $metrics['approved'] }}</div>
            <div style="margin-top: 10px; font-size: 12px; color: var(--color-muted);">Cleared for surgery</div>
        </div>
    </a>

    <!-- Completed -->
    <a href="{{ route('cases.index', ['status' => 'Completed']) }}" style="text-decoration: none; color: inherit;">
        <div class="metric-card" id="card-metric-completed">
            <div class="metric-label">Completed</div>
            <div class="metric-value" style="color: #087f5b;">{{ $metrics['completed'] }}</div>
            <div style="margin-top: 10px; font-size: 12px; color: var(--color-muted);">Surgery finalized</div>
        </div>
    </a>
</div>

<!-- Recent Cases Section -->
<div class="card" style="margin-top: 12px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2>Recently Updated Cases</h2>
            <p style="color: var(--color-muted); font-size: 13px; margin-top: 2px;">Latest patient-specific implant cases entering the system.</p>
        </div>
        <a href="{{ route('cases.index') }}" class="btn btn-secondary btn-sm">Explore All &rarr;</a>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="table-recent-cases">
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Patient Ref</th>
                    <th>Surgeon</th>
                    <th>Implant Type</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Surgery Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentCases as $case)
                    <tr>
                        <td>
                            <a href="{{ route('cases.show', $case) }}" class="mono" style="font-weight: 600; color: var(--color-primary); text-decoration: none;">
                                {{ $case->case_number }}
                            </a>
                        </td>
                        <td class="mono" style="font-size: 13px; color: var(--color-body);">{{ $case->patient_reference }}</td>
                        <td style="font-weight: 500;">{{ $case->surgeon_name }}</td>
                        <td style="color: var(--color-body);">{{ $case->implant_type }}</td>
                        <td>
                            <span class="badge badge-status-{{ Str::slug($case->status) }}">
                                {{ $case->status }}
                            </span>
                        </td>
                        <td>
                            <span class="priority-{{ strtolower($case->priority) }}">
                                &bull; {{ $case->priority }}
                            </span>
                        </td>
                        <td class="mono" style="font-size: 13px;">
                            {{ $case->surgery_date ? $case->surgery_date->format('M d, Y') : '—' }}
                        </td>
                        <td>
                            <a href="{{ route('cases.show', $case) }}" class="btn btn-secondary btn-sm" style="height: 32px; padding: 0 12px; font-size: 12px;">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--color-muted);">
                            No medical cases found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
