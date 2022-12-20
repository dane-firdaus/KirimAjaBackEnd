<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Payment Option</title>
    <style>
        /* @import url('https://fonts.googleapis.com/css?family=Roboto:300,400,700'); */

        body {
            background-color: #a9a9a9;
            background-size: 100% auto;
            font-family: 'Roboto', Helvetica, sans-serif;
            letter-spacing: 0.05em;
        }

        .container {
            max-width: 350px;
            margin: 100px auto;
        }

        .card {
            background-color: #fff;
            text-transform: uppercase;
            border-radius: 4px;
            box-shadow: 0 20px 40px 0 #42472B, 0 2px 4px 0 rgba(0, 0, 0, 0.19);
        }

        a {
            text-decoration: none;
            color: #333;
        }

        .title {
            padding: 10px;
            text-align: center;
        }

        .title h1 {
            font-size: 16px;
            color: #333;
            font-weight: normal;

        }

        .item {
            border-top: solid 1.5px #f1f1f1;
            padding: 0px 20px;
            background-color: #fff;
            transition: background-color ease 240ms;
            font-size: 11px;
            padding: 18px 20px 15px 20px;

        }

        .item:hover {
            background-color: #f6f6f6;
        }

        .item-image {
            width: 40px;
            height: 30px;
            display: inline-block;
            margin-right: 10px;
            vertical-align: middle;
        }

        .item-text {
            display: inline-block;
            padding-bottom: 10px;
        }

        .item-selected {
            background-color: #f6f6f6;
            position: relative;
        }

        .button {
            padding: 20px;
            background-color: #598BDD;
            border-radius: 0px 0px 4px 4px;
            color: #fff;
            text-align: center;
            transition: background-color ease 240ms;
            font-size: 13px;
        }

        .button:hover {
            background-color: #4E7CC7;
            transform: translateY(1px);
        }

        .check {
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .arrow {
            display: inline-block;
            vertical-align: bottom;
        }

        .dots-container {
            display: block;
            margin: 10px auto;
        }

        .dots {
            width: 10px;
            height: 10px;
            display: inline-block;
            margin: 0 0.125em;
            border-radius: 50%;
            background-color: #DEDEDE;
        }

        .dot-active {
            background-color: #AAAAAA;
        }

        .item-text span {
            margin-left: 10px;
            font-size: 10px;
            color: #fff;
            background-color: #a9a9a9;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .item-text h4#copyVA {
            margin-top: 10px !important;
            padding: 10px 15px;
            font-size: 10px;
            color: #fff;
            background-color: #a9a9a9;            
            border-radius: 5px;
        }

        /* our own */
        button {
            font-size: 15px;
        }
        .mt-1 {
            margin-top: 0.4rem;
        }

        .mt-2 {
            margin-top: 0.8rem;
        }

        .item-text img {
            margin-top: 10px;
            width: 100%;
        }

        /* reset */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0px;
        }
        button.bg-gradient1 {
            margin: 0px;
            padding: 0px;
        }
        .bg-gradient1>span,
        .bg-gradient1:before {
            margin: 0px;
            background: #fa7e29;
            background: -webkit-gradient(linear, left top, left bottom, from(#83ABE0), color-stop(80%, #83ABE0), to(#83ABE0));
            background: linear-gradient(180deg, #83ABE0 0%, #83ABE0 80%, #83ABE0 100%);
        }
        .bg-gradient2>span,
        .bg-gradient2:before {
            margin: 0px;
            background: #83ABE0;
            background: -webkit-gradient(linear, left top, left bottom, from(#314251), color-stop(80%, #314251), to(#314251));
            background: linear-gradient(180deg, #314251 0%, #314251 80%, #314251 100%);
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#img-preview')
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
                $('#storeFile').show();
            }
        }

        $(document).ready(function () {
            $('#uploadFile').click(function (e) {
                e.preventDefault();
                $('#getFile').click();
            })
            // $('#storeFile').click(function (e) {
            //     e.preventDefault();
            //     $('#proofForm').submit();
            // })
            $('a#va_option').click(function (e) {
                e.preventDefault();
                $('.vitrual-account-info').show();
            })

            $('h4#copyVA').click(function (e) {
                console.log("clicked");
                
                // var copyVA = document.getElementById("va_account");
                // copyVA.select();
                // copyVA.setSelectionRange(0, 99999);

                // document.execCommand("copy");
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val("88554124242424").select();
                document.execCommand("copy");
                $temp.remove();
                $('h4#copyVA').text("Copied");
            });
        });
    </script>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="title">
                <h1>Metode Pembayaran</h1>
            </div>
            <a id="va_option" href="#">
                <div class="item">
                    <div class="item-text">
                        Virtual Account</div>
                </div>
            </a>
            <a href="#">
                <div class="item">
                    <div class="item-text">Kartu Debit atau Kredit<span>Belum Tersedia</span></div>
                </div>
            </a>

        </div>

        <div class="card mt-2 vitrual-account-info" hidden>
            <div class="title">
                <h1>Pembayaran Virtual Account</h1>
            </div>
            <a href="#">
                <div class="item">
                    <div class="item-text">
                        <h1>Bank Mandiri</h1>
                        <h1>88554124242424</h1>
                        <input id="va_account" type="hidden" name="va_account" value="88554124242424">
                        <h4 id="copyVA">Copy Virtual Account</h4>
                    </div>
                </div>
            </a>
            <a href="#">
                <div class="item">
                    <div class="item-text">
                        <form id="proofForm" action="" method="POST" enctype="multipart/form-data">
                            <!-- <button>Upload Bukti Bayar</button> -->
                            {{--  class="fancy-button pop-onhover bg-gradient1" --}}
                            {{-- class="fancy-button pop-onhover bg-gradient2" --}}
                            <button id="uploadFile">Upload Bukti Bayar</button>
                            <button id="storeFile" type="submit" hidden><span>Simpan</span></button>
                            <input type="file" id="getFile" name="proof_of_payment" onchange="readURL(this);" hidden>
                        </form>
                        <img id="img-preview" src="" alt="" />
                    </div>
                </div>
            </a>

        </div>
    </div>
</body>

</html>