<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@tunacademy.com'], [
            'name'                     => 'Super Admin',
            'password'                 => Hash::make('12345678'),
            'phone'                    => '0900000001',
            'gender'                   => 'male',
            'address'                  => 'Ha Noi',
            'qualification'            => 'Quan tri he thong',
            'status'                   => 'active',
            'must_change_password'     => false,
        ]);
        $admin->syncRoles(['admin']);

        $teachers = [
            ['name' => 'Nguyen Van An',  'email' => 'teacher1@tunacademy.com', 'gender' => 'male',   'qualification' => 'Thac si Toan hoc'],
            ['name' => 'Tran Thi Binh', 'email' => 'teacher2@tunacademy.com', 'gender' => 'female', 'qualification' => 'Thac si Ngu van'],
            ['name' => 'Le Minh Chau',  'email' => 'teacher3@tunacademy.com', 'gender' => 'female', 'qualification' => 'Thac si Tieng Anh'],
        ];
        foreach ($teachers as $data) {
            $teacher = User::updateOrCreate(['email' => $data['email']], [
                ...$data,
                'password'             => Hash::make('12345678'),
                'address'              => 'Ha Noi',
                'status'               => 'active',
                'must_change_password' => false,
            ]);
            $teacher->syncRoles(['teacher']);
        }

        $students = [
            ['name' => 'Pham Quoc Dat', 'email' => 'student1@tunacademy.com', 'gender' => 'male',   'parent_name' => 'Pham Van A', 'parent_phone' => '0911111111'],
            ['name' => 'Hoang Mai Lan', 'email' => 'student2@tunacademy.com', 'gender' => 'female', 'parent_name' => 'Hoang Van B', 'parent_phone' => '0911111112'],
            ['name' => 'Do Gia Huy',    'email' => 'student3@tunacademy.com', 'gender' => 'male',   'parent_name' => 'Do Van C',    'parent_phone' => '0911111113'],
            ['name' => 'Ngo Thu Ha',    'email' => 'student4@tunacademy.com', 'gender' => 'female', 'parent_name' => 'Ngo Van D',   'parent_phone' => '0911111114'],
        ];
        foreach ($students as $data) {
            $student = User::updateOrCreate(['email' => $data['email']], [
                ...$data,
                'password'             => Hash::make('12345678'),
                'address'              => 'Ha Noi',
                'parent_address'       => 'Ha Noi',
                'status'               => 'active',
                'must_change_password' => true,
            ]);
            $student->syncRoles(['student']);
        }
    }
}
