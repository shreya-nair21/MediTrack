<?php

namespace Database\Seeders;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@jajalmedical.com'],
            [
                'name' => 'Dr. Admin Case Lead',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
            ]
        );

        $viewer = User::firstOrCreate(
            ['email' => 'viewer@jajalmedical.com'],
            [
                'name' => 'Clinical Case Reviewer',
                'password' => Hash::make('Password123!'),
                'role' => 'viewer',
            ]
        );

        $engineer = User::firstOrCreate(
            ['email' => 'engineer@jajalmedical.com'],
            [
                'name' => 'Biomedical CAD Engineer',
                'password' => Hash::make('Password123!'),
                'role' => 'engineer',
            ]
        );

        $implantCatalog = [
            ['implant' => 'Cranial Titanium Mesh 3D', 'surgeon' => 'Dr. Elizabeth Hayes', 'priority' => 'High', 'status' => 'Planning'],
            ['implant' => 'Acetabular Hip Cup System', 'surgeon' => 'Dr. Marcus Vance', 'priority' => 'Medium', 'status' => 'In Review'],
            ['implant' => 'Spinal Fixation Rod Assembly', 'surgeon' => 'Dr. Sarah Jenkins', 'priority' => 'High', 'status' => 'Approved'],
            ['implant' => 'Patient-Specific Mandible Plate', 'surgeon' => 'Dr. Anthony Patel', 'priority' => 'Low', 'status' => 'Draft'],
            ['implant' => 'Bicondylar Knee Femoral Tray', 'surgeon' => 'Dr. Helena Gomez', 'priority' => 'Medium', 'status' => 'Completed'],
            ['implant' => 'Custom PEEK Cranial Flap', 'surgeon' => 'Dr. Robert Chen', 'priority' => 'High', 'status' => 'Planning'],
            ['implant' => 'Titanium Interbody Fusion Cage', 'surgeon' => 'Dr. Sarah Jenkins', 'priority' => 'Medium', 'status' => 'Approved'],
            ['implant' => 'Sternum Reconstruction Plate', 'surgeon' => 'Dr. Elizabeth Hayes', 'priority' => 'Low', 'status' => 'Cancelled'],
            ['implant' => 'Maxillofacial Reconstruction Lattice', 'surgeon' => 'Dr. Anthony Patel', 'priority' => 'High', 'status' => 'In Review'],
            ['implant' => 'Radius Osteotomy Fixator', 'surgeon' => 'Dr. Marcus Vance', 'priority' => 'Low', 'status' => 'Completed'],
            ['implant' => 'Custom PEEK Orbital Floor', 'surgeon' => 'Dr. Robert Chen', 'priority' => 'Medium', 'status' => 'Planning'],
            ['implant' => 'Porous Tantalum Acetabular Wedge', 'surgeon' => 'Dr. Marcus Vance', 'priority' => 'High', 'status' => 'Approved'],
            ['implant' => 'Sacroiliac Joint Fusion Implant', 'surgeon' => 'Dr. Sarah Jenkins', 'priority' => 'Low', 'status' => 'Draft'],
            ['implant' => 'Scapula Custom Reconstruction Unit', 'surgeon' => 'Dr. Elizabeth Hayes', 'priority' => 'High', 'status' => 'In Review'],
            ['implant' => 'Subperiosteal Jaw Implant Matrix', 'surgeon' => 'Dr. Anthony Patel', 'priority' => 'Medium', 'status' => 'Completed'],
            ['implant' => 'Total Talus Replacement Assembly', 'surgeon' => 'Dr. Helena Gomez', 'priority' => 'Medium', 'status' => 'Planning'],
            ['implant' => 'Cervical Spine Spacer Plate', 'surgeon' => 'Dr. Sarah Jenkins', 'priority' => 'High', 'status' => 'Approved'],
            ['implant' => 'Custom Rib Cage Bridging Plate', 'surgeon' => 'Dr. Elizabeth Hayes', 'priority' => 'Low', 'status' => 'Draft'],
            ['implant' => 'Metacarpal Fracture Locking Mesh', 'surgeon' => 'Dr. Marcus Vance', 'priority' => 'Low', 'status' => 'Completed'],
            ['implant' => 'Patellar Resurfacing Component', 'surgeon' => 'Dr. Helena Gomez', 'priority' => 'Medium', 'status' => 'In Review'],
            ['implant' => 'Zygomatic Complex Trauma Plate', 'surgeon' => 'Dr. Anthony Patel', 'priority' => 'High', 'status' => 'Planning'],
            ['implant' => 'Thoracolumbar Deformity Rod', 'surgeon' => 'Dr. Sarah Jenkins', 'priority' => 'High', 'status' => 'Approved'],
        ];

        $users = [$admin, $engineer];

        foreach ($implantCatalog as $index => $item) {
            $creator = $users[$index % count($users)];
            $caseNumber = sprintf('CASE-%04d', $index + 1);

            MedicalCase::updateOrCreate(
                ['case_number' => $caseNumber],
                [
                    'patient_reference' => sprintf('PT-%04d-%s', 1000 + $index, strtoupper(substr(md5((string) $index), 0, 2))),
                    'surgeon_name' => $item['surgeon'],
                    'implant_type' => $item['implant'],
                    'status' => $item['status'],
                    'priority' => $item['priority'],
                    'surgery_date' => now()->addDays(($index - 5) * 3)->format('Y-m-d'),
                    'created_by' => $creator->id,
                ]
            );
        }
    }
}
