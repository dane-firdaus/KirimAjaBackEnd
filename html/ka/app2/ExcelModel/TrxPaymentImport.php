<?php

namespace App\ExcelModel;

use App\Payment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TrxPaymentImport implements ToModel, WithHeadingRow {
    public function model(array $row)
    {
        return new Payment([
            'user_id' => $row['user_id'],
            'booking_id' => $row['booking_id'],
            'transaction_amount' => $row['transaction_amount'],
            'transaction_tax' => $row['transaction_tax'],
            'transaction_comission_amount' => $row['transaction_comission_amount'],
            'transaction_comission_by' => $row['transaction_comission_by'],
            'transaction_id' => $row['transaction_id'],
            'paid' => $row['paid'],
            'paid_at' => $row['paid_at'],
            'paid_channel' => $row['paid_channel'],
            'paid_response' => $row['paid_response'],
            'payment_proof' => $row['payment_proof'],
        ]);
    }
}