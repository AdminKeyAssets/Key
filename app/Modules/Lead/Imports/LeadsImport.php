<?php

namespace App\Modules\Lead\Imports;

use App\Modules\Lead\Models\Lead;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeadsImport implements ToModel, WithHeadingRow
{
    /**
     * The default admin_id to assign if not provided in the Excel file.
     *
     * @var mixed
     */
    protected $adminId;

    /**
     * Constructor receives the current authenticated admin's ID.
     *
     * @param mixed $adminId
     */
    public function __construct($adminId)
    {
        $this->adminId = $adminId;
    }

    /**
     * Define validation rules for each row.
     *
     * The '*' prefix applies the rules to every row in the sheet.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.name'              => ['required'],
            '*.surname'           => ['required'],
            '*.phone'             => ['required'],
            '*.prefix'            => ['required'],
            '*.marketing_channel' => ['required'],
        ];
    }

    /**
     * Create a new Lead model instance for each row.
     *
     * The Excel file should have headings matching these keys:
     * name, surname, phone, email, prefix, status, admin_id, marketing_channel.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $prefix = $row['prefix'] ?? null;
        if ($prefix && substr($prefix, 0, 1) !== '+') {
            $prefix = '+' . $prefix;
        }

        return new Lead([
            'name'              => $row['name'] ?? null,
            'surname'           => $row['surname'] ?? null,
            'phone'             => $row['phone'] ?? null,
            'email'             => $row['email'] ?? null,
            'prefix'            => $prefix,
            'status'            => $row['status'] ?? 'New',
            'admin_id'          => $row['admin_id'] ?? $this->adminId,
            'marketing_channel' => $row['marketing_channel'] ?? null,
        ]);
    }
}
