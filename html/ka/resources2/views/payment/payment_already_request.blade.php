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
            background: #edf0f4;
        }
        a.button {
            font-size: 14px;
            padding: 6px 12px;
            margin-bottom: 0;
            display: inline-block;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            -ms-touch-action: manipulation;
            touch-action: manipulation;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-image: none;
            border: 1px solid transparent;
            color: #333;
            background-color: #fff;
            border-color: #ccc;
        }
        .card {
            position: relative;
            /* width: 420px; */
            background: white;
            box-shadow: 0 0 0.4rem rgba(0, 0, 0, 0.05);
            /* border-radius: 0.4rem; */
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
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title success-text">
                Sudah Mengajukan Pembayaran
            </h2>
        </div>
        <div class="card-body">
            <p class="sub-text">Anda telah mengajukan pembayaran melalui Virtual Account sebelumnya dengan detail informasi: </p>
            <h4 class="mt-0 mb-1">{{ $bank_vendor }}</h4>
            <h4 class="mt-0 mb-1">{{ $va_account }}</h4>
            <p class="sub-text">Apabila Anda sudah melakukan pembayaran dengan Virtual Account tersebut, harap menunggu. Namun apabila Anda belum berhasil melakukan pembayaran, Anda dapat mencoba mengajukan pembayaran kembali atau mengganti metode pembayaran.</p>
            {{-- <p class="sub-text"><i>*Anda dapat mengalami duplikasi pembayaran pada pesanan kiriman anda.</i></p> --}}
            <a class="button" href="{{ urldecode($route) }}">Lanjutkan</a>
        </div>
    </div>
</body>
</html>