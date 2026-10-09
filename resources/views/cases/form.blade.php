@extends('layouts.app', ['title' => $isEdit ? 'Edit Case: ' . $case->case_number : 'Create New Implant Case'])

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ $isEdit ? route('cases.show', $case) : route('cases.index') }}" style="color: var(--color-muted); text-decoration: none; font-size: 13px; font-weight: 500;">
            &larr; Back to {{ $isEdit ? 'Case Details' : 'Cases Directory' }}
        </a>
        <h1 style="margin-top: 8px;">{{ $isEdit ? 'Edit Case: ' . $case->case_number : 'New Medical Implant Case' }}</h1>
        <p style="color: var(--color-muted);">{{ $isEdit ? 'Update case clinical details, status, or surgery date.' : 'Log a new patient-specific implant case into the tracking registry.' }}</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ $isEdit ? route('cases.update', $case) : route('cases.store') }}" id="case-form">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Case Number -->
                <div class="form-group">
                    <label for="case_number" class="form-label">
                        Case Number <span style="color: var(--color-semantic-down);">*</span>
                    </label>
                    <input
                        type="text"
                        name="case_number"
                        id="case_number"
                        class="form-control mono {{ $errors->has('case_number') ? 'is-invalid' : '' }}"
                        value="{{ old('case_number', $case->case_number ?? 'CASE-' . sprintf('%04d', rand(100, 9999))) }}"
                        placeholder="e.g. CASE-0001"
                        required
                    >
                    @error('case_number')
                        <span class="error-message" id="error-case_number">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Patient Reference -->
                <div class="form-group">
                    <label for="patient_reference" class="form-label">
                        Patient Reference Code <span style="color: var(--color-semantic-down);">*</span>
                    </label>
                    <input
                        type="text"
                        name="patient_reference"
                        id="patient_reference"
                        class="form-control mono {{ $errors->has('patient_reference') ? 'is-invalid' : '' }}"
                        value="{{ old('patient_reference', $case->patient_reference) }}"
                        placeholder="e.g. PT-4821-XA (dummy code)"
                        required
                    >
                    @error('patient_reference')
                        <span class="error-message" id="error-patient_reference">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Surgeon Name -->
                <div class="form-group">
                    <label for="surgeon_name" class="form-label">
                        Surgeon Name <span style="color: var(--color-semantic-down);">*</span>
                    </label>
                    <input
                        type="text"
                        name="surgeon_name"
                        id="surgeon_name"
                        class="form-control {{ $errors->has('surgeon_name') ? 'is-invalid' : '' }}"
                        value="{{ old('surgeon_name', $case->surgeon_name) }}"
                        placeholder="e.g. Dr. Sarah Jenkins"
                        required
                    >
                    @error('surgeon_name')
                        <span class="error-message" id="error-surgeon_name">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Implant Type -->
                <div class="form-group">
                    <label for="implant_type" class="form-label">
                        Implant Type / Model <span style="color: var(--color-semantic-down);">*</span>
                    </label>
                    <input
                        type="text"
                        name="implant_type"
                        id="implant_type"
                        class="form-control {{ $errors->has('implant_type') ? 'is-invalid' : '' }}"
                        value="{{ old('implant_type', $case->implant_type) }}"
                        placeholder="e.g. Cranial Titanium Mesh 3D"
                        required
                    >
                    @error('implant_type')
                        <span class="error-message" id="error-implant_type">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <!-- Status -->
                <div class="form-group">
                    <label for="status" class="form-label">
                        Lifecycle Status <span style="color: var(--color-semantic-down);">*</span>
                    </label>
                    <select
                        name="status"
                        id="status"
                        class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}"
                        required
                    >
                        @foreach($statuses as $statusOption)
                            <option value="{{ $statusOption }}" {{ old('status', $case->status ?? 'Draft') === $statusOption ? 'selected' : '' }}>
                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <span class="error-message" id="error-status">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Priority -->
                <div class="form-group">
                    <label for="priority" class="form-label">
                        Clinical Priority <span style="color: var(--color-semantic-down);">*</span>
                    </label>
                    <select
                        name="priority"
                        id="priority"
                        class="form-control {{ $errors->has('priority') ? 'is-invalid' : '' }}"
                        required
                    >
                        @foreach($priorities as $priorityOption)
                            <option value="{{ $priorityOption }}" {{ old('priority', $case->priority ?? 'Medium') === $priorityOption ? 'selected' : '' }}>
                                {{ $priorityOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority')
                        <span class="error-message" id="error-priority">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Surgery Date -->
                <div class="form-group">
                    <label for="surgery_date" class="form-label">
                        Surgery Date <span style="font-size: 11px; color: var(--color-muted);">(Optional)</span>
                    </label>
                    <input
                        type="date"
                        name="surgery_date"
                        id="surgery_date"
                        class="form-control mono {{ $errors->has('surgery_date') ? 'is-invalid' : '' }}"
                        value="{{ old('surgery_date', $case->surgery_date ? $case->surgery_date->format('Y-m-d') : '') }}"
                    >
                    @error('surgery_date')
                        <span class="error-message" id="error-surgery_date">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Action buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--color-hairline);">
                <a href="{{ $isEdit ? route('cases.show', $case) : route('cases.index') }}" class="btn btn-secondary" id="btn-cancel-form">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="btn-submit-case">
                    {{ $isEdit ? 'Save Changes' : 'Create Medical Case' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
