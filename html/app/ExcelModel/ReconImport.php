<?php

namespace App\ExcelModel;

use App\Recon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ReconImport implements ToModel     {
    // $table->id();
    // $table->dateTime('date')->nullable();
    // $table->string('invoice_no', 15)->nullable();
    // $table->string('channel', 25)->nullable();
    // $table->string('status', 10)->nullable();
    // $table->timestamps();
    public function model(array $row)
    {
        return new Recon([
            'date' => $row['Date'],
            'invoice_no' => $row['Invoice'],
            'channel' => $row['Channel'],
            'status' => $row['Status']
        ]);
    }
}