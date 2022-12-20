<?php

namespace App\Imports;

use App\Recon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
class ReconsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Recon([
            'date' => $row['date'],
            'invoice_no' => $row['invoice'],
            'channel' => $row['channel'],
            'status' => $row['status'],
        ]);
    }
}
