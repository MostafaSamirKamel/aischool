<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        $headings = [
            'الاسم',
            'البريد الإلكتروني',
            'الصلاحيات',
            'مدير نظام',
            'البريد مؤكد',
            'تاريخ التسجيل',
        ];

        if (auth()->user()?->is_super_admin) {
            array_unshift($headings, 'الفرع');
        }

        return $headings;
    }

    public function map($user): array
    {
        $roles = $user->roles->pluck('name')->map(function($role) {
            return match($role) {
                'super_admin' => 'مدير النظام',
                'panel_user' => 'مستخدم',
                default => $role,
            };
        })->join(', ');

        $data = [
            $user->name,
            $user->email,
            $roles ?: '-',
            $user->is_super_admin ? 'نعم' : 'لا',
            $user->email_verified_at ? 'نعم' : 'لا',
            $user->created_at->format('Y-m-d H:i'),
        ];

        if (auth()->user()?->is_super_admin) {
            array_unshift($data, $user->tenant?->name ?? '-');
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 30,
            'D' => 20,
            'E' => 15,
            'F' => 15,
            'G' => 20,
        ];
    }
}
