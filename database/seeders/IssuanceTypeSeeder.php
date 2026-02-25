<?php
// database/seeders/IssuanceTypeSeeder.php

namespace Database\Seeders;

use App\Models\IssuanceType;
use App\Models\Department;
use Illuminate\Database\Seeder;

class IssuanceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['New Hire', 'Salary Deduct', 'Annual', 'Additional'];

        foreach (Department::all() as $department) {
            foreach ($types as $name) {
                IssuanceType::firstOrCreate([
                    'department_id' => $department->id,
                    'name'          => $name,
                ]);
            }
        }
    }
}