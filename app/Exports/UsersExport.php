<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private string $role = 'student') {}

    public function query()
    {
        return User::role($this->role)->with('roles')->orderBy('name');
    }

    public function headings(): array
    {
        return ['ID', 'Họ tên', 'Email', 'Số điện thoại', 'Giới tính', 'Trạng thái', 'Ngày tạo'];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? '',
            $user->gender ?? '',
            $user->status,
            $user->created_at->format('d/m/Y'),
        ];
    }
}
