<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class ResultsReportExport implements WithHeadings, WithEvents, ShouldAutoSize
{
    protected $data;
    protected $gradeLevel;

    public function __construct(array $data, int $gradeLevel)
    {
        $this->data = $data;
        $this->gradeLevel = $gradeLevel;
    }

    public function headings(): array
    {
        return [
            ['LIVINGSTONE DISTRICT EDUCATION BOARD'],
            ['GRADE ' . $this->gradeLevel . ' RESULTS ANALYSIS FORMAT - ' . date('Y')],
            [],
            ['CENTRE NAME', '', 'CENTRE CODE', '30015'],
            [],
            [
                'TOTAL REGISTERED', '', '', 'TOTAL SAT', '', '', 'TOTAL ABSENT', '', '',
                'CERTIFICATE', '', '', 'STATEMENT', '', '', 'FAIL', '', '',
                'PASS PERCENTAGE', '', '', 'OVERALL PASS PERCENTAGE'
            ],
            [
                'B', 'G', 'TOTAL', 'B', 'G', 'TOTAL', 'B', 'G', 'TOTAL',
                'B', 'G', 'TOTAL', 'B', 'G', 'TOTAL', 'B', 'G', 'TOTAL',
                'B', 'G', 'TOTAL', 'TOTAL'
            ]
        ];
    }

    public function collection(): Collection
    {
        return collect([]);
    }

    // Inside app/Exports/ResultsReportExport.php

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Merging and Styling (no changes here)
                $sheet->mergeCells('A1:Y1'); $sheet->mergeCells('A2:Y2');
                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('A4:B4'); $sheet->mergeCells('C4:D4');
                $sheet->getStyle('A4:D4')->getFont()->setBold(true);
                $sheet->mergeCells('A6:C6'); $sheet->mergeCells('D6:F6'); $sheet->mergeCells('G6:I6');
                $sheet->mergeCells('J6:L6'); $sheet->mergeCells('M6:O6'); $sheet->mergeCells('P6:R6');
                $sheet->mergeCells('S6:U6'); $sheet->mergeCells('V6:Y6');
                $sheet->getStyle('A6:Y7')->getFont()->setBold(true);
                $sheet->getStyle('A6:Y7')->getAlignment()->setHorizontal('center');

                // --- DATA INSERTION (UPDATED FOR QUALITATIVE PASS) ---
                $qualPass = $this->data['QUALITATIVE PASS (%)'];
                
                $dataRow = [
                    $this->data['TOTAL REGISTERED']['B'], $this->data['TOTAL REGISTERED']['G'], $this->data['TOTAL REGISTERED']['TOTAL'],
                    $this->data['TOTAL SAT']['B'], $this->data['TOTAL SAT']['G'], $this->data['TOTAL SAT']['TOTAL'],
                    $this->data['TOTAL ABSENT']['B'], $this->data['TOTAL ABSENT']['G'], $this->data['TOTAL ABSENT']['TOTAL'],
                    $this->data['CERTIFICATE']['B'], $this->data['CERTIFICATE']['G'], $this->data['CERTIFICATE']['TOTAL'],
                    $this->data['STATEMENT']['B'], $this->data['STATEMENT']['G'], $this->data['STATEMENT']['TOTAL'],
                    $this->data['FAIL']['B'], $this->data['FAIL']['G'], $this->data['FAIL']['TOTAL'],
                    $qualPass['B'], $qualPass['G'], $qualPass['TOTAL'], // Pass Percentage
                    $qualPass['TOTAL'] // Overall Pass Percentage
                ];
                
                $sheet->fromArray([$dataRow], NULL, 'A8');
                $sheet->getStyle('A6:Y8')->getBorders()->getAllBorders()->setBorderStyle('thin');
            },
        ];
    }
}