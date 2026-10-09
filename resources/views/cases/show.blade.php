@extends('layouts.app', ['title' => 'Case ' . $case->case_number])

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <!-- Breadcrumb & Top Bar -->
    <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
        <div>
            <a href="{{ route('cases.index') }}" style="color: var(--color-muted); text-decoration: none; font-size: 13px; font-weight: 500;">
                &larr; Back to Cases Directory
            </a>
            <div style="display: flex; align-items: center; gap: 12px; margin-top: 8px;">
                <h1 class="mono" style="margin: 0; font-size: 30px;">{{ $case->case_number }}</h1>
                <span class="badge badge-status-{{ Str::slug($case->status) }}" style="font-size: 14px; padding: 6px 14px;">
                    {{ $case->status }}
                </span>
            </div>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            @if(Auth::user()->isAdmin() || Auth::user()->isEngineer())
                <a href="{{ route('cases.edit', $case) }}" class="btn btn-secondary" id="btn-edit-case">
                    Edit Case
                </a>
            @endif

            @if(Auth::user()->isAdmin())
                <form method="POST" action="{{ route('cases.destroy', $case) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to permanently delete case {{ $case->case_number }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="btn-delete-case">
                        Delete Case
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Public Holiday Alert (Bonus Task) -->
    @if($holidayNotice)
        <div class="alert alert-success" style="background-color: #fff9db; color: #8f6b00; border: 1px solid #ffe066; margin-bottom: 24px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <div>
                <strong>Notice:</strong> {{ $holidayNotice }}
                <div style="font-size: 12px; opacity: 0.85;">Hospital surgical theater schedules may be affected by holiday staffing.</div>
            </div>
        </div>
    @endif

    <!-- Main Case Details Card -->
    <div class="card" style="margin-bottom: 24px;">
        <h2 style="font-size: 18px; margin-bottom: 20px; border-bottom: 1px solid var(--color-hairline); padding-bottom: 12px;">
            Clinical Case Specification
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
            <div>
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 4px;">
                    Patient Reference Code
                </div>
                <div class="mono" style="font-size: 16px; font-weight: 600; color: var(--color-ink);">
                    {{ $case->patient_reference }}
                </div>
                <div style="font-size: 11px; color: var(--color-muted);">Pseudo-anonymized identifier</div>
            </div>

            <div>
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 4px;">
                    Surgeon Name
                </div>
                <div style="font-size: 16px; font-weight: 600; color: var(--color-ink);">
                    {{ $case->surgeon_name }}
                </div>
                <div style="font-size: 11px; color: var(--color-muted);">Primary Lead Clinician</div>
            </div>

            <div>
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 4px;">
                    Implant Type / Model
                </div>
                <div style="font-size: 16px; font-weight: 600; color: var(--color-ink);">
                    {{ $case->implant_type }}
                </div>
                <div style="font-size: 11px; color: var(--color-muted);">Patient-specific device hardware</div>
            </div>

            <div>
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 4px;">
                    Clinical Priority
                </div>
                <div>
                    <span class="priority-{{ strtolower($case->priority) }}" style="font-size: 16px;">
                        &bull; {{ $case->priority }} Priority
                    </span>
                </div>
            </div>

            <div>
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 4px;">
                    Scheduled Surgery Date
                </div>
                <div class="mono" style="font-size: 16px; font-weight: 600; color: var(--color-ink);">
                    {{ $case->surgery_date ? $case->surgery_date->format('l, F j, Y') : 'Not scheduled yet' }}
                </div>
            </div>

            <div>
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-muted); margin-bottom: 4px;">
                    Lifecycle State
                </div>
                <div>
                    <span class="badge badge-status-{{ Str::slug($case->status) }}">
                        {{ $case->status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit & Traceability Box -->
    <div class="card" style="background-color: var(--color-surface-soft);">
        <h2 style="font-size: 15px; margin-bottom: 16px; color: var(--color-ink);">
            Audit Trail &amp; Regulatory Traceability
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 13px;">
            <div>
                <span style="color: var(--color-muted);">Created By:</span><br>
                <strong>{{ $case->creator->name ?? 'System' }}</strong> ({{ $case->creator->email ?? 'N/A' }})
                <br>
                <span class="badge badge-role-{{ $case->creator->role ?? 'viewer' }}" style="margin-top: 4px;">
                    {{ ucfirst($case->creator->role ?? 'user') }}
                </span>
            </div>
            <div>
                <span style="color: var(--color-muted);">Created At:</span><br>
                <span class="mono">{{ $case->created_at->format('Y-m-d H:i:s T') }}</span>
                <br>
                <span style="color: var(--color-muted); font-size: 12px;">({{ $case->created_at->diffForHumans() }})</span>
            </div>
            <div>
                <span style="color: var(--color-muted);">Last Updated At:</span><br>
                <span class="mono">{{ $case->updated_at->format('Y-m-d H:i:s T') }}</span>
                <br>
                <span style="color: var(--color-muted); font-size: 12px;">({{ $case->updated_at->diffForHumans() }})</span>
            </div>
        </div>
    </div>
</div>
@endsection
