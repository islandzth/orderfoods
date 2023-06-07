<?php

namespace App\Exports;

//use App\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Mon an',
            'So luong order',
            'So luong bep don',
            'SL da ra ban',
            'Tgian order',
            'Ban',
        ];
    }
}
