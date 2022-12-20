<?php

namespace App\Http\Controllers;

use App\Booking;
use App\ExcelModel\ReconImport;
use App\Exports\ReconsExport;
use App\Imports\ReconsImport;
use App\PaymentCart;
use App\Recon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReconController extends Controller
{
    public function storeData(Request $request)
    {
        $file = $request->file('doku_file');
        // storage_path('recon/KA-oct.xlsx')
        $dokuRecons = Excel::import(new ReconsImport, $file, null, \Maatwebsite\Excel\Excel::XLSX);
        // dd($dokuRecons);
        
        return response()->json($dokuRecons);
    }

    public function reporting(Request $request)
    {
        $recons = Recon::with('paymentRequest')->skip(8000)->take(4000)->get();
        $report = [];
        $row = 0;
        foreach ($recons as $item) {
            $invoiceNo = $item->invoice_no;
            if ($row == 0) {
                array_push($report, ['id','invoice no','booking code','paid status','transaction amount','transaction tax','transaction comission amount','transaction comission by','awb']);
            }
            // error_log(json_encode($item));
            // error_log(json_encode($item->paymentRequest['booking_id']));
            if (is_null($item->paymentRequest['cart_ids'])) {
                $booking = Booking::with(['payment:booking_id,paid,transaction_amount,transaction_tax,transaction_comission_amount,transaction_comission_by','shipment:booking_id,awb'])->select(['id','booking_code'])->where('id', $item->paymentRequest['booking_id'])->first();
                if (!is_null($booking)) {
                    $form_report = [
                        $booking->id, $invoiceNo, $booking->booking_code, $booking->payment->paid, $booking->payment->transaction_amount, $booking->payment->transaction_tax, $booking->payment->transaction_comission_amount, $booking->payment->transaction_comission_by, $booking->shipment->awb
                    ];
                    array_push($report, $form_report);
                } else {
                    array_push($report, [
                        '000', $item->invoice_no
                    ]);
                }
            } else {
                $paymentCart = PaymentCart::with(['booking:id,booking_code',
                                                    'booking.payment:booking_id,paid,transaction_amount,transaction_tax,transaction_comission_amount,transaction_comission_by',
                                                    'booking.shipment:booking_id,awb'])->whereIn('id', json_decode($item->paymentRequest['cart_ids']))->get();

                if (!is_null($paymentCart)) {
                    foreach ($paymentCart as $item) {
                        $form_report = [
                            $item->booking->id, $invoiceNo, $item->booking->booking_code, $item->booking->payment->paid, $item->booking->payment->transaction_amount, $item->booking->payment->transaction_tax, $item->booking->payment->transaction_comission_amount, $item->booking->payment->transaction_comission_by, $item->booking->shipment->awb
                        ];
                        array_push($report, $form_report);
                    }
                }
            }
            $row++;
        }

        $export = new ReconsExport([$report]);
        return Excel::download($export, 'recons_'.time().'.xlsx');
        // return response()->json($report, 200);
    }
}
