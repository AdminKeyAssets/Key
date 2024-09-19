<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Admin;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class AdminsExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Admin::query();

        if (!empty($this->filters['email'])) {
            $query->where('email', 'like', '%' . $this->filters['email'] . '%');
        }

        if (!empty($this->filters['phone'])) {
            $query->where('phone', 'like', '%' . $this->filters['phone'] . '%');
        }

        $admins = $query->select('name', 'surname', 'pid', 'email', 'prefix', 'phone', 'created_at')->get();

        $admins->transform(function ($admin) {
            return [
                'name' => $admin->name . ' ' . $admin->surname,
                'id' => $admin->pid,  // Keep the PID as a text value
                'email' => $admin->email,
                'full_phone' => $admin->prefix . $admin->phone, // Concatenate prefix and phone
                'created_at' => $admin->created_at,
            ];
        });

        return new Collection($admins->toArray());
    }

    public function headings(): array
    {
        return [
            'Name',
            'ID',
            'Email',
            'Phone',
            'Created At',
        ];
    }

    /**
     * Format specific columns.
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Name
            'B' => NumberFormat::FORMAT_TEXT, // ID (PID)
            'C' => NumberFormat::FORMAT_TEXT, // Email
            'D' => NumberFormat::FORMAT_TEXT, // Phone
            'E' => NumberFormat::FORMAT_DATE_DATETIME, // Created At
        ];
    }
}
