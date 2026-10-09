<?php

namespace App\Http\Requests;

use App\Models\MedicalCase;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && ($this->user()->isAdmin() || $this->user()->isEngineer());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $caseParam = $this->route('case') ?? $this->route('id');
        $caseId = $caseParam instanceof MedicalCase ? $caseParam->id : $caseParam;

        return [
            'case_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cases', 'case_number')->ignore($caseId),
            ],
            'patient_reference' => ['required', 'string', 'max:100'],
            'surgeon_name' => ['required', 'string', 'max:150'],
            'implant_type' => ['required', 'string', 'max:200'],
            'status' => ['required', 'string', Rule::in(MedicalCase::STATUSES)],
            'priority' => ['required', 'string', Rule::in(MedicalCase::PRIORITIES)],
            'surgery_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'case_number.unique' => 'This Case Number is already assigned to another medical case.',
            'status.in' => 'Status must be one of: '.implode(', ', MedicalCase::STATUSES).'.',
            'priority.in' => 'Priority must be one of: '.implode(', ', MedicalCase::PRIORITIES).'.',
            'surgery_date.date' => 'Surgery Date must be a valid date.',
        ];
    }
}
