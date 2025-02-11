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

        $leads = $query->select(
            'name',
            'surname',
            'email',
            'phone',
            'prefix',
            'status',
            'marketing_channel',
            'admin_id',
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
                'status' => $lead->status,
                'email' => $lead->email,
                'phone' => '"' . $lead->prefix . $lead->phone . '"',
                'marketing_channel' => $lead->marketing_channel,
                'manager' => $manager,
                'comments' => $comments,
            ];
        });

        return new Collection($leads->toArray());
    }

    public function headings(): array
    {
        return [
            'Name',
            'Status',
            'Email',
            'Phone',
            'Marketing Channel',
            'Manager',
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
