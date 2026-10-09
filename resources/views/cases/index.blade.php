@extends('layouts.app', ['title' => 'Case Directory'])

@section('content')
<div style="margin-bottom: 28px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="margin-bottom: 6px;">Medical Implant Cases</h1>
        <p style="color: var(--color-muted);">Browse, search, and manage patient-specific implant cases across clinical statuses.</p>
    </div>

    @if(Auth::user()->isAdmin() || Auth::user()->isEngineer())
        <a href="{{ route('cases.create') }}" class="btn btn-primary" id="btn-create-case">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Create New Case</span>
        </a>
    @endif
</div>

<!-- Search, Filter & Sort Controls Card -->
<div class="card" style="padding: 20px 24px; margin-bottom: 24px;">
    <form method="GET" action="{{ route('cases.index') }}" id="filter-form" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        
        <!-- Live Search Input Pill -->
        <div class="search-pill-container" style="flex: 1; min-width: 260px;">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input
                type="text"
                name="search"
                id="input-search"
                class="search-input-pill"
                placeholder="Search by Case Number or Surgeon..."
                value="{{ $search }}"
            >
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <!-- Filter by Status -->
            <select name="status" id="filter-status" class="form-control" style="width: auto; height: 44px; padding: 8px 16px; border-radius: var(--radius-pill); font-size: 13px;">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>

            <!-- Filter by Priority (Bonus) -->
            <select name="priority" id="filter-priority" class="form-control" style="width: auto; height: 44px; padding: 8px 16px; border-radius: var(--radius-pill); font-size: 13px;">
                <option value="">All Priorities</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority }}" {{ $selectedPriority === $priority ? 'selected' : '' }}>
                        {{ $priority }} Priority
                    </option>
                @endforeach
            </select>

            <!-- Column Sorting (Bonus) -->
            <select name="sort_by" id="sort-by" class="form-control" style="width: auto; height: 44px; padding: 8px 16px; border-radius: var(--radius-pill); font-size: 13px;">
                <option value="id" {{ $sortBy === 'id' ? 'selected' : '' }}>Sort by: Newest First</option>
                <option value="case_number" {{ $sortBy === 'case_number' ? 'selected' : '' }}>Sort by: Case Number</option>
                <option value="surgery_date" {{ $sortBy === 'surgery_date' ? 'selected' : '' }}>Sort by: Surgery Date</option>
                <option value="status" {{ $sortBy === 'status' ? 'selected' : '' }}>Sort by: Status</option>
                <option value="priority" {{ $sortBy === 'priority' ? 'selected' : '' }}>Sort by: Priority</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" id="btn-apply-filter" style="height: 44px; padding: 0 20px;">
                Filter
            </button>

            @if($search || $selectedStatus || $selectedPriority || $sortBy !== 'id')
                <a href="{{ route('cases.index') }}" class="btn btn-secondary btn-sm" style="height: 44px; padding: 0 16px;" title="Reset filters">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Cases Table Card -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" id="cases-data-table">
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Patient Reference</th>
                    <th>Surgeon</th>
                    <th>Implant Type</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Surgery Date</th>
                    <th>Created By</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cases as $case)
                    <tr id="case-row-{{ $case->id }}">
                        <td>
                            <a href="{{ route('cases.show', $case) }}" class="mono" style="font-weight: 600; color: var(--color-primary); text-decoration: none;">
                                {{ $case->case_number }}
                            </a>
                        </td>
                        <td class="mono" style="font-size: 13px; color: var(--color-body);">
                            {{ $case->patient_reference }}
                        </td>
                        <td style="font-weight: 500;">
                            {{ $case->surgeon_name }}
                        </td>
                        <td style="color: var(--color-body); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $case->implant_type }}
                        </td>
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
                        <td style="font-size: 13px; color: var(--color-muted);">
                            {{ $case->creator->name ?? 'System' }}
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <!-- View button (Shown to all roles) -->
                                <a href="{{ route('cases.show', $case) }}" class="btn btn-secondary btn-sm" id="btn-view-case-{{ $case->id }}" style="height: 32px; padding: 0 12px; font-size: 12px;">
                                    View
                                </a>

                                <!-- Edit button (Shown to Admin and Engineer only) -->
                                @if(Auth::user()->isAdmin() || Auth::user()->isEngineer())
                                    <a href="{{ route('cases.edit', $case) }}" class="btn btn-secondary btn-sm" id="btn-edit-case-{{ $case->id }}" style="height: 32px; padding: 0 12px; font-size: 12px;">
                                        Edit
                                    </a>
                                @endif

                                <!-- Delete button (Shown ONLY to Admin) -->
                                @if(Auth::user()->isAdmin())
                                    <form method="POST" action="{{ route('cases.destroy', $case) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete case {{ $case->case_number }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" id="btn-delete-case-{{ $case->id }}" style="height: 32px; padding: 0 10px; font-size: 12px;">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 48px; color: var(--color-muted);">
                            <div style="font-size: 16px; font-weight: 500; margin-bottom: 6px;">No cases found</div>
                            <p style="font-size: 13px;">Try adjusting your search keywords or filter criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination (10 per page) -->
    @if($cases->hasPages())
        <div style="padding: 20px 24px; border-top: 1px solid var(--color-hairline); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 13px; color: var(--color-muted);">
                Showing <strong>{{ $cases->firstItem() }}</strong> to <strong>{{ $cases->lastItem() }}</strong> of <strong>{{ $cases->total() }}</strong> total cases
            </div>
            <div>
                {{ $cases->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
