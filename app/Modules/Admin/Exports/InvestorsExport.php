<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class InvestorsExport implements FromCollection, WithHeadings, WithEvents
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
        $query = Investor::query();

        if (auth()->user()->getRolesNameAttribute() != 'administrator') {
            $query->where('admin_id', auth()->user()->getAuthIdentifier());
        }
        if (!empty($this->filters['citizenship'])) {
            $query->where('citizenship', '=', $this->filters['citizenship']);
        }
        if (!empty($this->filters['assets'])) {
            $assetsCount = $this->filters['assets'];
            $query->withCount('assets')
                ->having('assets_count', '=', $assetsCount);
        }

        if (!empty($this->filters['create_date'])) {
            $createdDates = explode(',', $this->filters['create_date']);
            if (isset($createdDates[0])) {
                $query->where('created_at', '>=', $createdDates[0]);
            }
            if (isset($createdDates[1])) {
                $query->where('created_at', '<=', $createdDates[1]);
            }
        }

        if (!empty($this->filters['manager']) && $this->filters['manager'] != 'all') {
            $managerNamesArray = explode(' ', $this->filters['manager']);

            $managerFirstName = array_shift($managerNamesArray);

            $managerSurname = implode(' ', $managerNamesArray);

            $managerUser = Admin::where('name', $managerFirstName)
                ->where('surname', $managerSurname)->
                first();
            if (isset($managerUser->id)) {
                $query->where('admin_id', $managerUser->id);
            }
        }

        if (!empty($this->filters['search']) && $this->filters['search'] != 'all') {
            $query->whereRaw("CONCAT(name, ' ', surname) LIKE ?", ['%' . $this->filters['search'] . '%']);
        }

        $investors = $query->select('name', 'surname', 'pid', 'email', 'prefix', 'phone', 'citizenship', 'address', 'created_at')->get();

        $investors->transform(function ($investor) {
            return [
                'name' => $investor->name . ' ' . $investor->surname,
                'id' => '"' . $investor->pid . '"',
                'citizenship' => $investor->citizenship,
                'address' => $investor->address,
                'email' => $investor->email,
                'phone' => '"' . $investor->prefix . $investor->phone . '"',
                'created_at' => $investor->created_at,
            ];
        });

        return new Collection($investors->toArray());
    }

    public function headings(): array
    {
        return [
            'Name',
            'ID',
            'Citizenship',
            'Address',
            'Email',
            'Phone',
            'Created At',
        ];
    }

    /**
     * Automatically adjust column width after the sheet is generated.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set auto-size for all columns
                foreach ($sheet->getColumnIterator() as $column) {
                    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }
            },
        ];
    }
}
