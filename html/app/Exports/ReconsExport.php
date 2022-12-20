<?php

namespace App\Exports;

use App\Booking;
use App\PaymentCart;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;

class ReconsExport implements FromArray
{
    protected $recons;

    public function __construct(array $recons)
    {
        $this->recons = $recons;
    }

    public function array(): array
    {
        return $this->recons;
    }
}
