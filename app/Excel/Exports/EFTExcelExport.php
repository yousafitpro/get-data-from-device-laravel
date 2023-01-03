<?php
namespace App\Excel\Exports;
use Maatwebsite\Excel\Concerns\FromArray;
class EFTExcelExport implements FromArray
{
    protected $bills;
    public function __construct(array $bills)
    {
        $this->bills = $bills;
    }

    public function array(): array
    {
        return $this->bills;
    }
    public static function export()
    {
        $export = new EFTExcelExport([
            [1, 2, 3],
            [4, 5, 6]
        ]);

        return Excel::download($export, 'invoices.xlsx');
    }
}
