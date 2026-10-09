<?php

namespace App\Models;

use Database\Factories\MedicalCaseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalCase extends Model
{
    /** @use HasFactory<MedicalCaseFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cases';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'case_number',
        'patient_reference',
        'surgeon_name',
        'implant_type',
        'status',
        'priority',
        'surgery_date',
        'created_by',
    ];

    /**
     * Allowed case lifecycle status values.
     *
     * @var list<string>
     */
    public const STATUSES = [
        'Draft',
        'Planning',
        'In Review',
        'Approved',
        'Completed',
        'Cancelled',
    ];

    /**
     * Allowed priority levels.
     *
     * @var list<string>
     */
    public const PRIORITIES = [
        'Low',
        'Medium',
        'High',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'surgery_date' => 'date',
        ];
    }

    /**
     * Get the user who created this case.
     *
     * @return BelongsTo<User, MedicalCase>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Filter cases by search term (Case Number or Surgeon Name).
     *
     * @param  Builder<MedicalCase>  $query
     * @return Builder<MedicalCase>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($term) {
            $subQuery->where('case_number', 'like', "%{$term}%")
                ->orWhere('surgeon_name', 'like', "%{$term}%");
        });
    }

    /**
     * Filter cases by status.
     *
     * @param  Builder<MedicalCase>  $query
     * @return Builder<MedicalCase>
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Filter cases by priority (Bonus feature).
     *
     * @param  Builder<MedicalCase>  $query
     * @return Builder<MedicalCase>
     */
    public function scopePriority(Builder $query, ?string $priority): Builder
    {
        if (blank($priority)) {
            return $query;
        }

        return $query->where('priority', $priority);
    }
}
