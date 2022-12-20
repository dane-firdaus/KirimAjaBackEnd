<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

    <title>Informasi Pembayaran</title>
    <style>
		html {
			height: 100%;
			background-image: url('{{ Storage::url('artwork/ka-pattern.png') }}');
			/* background-position: center; */
			background-repeat: no-repeat;
			background-size: cover;
		}
        body {
            background-color: transparent;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center mt-5 mb-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Informasi Pembayaran
                    </div>
                    <div class="card-body">
                        
                        @if ($success)
                        <h3 class="card-title text-success">Pembayaran Berhasil</h3>
                        @else
                        <h3 class="card-title text-danger">Pembayaran Gagal</h3>
                        @endif

                        <p class="card-text">{{$paidDate}}<br>{{$paidAt}}</p>
                        
                        <h5>Rincian Pengiriman</h5>
                        @php
                            $amount = 0;
                            $tax = 0;
                        @endphp
                        @foreach ($bookings as $booking)
                        <div class="list-group mt-3">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{strtoupper($booking->booking_code)}}</h5>
                                </div>
                                <p class="mb-1">
                                    {{ $booking->booking_origin_name }} ({{ $booking->booking_origin_phone }})<br>
                                    {{ $booking->booking_origin_addr_1 }} - {{ $booking->booking_origin_city }}
                                </p>
                                <hr>
                                <p class="mb-1">
                                    {{ $booking->booking_destination_name }} ({{ $booking->booking_destination_phone }})<br>
                                    {{ $booking->booking_destination_addr_1 }} - {{ $booking->booking_destination_city }}
                                </p>
                            </a>
                        </div>  
                        @php
                            $amount += $booking->payment->transaction_amount;
                            $tax += $booking->payment->transaction_tax;
                        @endphp  
                        @endforeach

                        {{-- <div class="list-group mt-3">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">KODE-BOOKING</h5>
                                    
                                </div>
                                <p class="mb-1">
                                    Andri (08117777804)<br>
                                    Jalan M1 - TANGERANG KOTA, KOTA. TANGERANG, BANTEN, INDONESIA
                                </p>
                                <hr>
                                <p class="mb-1">
                                    Joko (085157810804)<br>
                                    Jalan Apa Aja - NONGSA, KOTA. BATAM, KEPULAUAN RIAU, INDONESIA
                                </p>
                            </a>
                        </div>

                        <div class="list-group mt-3">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">KODE-BOOKING</h5>
                                    
                                </div>
                                <p class="mb-1">
                                    Andri (08117777804)<br>
                                    Jalan M1 - TANGERANG KOTA, KOTA. TANGERANG, BANTEN, INDONESIA
                                </p>
                                <hr>
                                <p class="mb-1">
                                    Joko (085157810804)<br>
                                    Jalan Apa Aja - NONGSA, KOTA. BATAM, KEPULAUAN RIAU, INDONESIA
                                </p>
                            </a>
                        </div> --}}

                        <h5 class="mt-4">Rincian Biaya</h5>
                        <dl class="row">
                            <dt class="col-sm-3">Biaya Kirim</dt>
                            <dd class="col-sm-9 text-right">IDR {{ number_format($amount) }}</dd>
                            <dt class="col-sm-3">Pajak Kirim</dt>
                            <dd class="col-sm-9 text-right">IDR {{ number_format($tax) }}</dd>
                            <dt class="col-sm-3">Total</dt>
                            <dd class="col-sm-9 text-right">IDR {{ number_format($amount+$tax) }}</dd>
                        </dl>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</body>

</html>