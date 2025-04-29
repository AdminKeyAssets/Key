<?php

namespace App\Modules\Lead\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SampleLeadsExport implements FromCollection, WithHeadings, WithEvents
{
    /**
     * Returns the headings for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'name',
            'surname',
            'prefix',
            'phone',
            'email',
            'marketing_channel',
        ];
    }

    /**
     * Returns a collection with sample data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $sample = [
            [
                'name'              => "John",
                'surname'           => "Doe",
                'prefix'            => "+995",
                'phone'             => "1234567890",
                'email'             => "john.doe@example.com",
                'marketing_channel' => "Facebook",
            ]
        ];

        return new Collection($sample);
    }

    /**
     * Register events to auto-size columns and apply wrap text on a valid range.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto-size columns A to F, as these are the valid columns in this export.
                foreach (range('A', 'F') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }

                // If you need wrap text, apply it to a valid cell range. For example, apply to all data cells.
                $dimension = $sheet->calculateWorksheetDimension(); // e.g., "A1:F2"
                $sheet->getStyle($dimension)->getAlignment()->setWrapText(true);
            },
        ];
    }
}
