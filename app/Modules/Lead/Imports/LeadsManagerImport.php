<?php

namespace App\Modules\Lead\Imports;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Lead\Models\Lead;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeadsManagerImport implements ToModel, WithHeadingRow
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
            '*.name' => ['required'],
            '*.surname' => ['required'],
            '*.manager_name' => ['required'],
            '*.manager_surname' => ['required'],
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
        $manager = Admin::where('name', $row['manager_name'])
            ->where('surname', $row['manager_surname'])
            ->first();

        Lead::where('name', $row['name'])
            ->where('surname', $row['surname'])
            ->update(['admin_id' => $manager ? $manager->id : null]);

        return null;
    }
}
