<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'admin',
                'guard_name' => 'api'
            ],
            [
                'name' => 'user',
                'guard_name' => 'api'
            ]
        ];

        foreach($roles as $role){
            Role::create([
                'name' => $role['name'],
                'guard_name' => $role['guard_name'],
            ]);
        }
    }
}
