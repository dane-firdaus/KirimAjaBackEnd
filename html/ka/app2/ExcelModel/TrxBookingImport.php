<?php

namespace App\ExcelModel;

use App\Booking;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TrxBookingImport implements ToModel, WithHeadingRow {
    public function model(array $row)
    {
        return new Booking([
            'user_id' => $row['user_id'],
            'booking_code' => $row['booking_code'],
            'booking_origin_name' => $row['booking_origin_name'],
            'booking_origin_addr_1' => $row['booking_origin_addr_1'],
            'booking_origin_addr_2' => $row['booking_origin_addr_2'],
            'booking_origin_addr_3' => $row['booking_origin_addr_3'],
            'booking_origin_city' => $row['booking_origin_city'],
            'booking_origin_zip' => $row['booking_origin_zip'],
            'booking_origin_contact' => $row['booking_origin_contact'],
            'booking_origin_phone' => $row['booking_origin_phone'],
            'booking_destination_name' => $row['booking_destination_name'],
            'booking_destination_addr_1' => $row['booking_destination_addr_1'],
            'booking_destination_addr_2' => $row['booking_destination_addr_2'],
            'booking_destination_addr_3' => $row['booking_destination_addr_3'],
            'booking_destination_city' => $row['booking_destination_city'],
            'booking_destination_zip' => $row['booking_destination_zip'],
            'booking_destination_contact' => $row['booking_destination_contact'],
            'booking_destination_phone' => $row['booking_destination_phone'],
            'booking_delivery_point_id' => $row['booking_delivery_point_id'],
            'valid' => $row['valid'],
        ]);
    }
}