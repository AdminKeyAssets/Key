<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Admin;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class AdminsExport implements FromCollection, WithHeadings
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

        $investors = $query->select('name', 'surname', 'pid', 'email', 'prefix', 'phone', 'created_at')->get();

        $investors->transform(function ($investor) {
            return [
                'name' => $investor->name . ' ' . $investor->surname,
                'pid' => $investor->pid,
                'email' => $investor->email,
                'full_phone' => $investor->prefix . $investor->phone,
                'created_at' => $investor->created_at,
            ];
        });


        return new Collection($investors->toArray());
    }

    public function headings(): array
    {
        return [
            'Name',
            'Pid',
            'Email',
            'Phone',
            'Created At',
        ];
    }
}
