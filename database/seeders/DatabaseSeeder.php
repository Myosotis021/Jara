<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
            ]
        );

        $workspace = Workspace::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Tugas Kuliah Semester 4',
            ],
            [
                'description' => 'Pengelolaan tugas mingguan dan praktikum semester 4',
            ]
        );

        Task::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'title' => 'Membuat Laporan Praktikum Modul 1',
            ],
            [
                'user_id' => $user->id,
                'description' => 'Laporan akhir praktikum modul 1 beserta lampiran pdf & zip',
                'priority' => 'penting',
                'is_completed' => false,
            ]
        );

        Task::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'title' => 'Menyusun Slides Presentasi Proyek',
            ],
            [
                'user_id' => $user->id,
                'description' => 'Bahan tayang untuk diskusi kelompok',
                'priority' => 'menyusul',
                'is_completed' => false,
            ]
        );
    }
}
