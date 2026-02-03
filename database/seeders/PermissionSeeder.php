<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Students
            'students.view',
            'students.create',
            'students.edit',
            'students.delete',

            // Courses
            'courses.view',
            'courses.create',
            'courses.edit',
            'courses.delete',
            'courses.publish',

            // Modules
            'modules.view',
            'modules.create',
            'modules.edit',
            'modules.delete',

            // Lessons
            'lessons.view',
            'lessons.create',
            'lessons.edit',
            'lessons.delete',

            // Quizzes
            'quizzes.view',
            'quizzes.create',
            'quizzes.edit',
            'quizzes.delete',

            // Enrollments
            'enrollments.view',
            'enrollments.create',
            'enrollments.edit',
            'enrollments.delete',

            // Payments
            'payments.view',
            'payments.edit',
            'payments.refund',

            // Certificates
            'certificates.view',
            'certificates.issue',
            'certificates.revoke',

            // Categories
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            // Reviews
            'reviews.view',
            'reviews.delete',

            // Reports
            'reports.view',
            'reports.export',

            // Admins
            'admins.view',
            'admins.create',
            'admins.edit',
            'admins.delete',

            // Activity Log
            'activity-log.view',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        // Super Admin - all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin - all permissions except admins.*
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminPermissions = Permission::whereNotIn('name', [
            'admins.view',
            'admins.create',
            'admins.edit',
            'admins.delete',
        ])->get();
        $admin->syncPermissions($adminPermissions);

        // Content Manager - courses.*, modules.*, lessons.*, quizzes.*, categories.*
        $contentManager = Role::firstOrCreate(['name' => 'Content Manager', 'guard_name' => 'web']);
        $contentManagerPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', 'courses.%')
                ->orWhere('name', 'like', 'modules.%')
                ->orWhere('name', 'like', 'lessons.%')
                ->orWhere('name', 'like', 'quizzes.%')
                ->orWhere('name', 'like', 'categories.%');
        })->get();
        $contentManager->syncPermissions($contentManagerPermissions);

        // Support - view only permissions
        $support = Role::firstOrCreate(['name' => 'Support', 'guard_name' => 'web']);
        $supportPermissions = Permission::whereIn('name', [
            'students.view',
            'enrollments.view',
            'payments.view',
            'courses.view',
            'reviews.view',
        ])->get();
        $support->syncPermissions($supportPermissions);
    }
}
