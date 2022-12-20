<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

    <title>FAQ • KirimAja</title>
    <style>
		body {
			height: 100%;
			background-image: url('{{ Storage::url('artwork/ka-pattern.png') }}');
			background-position: center;
			background-repeat: no-repeat;
			background-size: cover;
		}
        button:hover {
            text-decoration: none !important;
        }
    </style>
  </head>
  <body>
    <div class="container">
        <div class="row justify-content-md-center mt-5">
            <div class="card col-md-8 shadow p-4 ml-2 mr-2 mb-5 bg-white rounded">
                <h1 class="display-4 text-center">Frequently asked questions</h1>
                <div class="accordion mt-4" id="faq_content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#explain_sohib_subconsole" aria-expanded="false" aria-controls="#explain_sohib_subconsole">
                                    Apa itu Sohib dan Sub-Console KirimAja? 
                                </button>
                            </h2>
                        </div>
                        <div id="explain_sohib_subconsole" class="collapse" aria-labelledby="headingOne" data-parent="#faq_content">
                            <div class="card-body">
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <b>Sohib KirimAja</b> adalah pihak yang melakukan transaksi pengiriman barang menggunakan aplikasi KirimAja dan mengantarkan paket ke Drop Point atau Sub-Console. 
                                    </li>
                                    <li class="list-group-item">
                                        <b>Sub-Console KirimAja</b> adalah Sohib yang mempunyai peran tambahan untuk menerima atau mengumpulkan paket dari Sohib dan/atau Customer kemudian bertugas untuk mengantarkan paket ke Drop Point terdekat.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#expected_package_arrive" aria-expanded="false" aria-controls="#expected_package_arrive">
                                    Kapan barang/paket akan tiba di tujuan?
                                </button>
                            </h2>
                        </div>
                        <div id="expected_package_arrive" class="collapse" aria-labelledby="headingOne" data-parent="#faq_content">
                            <div class="card-body">
                                Barang/Paket akan tiba di tujuan dalam waktu 1-2 hari (intra-city) dan 3-5 hari (antar kota/provinsi) setelah diserahkan di Drop Point 
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#allowed_items" aria-expanded="false" aria-controls="#allowed_items">
                                    Apa saja Barang/Paket yang dapat dikirim? 
                                </button>
                            </h2>
                        </div>
                        <div id="allowed_items" class="collapse" aria-labelledby="headingOne" data-parent="#faq_content">
                            <div class="card-body">
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <b>Jenis Barang yang Dilayani</b><br>
                                        Barang yang dapat dikirimkan menggunakan KirimAja adalah General Commodity (GENCO). General Cargo adalah kargo atau barang yang memiliki sifat yang tidak membahayakan, tidak mudah rusak, busuk ataupun mati, contohnya: 
                                        <ul>
                                            <li>Aksesoris HP</li>
                                            <li>Aksesoris Wanita</li>
                                            <li>Barang Gaming</li>
                                            <li>Buku</li>
                                            <li>Mainan</li>
                                            <li>Makanan yang tidak mudah rusak</li>
                                            <li>Pakaian, Tas, Sepatu</li>
                                            <li>Perlengkapan Bayi</li>
                                            <li>Perlengkapan Rumah Tangga</li>
                                            <li>Produk Fashion</li>
                                            <li>Produk Kecantikan</li>
                                            <li>dan Sebagainya</li>
                                        </ul>
                                    </li>
                                    <li class="list-group-item">
                                        <b>KirimAja tidak dapat menjamin barang kiriman dan risiko berupa:</b><br>
                                        <ul>
                                            <li>Uang tunai atau ekuivalennya (misalnya kartu kredit yang belum ditandatangani, kartu ATM beserta nomor PIN, cheque cash, dll)</li>
                                            <li>Logam mulia, intan, berlian dan batu mulia lainnya, perhiasan, arloji yang bernilai mahal walaupun barang-barang tersebut diasuransikan</li>
                                            <li>Surat yang sifatnya pribadi dengan berat di bawah 500 gr, warkat pos, dan kartu pos.</li>
                                            <li>Benda-benda yang bernilai seni dengan nilai penuh.</li>
                                            <li>Narkoba, material pornografi dan barang-barang lain yang dilarang oleh pemerintah.</li>
                                            <li>Barang/komoditi yang dilindungi oleh negara.</li>
                                            <li>Berasal dari sumber yang tidak halal.</li>
                                            <li>Kerugian yang dilakukan dengan sengaja oleh Pengirim dan Penerima Paket.</li>
                                            <li>Kerugian karena kerusakan sifat alamiah barang itu sendiri.</li>
                                            <li>Kerugian akibat resiko nuklir dan sejenisnya.</li>
                                            <li>Kerugian akibat penipuan.</li>
                                            <li>Kehilangan fungsi terhadap peralatan elektronik, kecuali disebabkan karena kecelakaan.</li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#member_benefits" aria-expanded="false" aria-controls="#member_benefits">
                                    Apa benefit sebagai Sohib atau Sub-Console? 
                                </button>
                            </h2>
                        </div>
                        <div id="member_benefits" class="collapse" aria-labelledby="headingOne" data-parent="#faq_content">
                            <div class="card-body">
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <b>Pengantaran Langsung (Direct Delivery)</b><br>
                                        Apabila Sohib KirimAja memilih opsi Pengantaran Langsung, yaitu langsung melakukan pengantaran paket ke Drop Point KirimAja, maka Sohib KirimAja akan diberikan komisi 50% dari harga pengiriman (sebelum pajak 1%). Komisi dapat langsung diperoleh pada saat pembayaran dari Sohib ke pihak KirimAja.<br>
                                        Apabila berat Barang/ Paket lebih dari 10 Kg maka Sohib KirimAja akan diberikan komisi 25% dari harga pengiriman (sebelum pajak 1%).<br>
                                        Pembayaran melalui Digital Payment yang ada di aplikasi, harga pada saat pembayaran akan otomatis dipotong komisi 50% oleh sistem.
                                    </li>
                                    <li class="list-group-item">
                                        <b>Pengantaran ke Sub-Console (Indirect Delivery)</b><br>
                                        Apabila Sohib KirimAja memilih opsi Indirect Delivery, yaitu memilih pengiriman paket melalui Sub-Console, maka Sohib KirimAja mendapatkan komisi 40%, sedangkan Sub-Console mendapatkan komisi 10% dari harga pengiriman (sebelum pajak 1%). Sohib akan mendapatkan langsung komisi pada saat melakukan transaksi.<br>
                                        Apabila berat Barang/Paket lebih dari 10 Kg maka Sohib KirimAja akan diberikan komisi 15% dari harga pengiriman (sebelum pajak 1%).<br>
                                        Pembayaran melalui Digital Payment yang ada di aplikasi, harga pada saat pembayaran akan otomatis dipotong komisi 40% oleh sistem.<br>
                                        Sedangkan untuk Sub-Console tidak langsung mendapatkan komisi saat melakukan proses penerimaan dan pengantaran barang. Komisi 10% untuk Sub-Console akan dibayarkan oleh pihak KirimAja kepada Sub-Console dengan mekanisme transfer ke rekening Sub-Console.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#how_pricing_works" aria-expanded="false" aria-controls="#how_pricing_works">
                                    Bagaimana penentuan harga yang dikenakan oleh KirimAja? 
                                </button>
                            </h2>
                        </div>
                        <div id="how_pricing_works" class="collapse" aria-labelledby="headingOne" data-parent="#faq_content">
                            <div class="card-body">
                                Apabila nilai dimensi bruto lebih besar dari berat Paket yang dimasukkan, maka harga yang akan dikenakan adalah sebesar nilai dimensi bruto Paket. Apabila berat Paket lebih besar dari perhitungan dimensi bruto Paket, maka harga yang dikenakan adalah sebesar berat Paket.
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#how_it_works" aria-expanded="false" aria-controls="#how_it_works">
                                    Bagaimana alur pengiriman Paket melalui KirimAja? 
                                </button>
                            </h2>
                        </div>
                        <div id="how_it_works" class="collapse" aria-labelledby="headingOne" data-parent="#faq_content">
                            <div class="card-body">
                                <ul>
                                    <li><b>Sohib (Agent)</b> memasukkan informasi lengkap pengirim, penerima, dimensi dan berat Paket.</li>
                                    <li><b>Sohib</b> akan memberikan informasi estimasi biaya pengiriman kepada Pengirim. </li>
                                    <li><b>Sohib</b> mengantarkan Paket ke Sub-Console/Drop Point yang dipilih.</li>
                                    <li><b>Sub-Console/Drop Point</b> bertugas untuk meneruskan Paket ke Main Console.</li>
                                    <li>KirimAja akan mengantarkan Paket ke Penerima dalam waktu 1-2 hari (intra-city) dan 3-5 hari (antar kota/provinsi)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#how_to_pay" aria-expanded="false" aria-controls="#how_to_pay">
                                    Bagaimana proses pembayaran saat mengirimkan Paket?
                                </button>
                            </h2>
                        </div>
                        <div id="how_to_pay" class="collapse" aria-labelledby="headingOne" data-parent="#faq_content">
                            <div class="card-body">
                                Pembayaran dapat menggunakan berbagai macam metode pembayaran, seperti transfer Kartu Kredit, OVO, LinkAja, dsb. Pastikan melakukan pembayaran setelah Paket terverifikasi oleh Sub-Console.
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#dropship_mechanism" aria-expanded="false" aria-controls="#dropship_mechanism">
                                    Mekanisme Dropship
                                </button>
                            </h2>
                        </div>
                        <div id="dropship_mechanism" class="collapse" aria-labelledby="headingOne" data-parent="#faq_content">
                            <div class="card-body">
                                Saat ini KirimAja belum memiliki fitur khusus untuk Dropship.<br>
                                Namun apabila Paket yang dikirimkan merupakan Paket Dropship, Anda dapat mengisi Nama dan No. Seluler sesuai dengan informasi Dropshipper, namun Alamat Pengirim tetap diisi sesuai dengan alamat Sohib atau Pengirim. 
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#more_info" aria-expanded="false" aria-controls="#more_info">
                                    Informasi lebih lanjut silahkan hubungi
                                </button>
                            </h2>
                        </div>
                        <div id="more_info" class="collapse" aria-labelledby="headingOne" data-parent="#faq_content">
                            <div class="card-body">
                                <ul>
                                    <li>WhatsApp Service (24 Jam): +62813 888 95118</li>
                                    <li>Hotline Call Center (office hours): +6221 83702563</li>
                                    <li>Email: kirimaja@aerowisatalogistics.com</li>
                                    <li>Instagram: @kirimaja_id</li>
                                    <li>Facebook: @kirimajaindonesia</li>
                                    <li>Twitter: @kirimaja_id</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
  </body>
</html>