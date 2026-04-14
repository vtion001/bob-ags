<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // DEV USERS
            [
                'name' => 'Dev Admin',
                'email' => 'dev.admin@bob-ags.local',
                'password' => Hash::make('DevAdmin123!'),
                'role' => 'admin',
                'ctm_agent_id' => null,
            ],
            [
                'name' => 'Dev QA',
                'email' => 'dev.qa@bob-ags.local',
                'password' => Hash::make('DevQA123!'),
                'role' => 'qa',
                'ctm_agent_id' => null,
            ],
            [
                'name' => 'Dev Agent',
                'email' => 'dev.agent@bob-ags.local',
                'password' => Hash::make('DevAgent123!'),
                'role' => 'viewer',
                'ctm_agent_id' => 'DEV001',
            ],
            // TEST USERS
            [
                'name' => 'Test Admin',
                'email' => 'test.admin@bob-ags.local',
                'password' => Hash::make('TestAdmin123!'),
                'role' => 'admin',
                'ctm_agent_id' => null,
            ],
            [
                'name' => 'Test QA',
                'email' => 'test.qa@bob-ags.local',
                'password' => Hash::make('TestQA123!'),
                'role' => 'qa',
                'ctm_agent_id' => null,
            ],
            [
                'name' => 'Test Agent',
                'email' => 'test.agent@bob-ags.local',
                'password' => Hash::make('TestAgent123!'),
                'role' => 'viewer',
                'ctm_agent_id' => 'TEST001',
            ],
        ];

        foreach ($users as $userData) {
            $existing = User::where('email', $userData['email'])->first();
            if ($existing) {
                $existing->update($userData);
            } else {
                User::create($userData);
            }
        }
    }
}
