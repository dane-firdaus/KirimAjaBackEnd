<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>KirimAja Payment</title>
    <style>
        @import url("https://fonts.googleapis.com/css?family=Inria+Sans:400,700&display=swap");
        :root {
            font-family: "Inria Sans";
            font-size: 18px;
            color: #191f29;
        }
        body {
            width: 100vw;
            height: 100vh;
            display: -webkit-box;
            display: flex;
            -webkit-box-align: center;
                    align-items: center;
            -webkit-box-pack: center;
                    justify-content: center;
            margin: 0;
            background: #edf0f4;
        }
        .card {
            position: relative;
            width: 320px;
            background: white;
            box-shadow: 0 0 0.4rem rgba(0, 0, 0, 0.05);
            border-radius: 0.4rem;
            overflow: hidden;
        }
        .card .card-header .card-title {
            font-size: 1.4rem;
            margin: 0;
            padding: 0.5rem 1rem;
            padding-top: 1.2rem;
        }
        .card .card-body {
            overflow: hidden;
            padding: 0 1rem 1rem 1rem;
            -webkit-transition: 0.3s;
            transition: 0.3s;
        }
        .card .card-body .sub-text {
            font-size: 0.9rem;
            color: #646b75;
        }
        .card .card-link-footer {
            display: block;
            padding: 1rem;
            font-weight: bold;
            color: #d96c0f;
            text-align: center;
            text-decoration: none;
            -webkit-transition: 0.2s;
            transition: 0.2s;
        }
        .card .card-link-footer:hover {
            color: white;
            box-shadow: inset 0 -56px 0 #d96c0f;
            -webkit-transition: 0.2s;
            transition: 0.2s;
        }

        .mt-0 {
            margin-top: 0;
        }
        .mt-1 {
            margin-top: 0.4rem;
        }
        .mt-2 {
            margin-top: 0.8rem;
        }

        .mb-1 {
            margin-bottom: 0.4rem;
        }
        .list-item {
            list-style: none;
            padding-left: 0;
            margin: 0 -1rem;
            color: #646b75;
        }
        .list-item li {
            padding-left: 0.6rem;
        }
        .list-item li a {
            display: block;
            text-decoration: none;
            color: inherit;
            padding: 0.4rem 1rem;
            padding-left: 0.4rem;
            -webkit-transition: -webkit-transform 0.1s;
            transition: -webkit-transform 0.1s;
            transition: transform 0.1s;
            transition: transform 0.1s, -webkit-transform 0.1s;
        }
        .list-item li a:hover {
            -webkit-transform: translateX(4px);
                    transform: translateX(4px);
            -webkit-transition: -webkit-transform 0.1s;
            transition: -webkit-transform 0.1s;
            transition: transform 0.1s;
            transition: transform 0.1s, -webkit-transform 0.1s;
        }
        .list-item li a b {
            font-size: 0.8rem;
            margin-right: 4px;
        }
        .list-item li:nth-child(even) div {
            border-radius: 4px 0 0 4px;
            background: rgba(239, 241, 245, 0.9);
        }
        .success-text {
            color: #05c46b;
        }
        .failed-text {
            color: #ff5e57;
        }
        @media only screen and (min-width:800px) {
            #link-web {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title success-text">
                Menunggu Pembayaran.
            </h2>
            <a href="https://kirimaja.id/" style="display:none;float:right" id="link-web">🏠</a>
        </div>
        <div class="card-body">
            <p class="sub-text">{{ $provider ?? '' }} - Virtual Account<br>{{ $virtualNo ?? '' }}</p>
            <p class="sub-text">{{$paidDate}}<br>{{$paidAt}}</p>
            <h4 class="mt-0 mb-1">Rincian Pengiriman</h4>
            <ol class="list-item">
                <li>
                    <div>
                    <a href="#"><b>Pengirim</b></a>
                    </div>
                </li>
                <li>
                    <div>
                    <a href="#">{{ $booking->booking_origin_name }} ({{ $booking->booking_origin_phone }})</a>
                    </div>
                </li>
                <li>
                    <div>
                        <a href="#">{{ $booking->booking_origin_addr_1 }} - {{ $booking->booking_origin_city }}</a>
                    </div>
                </li>
            </ol>

            <ol class="list-item">
                <li>
                    <div>
                    <a href="#"><b>Penerima</b></a>
                    </div>
                </li>
                <li>
                    <div>
                    <a href="#">{{ $booking->booking_destination_name }} ({{ $booking->booking_destination_phone }})</a>
                    </div>
                </li>
                <li>
                    <div>
                        <a href="#">{{ $booking->booking_destination_addr_1 }} - {{ $booking->booking_destination_city }}</a>
                    </div>
                </li>
            </ol>

            <h4 class="mt-2 mb-1">Rincian Biaya</h4>
            <ol class="list-item">
                <li>
                    <div style="clear: both;">
                        <a href="#" style="float: left;">
                            <b>Biaya Kirim</b>
                        </a>
                        <a href="#" style="float: right;">
                           IDR. {{ number_format($booking->payment->transaction_amount) }}
                        </a>
                    </div>
                </li>
                <li>
                    <div style="clear: both;">
                        <a href="#" style="float: left;">
                            <b>Pajak Kirim</b>
                        </a>
                        <a href="#" style="float: right;">
                            IDR. {{ number_format($booking->payment->transaction_tax) }}
                        </a>
                    </div>
                </li>
                <li>
                    <div style="clear: both; background: #edf0f4; height:1px;">
                    </div>
                </li>
                <li>
                    <div style="clear: both;">
                        <a href="#" style="float: left;">
                            <b>Total</b>
                        </a>
                        <a href="#" style="float: right;">
                            IDR. {{ number_format($booking->payment->transaction_amount+$booking->payment->transaction_tax) }}
                        </a>
                    </div>
                </li>
            </ol>

            {{-- <h4 class="mt-2 mb-1">Rincian Barang</h4>
            <ol class="list-item">
                <li>
                    <div>
                    <a href="#">
                        Makanan<br>
                        <b>Berat Volume: 4,5 Kg</b><br>
                        <b>Berat Barang: 2 Kg</b>
                    </a>
                    </div>
                </li>
                <li>
                    <div>
                        <a href="#">
                            Dokumen<br>
                            <b>Berat Volume: 1 Kg</b><br>
                            <b>Berat Barang: 0.5 Kg</b>
                        </a>
                    </div>
                </li>
            </ol> --}}
        </div>
        {{-- <a class="card-link-footer" href="#">See more episodes</a> --}}
    </div>
</body>
</html>
