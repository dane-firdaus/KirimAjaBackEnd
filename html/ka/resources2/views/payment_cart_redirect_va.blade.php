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
    <script>
        function copyVA() {
            /* Get the text field */
            var copyText = document.getElementById('virtualAccountNo');
            var textArea = document.createElement("textarea");
            textArea.value = copyText.textContent;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("Copy");
            textArea.remove();
            document.getElementById("copyVirtualAccount").value = "Tersalin";
        }

        function copyAmount() {
            /* Get the text field */
            var copyText = document.getElementById("totalAmount");
            var textArea = document.createElement("textarea");
            textArea.value = copyText.textContent.replace(',','');
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("Copy");
            textArea.remove();
            document.getElementById("copyTotalAmount").value = "Tersalin";
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center mt-5 mb-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Informasi Pembayaran
                        <a href="https://kirimaja.id/" style="float:right">Kembali ke web</a>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title text-success">Menunggu Pembayaran</h3>
                        <p class="card-text">{{$paidDate}}<br>{{$paidAt}}</p>

                        <h5></h5>
                        <div class="card text-white bg-success mb-2">
                            <div class="card-header">Tagihan Anda Saat Ini</div>
                            <div class="card-body">
                                <h4 class="card-title">{{ $provider ?? '' }} - Virtual Account<br><span id="virtualAccountNo">{{ $virtualNo ?? '' }}</span></h4>
                                <button id="copyVirtualAccount" type="button" class="btn btn-outline-light btn-sm" onclick="copyVA()">Salin Virtual Account</button>
                                <p class="card-text mt-2">
                                    <h3>IDR <span id="totalAmount">{{ number_format($amount) }}</span></h3>
                                    <button id="copyTotalAmount" type="button" class="btn btn-outline-light btn-sm" onclick="copyAmount()">Salin Tagihan</button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</body>

</html>
