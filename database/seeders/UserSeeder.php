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
            'name'                 => 'Super Admin',
            'password'             => Hash::make('12345678'),
            'phone'                => '0900000001',
            'date_of_birth'        => '1980-01-01',
            'gender'               => 'male',
            'address'              => '1 Dinh Tien Hoang, Ha Noi',
            'qualification'        => 'Quan tri he thong',
            'status'               => 'active',
            'must_change_password' => false,
        ]);
        $admin->syncRoles(['admin']);

        $teachers = [   
            [
                'name'          => 'Nguyen Van An',
                'email'         => 'teacher1@tunacademy.com',
                'phone'         => '0912000001',
                'date_of_birth' => '1985-03-10',
                'gender'        => 'male',
                'address'       => '12 Pham Ngoc Thach, Ha Noi',
                'qualification' => 'Thac si Toan hoc',
            ],
            [
                'name'          => 'Tran Thi Binh',
                'email'         => 'teacher2@tunacademy.com',
                'phone'         => '0912000002',
                'date_of_birth' => '1990-07-22',
                'gender'        => 'female',
                'address'       => '45 Nguyen Trai, Ha Noi',
                'qualification' => 'Thac si Ngu van',
            ],
            [
                'name'          => 'Le Minh Chau',
                'email'         => 'teacher3@tunacademy.com',
                'phone'         => '0912000003',
                'date_of_birth' => '1988-11-05',
                'gender'        => 'female',
                'address'       => '78 Bach Mai, Ha Noi',
                'qualification' => 'Thac si Tieng Anh',
            ],
        ];
        foreach ($teachers as $data) {
            $teacher = User::updateOrCreate(['email' => $data['email']], [
                ...$data,
                'password'             => Hash::make('12345678'),
                'status'               => 'active',
                'must_change_password' => false,
            ]);
            $teacher->syncRoles(['teacher']);   
        }

        $students = [
            [
                'name'           => 'Pham Quoc Dat',
                'email'          => 'student1@tunacademy.com',
                'phone'          => '0933000001',
                'date_of_birth'  => '2010-04-18',
                'gender'         => 'male',
                'address'        => '23 Le Loi, Ha Noi',
                'parent_name'    => 'Pham Van A',
                'parent_phone'   => '0911111111',
                'parent_email'   => 'phamvana.phuhuynh@gmail.com',
                'parent_address' => '23 Le Loi, Ha Noi',
            ],
            [
                'name'           => 'Hoang Mai Lan',
                'email'          => 'student2@tunacademy.com',
                'phone'          => '0933000002',
                'date_of_birth'  => '2010-09-30',
                'gender'         => 'female',
                'address'        => '56 Tran Hung Dao, Ha Noi',
                'parent_name'    => 'Hoang Van B',
                'parent_phone'   => '0911111112',
                'parent_email'   => 'hoangvanb.phuhuynh@gmail.com',
                'parent_address' => '56 Tran Hung Dao, Ha Noi',
            ],
            [
                'name'           => 'Do Gia Huy',
                'email'          => 'student3@tunacademy.com',
                'phone'          => '0933000003',
                'date_of_birth'  => '2011-01-14',
                'gender'         => 'male',
                'address'        => '9 Kim Ma, Ha Noi',
                'parent_name'    => 'Do Van C',
                'parent_phone'   => '0911111113',
                'parent_email'   => 'dovanc.phuhuynh@gmail.com',
                'parent_address' => '9 Kim Ma, Ha Noi',
            ],
            [
                'name'           => 'Ngo Thu Ha',
                'email'          => 'student4@tunacademy.com',
                'phone'          => '0933000004',
                'date_of_birth'  => '2011-05-27',
                'gender'         => 'female',
                'address'        => '34 Doi Can, Ha Noi',
                'parent_name'    => 'Ngo Van D',
                'parent_phone'   => '0911111114',
                'parent_email'   => 'ngovand.phuhuynh@gmail.com',
                'parent_address' => '34 Doi Can, Ha Noi',
            ],
        ];
        foreach ($students as $data) {
            $student = User::updateOrCreate(['email' => $data['email']], [
                ...$data,
                'password'             => Hash::make('12345678'),
                'status'               => 'active',
                'must_change_password' => true,
            ]);
            $student->syncRoles(['student']);
        }

        // Demo accounts for thesis demonstration (Section 4.4.4)
        $demoAdmin = User::updateOrCreate(['email' => 'admin@demo.com'], [
            'name'                 => 'Admin Demo',
            'password'             => Hash::make('password'),
            'phone'                => '0900000099',
            'date_of_birth'        => '1980-01-01',
            'gender'               => 'male',
            'address'              => 'Ha Noi',
            'qualification'        => 'Quan tri he thong',
            'status'               => 'active',
            'must_change_password' => false,
        ]);
        $demoAdmin->syncRoles(['admin']);

        $demoTeacher = User::updateOrCreate(['email' => 'teacher@demo.com'], [
            'name'                 => 'Teacher Demo',
            'password'             => Hash::make('password'),
            'phone'                => '0912000099',
            'date_of_birth'        => '1985-01-01',
            'gender'               => 'male',
            'address'              => 'Ha Noi',
            'qualification'        => 'Cu nhan Su pham',
            'status'               => 'active',
            'must_change_password' => false,
        ]);
        $demoTeacher->syncRoles(['teacher']);

        $demoStudent = User::updateOrCreate(['email' => 'student@demo.com'], [
            'name'                 => 'Student Demo',
            'password'             => Hash::make('password'),
            'phone'                => '0933000099',
            'date_of_birth'        => '2010-01-01',
            'gender'               => 'male',
            'address'              => 'Ha Noi',
            'parent_name'          => 'Phu Huynh Demo',
            'parent_phone'         => '0911111199',
            'parent_email'         => 'phuhuynh.demo@gmail.com',
            'parent_address'       => 'Ha Noi',
            'status'               => 'active',
            'must_change_password' => false,
        ]);
        $demoStudent->syncRoles(['student']);
    }
}
