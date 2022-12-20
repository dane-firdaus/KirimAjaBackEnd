<?php

namespace App\ExcelModel;

use App\MasterDistrictPrice;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MstDistrictPriceImport implements ToModel, WithHeadingRow, WithBatchInserts {
    public function model(array $row)
    {
        return new MasterDistrictPrice([
            'origin' => $row['Origin'],
            'destination' => $row['Destination'],
            'price' => $row['HARGA']
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
}