<?php

namespace App\ExcelModel;

use App\BookingDetail;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TrxBookingDetailImport implements ToModel, WithHeadingRow {
    public function model(array $row) {
        return new BookingDetail([
            'package_description' => $row['package_description'],
            'package_length' => $row['package_length'],
            'package_width' => $row['package_width'],
            'package_height' => $row['package_height'],
            'package_weight' => $row['package_weight'],
            'package_volume' => $row['package_volume'],
            'package_quantity' => $row['package_quantity']
        ]);
    }
}