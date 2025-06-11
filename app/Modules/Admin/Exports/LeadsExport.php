<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Models\LeadComment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LeadsExport implements FromCollection, WithHeadings, WithEvents
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Lead::query();

        if (auth()->user()->getRolesNameAttribute() != 'administrator') {
            $query->where('admin_id', auth()->user()->getAuthIdentifier());
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
            $managerUser = Admin::where('name', $managerNamesArray[0])
                ->where('surname', $managerNamesArray[1])->first();
            $query->where('admin_id', '=', $managerUser->id);
        }

        if (!empty($this->filters['marketing_channel']) && $this->filters['marketing_channel'] != 'all') {
            $query->where('marketing_channel', '=', $this->filters['marketing_channel']);
        }


        if (!empty($this->filters['status']) && $this->filters['status'] === 'archieve') {
            $query->where('status', 'archieve');
        }
        elseif (!empty($this->filters['status']) && $this->filters['status'] === 'active') {
            $query->where('status', '!=', 'archieve');
        }

        if (!empty($this->filters['communication_status']) && $this->filters['communication_status'] != 'all') {
            $query->where('status', '=', $this->filters['communication_status']);
        }

        if (!empty($this->filters['search']) && $this->filters['search'] != 'all') {
            $query->whereRaw("CONCAT(name, ' ', surname) LIKE ?", ['%' . $this->filters['search'] . '%']);
        }

        $leads = $query->select(
            'name',
            'surname',
            'email',
            'phone',
            'prefix',
            'status',
            'marketing_channel',
            'admin_id',
            'created_at',
            'id'
        )->get();

        $leads->transform(function ($lead) {
            $manager = '';
            if($lead->admin_id){
                $managerObj = Admin::where('id', $lead->admin_id)->first();
                if($managerObj){
                    $manager = $managerObj->name . ' ' . $managerObj->surname;
                }
            }

            $commentsObj = LeadComment::where('lead_id', $lead->id)
                ->orderByDesc('id')
                ->get();

            $commentLines = [];
            foreach ($commentsObj as $comment) {
                $formattedDate = $comment->created_at->format('d/m/Y');
                $commentLines[] = $formattedDate . ': ' . $comment->comment;
            }

            $comments = implode("\n", $commentLines);

            return [
                'name' => $lead->name . ' ' . $lead->surname,
                'email' => $lead->email,
                'phone' => '"' . $lead->prefix . $lead->phone . '"',
                'manager' => $manager,
                'status' => $lead->status,
                'created_at' => $lead->created_at->toDateString(),
                'marketing_channel' => $lead->marketing_channel,
                'comments' => $comments,
            ];
        });

        return new Collection($leads->toArray());
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Manager',
            'Status',
            'Created At',
            'Marketing Channel',
            'Comments'
        ];
    }

    /**
     * Register the events.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach ($sheet->getColumnIterator() as $column) {
                    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }

                $sheet->getStyle('G')->getAlignment()->setWrapText(true);

            },
        ];
    }
}
