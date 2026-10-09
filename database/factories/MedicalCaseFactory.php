<?php

namespace Database\Factories;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalCase>
 */
class MedicalCaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<MedicalCase>
     */
    protected $model = MedicalCase::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $implantTypes = [
            'Cranial Titanium Mesh 3D',
            'Acetabular Hip Cup System',
            'Spinal Fixation Rod Assembly',
            'Patient-Specific Mandible Plate',
            'Bicondylar Knee Femoral Tray',
            'Custom PEEK Cranial Flap',
            'Titanium Interbody Fusion Cage',
            'Sternum Reconstruction Plate',
            'Maxillofacial Reconstruction Lattice',
            'Radius Osteotomy Fixator',
        ];

        static $sequence = 1;

        return [
            'case_number' => sprintf('CASE-%04d', $sequence++),
            'patient_reference' => 'PT-'.fake()->unique()->numerify('####').'-'.fake()->lexify('??'),
            'surgeon_name' => 'Dr. '.fake()->firstName().' '.fake()->lastName(),
            'implant_type' => fake()->randomElement($implantTypes),
            'status' => fake()->randomElement(MedicalCase::STATUSES),
            'priority' => fake()->randomElement(MedicalCase::PRIORITIES),
            'surgery_date' => fake()->boolean(80) ? fake()->dateTimeBetween('-1 month', '+3 months')->format('Y-m-d') : null,
            'created_by' => User::factory(),
        ];
    }
}
