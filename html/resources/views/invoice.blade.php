<html>
{{--<head><meta name="viewport" content="initial-scale=1, maximum-scale=1"></head>--}}
<body>
<?php
//echo '<pre>';
//print_r($result);
//print_r($result_shipment);
//echo '</pre>';
//    dd($result);
    $url='https://devel-ajc-retail.kirimaja.id';
    // $url='https://kirimaja.id';
?>
<style>
    @font-face {
        font-family: Orkney;
        src: url("<?php echo $url ?>/assets/fonts/orkney-light.otf") format("opentype");
    }

    /* info (hed, dek, source, credit) */
    .rg-container {
        font-family: Orkney;
        font-size: 16px;
        line-height: 1.4;
        margin: 0;
        padding: 1em 0.5em;
        color: #222;
        border: 1px solid #434343;
        border-radius: 8px;
        -webkit-border-radius: 8px;
        -moz-border-radius: 8px;
        max-width: 685px;
    }
    .rg-header {
        margin-bottom: 1em;
        text-align: left;
    }

    .rg-header > * {
        display: block;
    }
    .rg-hed {
        font-weight: bold;
        font-size: 1.4em;
    }
    .rg-dek {
        font-size: 1em;
    }

    .rg-source {
        margin: 0;
        font-size: 0.75em;
        text-align: right;
    }
    .rg-source .footer {
        font-size: 1em;
        color:#000000;
    }

    .rg-source .post-colon {
        font-weight: bold;
    }

    /* table */
    table.rg-table {
        width: 100%;
        margin-bottom: 0.5em;
        font-size: 1em;
        border-collapse: collapse;
        border-spacing: 0;
    }
    table.rg-table tr {
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        border: 0;
        font-size: 100%;
        font: inherit;
        vertical-align: baseline;
        text-align: left;
        color: #333;
    }
    table.rg-table td {
        color: #000000;
    }
    table.rg-table thead {
        /* border-bottom: 3px solid #ddd; */
    }
    table.rg-table tr {
        /* border-bottom: 1px solid #ddd; */
        color: #222;
    }
    table.rg-table td.spacing {
        width:50px;
    }
    table.rg-table td.descp {
        width:275px;
    }
    table.rg-table td.descp2 {
        width:100px;
    }
    table.rg-table tr.highlight {
        background-color: #dcf1f0 !important;
    }
    table.rg-table.zebra tr:nth-child(even) {
        background-color: #f6f6f6;
    }
    table.rg-table th.spacing {
        width:50px;
    }
    table.rg-table th {
        font-weight: bold;
        padding: 0.35em;
        font-size: 0.9em;
        color:#9a9a9a;
    }
    table.rg-table td {
        padding: 0.35em;
        font-size: 0.9em;
    }
    table.rg-table .highlight td {
        font-weight: bold;
        color:#9a9a9a;
    }
    table.rg-table th.number,
    td.number {
        text-align: right;
    }

    /* media queries */
    @media screen and (max-width: 401px) {
        .rg-container {
            max-width: 401px;
            margin: 0 auto;
        }
        table.rg-table {
            width: 100%;
        }
        table.rg-table tr.hide-mobile,
        table.rg-table th.hide-mobile,
        table.rg-table td.hide-mobile {
            display: none;
        }
        table.rg-table thead {
            display: none;
        }
        table.rg-table tbody {
            width: 100%;
        }
        table.rg-table tr,
        table.rg-table th,
        table.rg-table td {
            display: block;
            padding: 0;
        }
        table.rg-table tr {
            border-bottom: none;
            margin: 0 0 1em 0;
            padding: 0.5em;
        }
        table.rg-table tr.highlight {
            background-color: inherit !important;
        }
        table.rg-table.zebra tr:nth-child(even) {
            background-color: transparent;
        }
        table.rg-table.zebra td:nth-child(even) {
            background-color: #f6f6f6;
        }
        table.rg-table tr:nth-child(even) {
            background-color: transparent;
        }
        table.rg-table td {
            padding: 0.5em 0 0.25em 0;
            border-bottom: 1px dotted #ccc;
            text-align: right;
        }
        table.rg-table td[data-title]:before {
            content: attr(data-title);
            font-weight: bold;
            display: inline-block;
            content: attr(data-title);
            float: left;
            margin-right: 0.5em;
            font-size: 0.95em;
        }
        table.rg-table td:last-child {
            padding-right: 0;
            border-bottom: 2px solid #ccc;
        }
        table.rg-table td:empty {
            display: none;
        }
        table.rg-table .highlight td {
            background-color: inherit;
            font-weight: normal;
        }

        .head-logo
        {
            margin-top: 50px;
            margin-bottom: 50px;
        }
    }

</style>
<div align="center">
<p align="center">
    <div class='rg-container'>
        <p align="center" style="margin-top: 25px;margin-left:500px;">

    @php
        $generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();
    @endphp

    <img style="padding-bottom: 5px" width="80%" src="data:image/png;base64,{{ base64_encode($generatorPNG->getBarcode($result->shipment->awb, $generatorPNG::TYPE_CODE_128)) }}">
    <br>
    <span style="font-size:12px;text-align:center;font-weight: bold;margin-top:10px"><?php echo $result->shipment->awb; ?></span>
        </p>
        <p align="center" style="margin-top: 75px;margin-bottom: 20px;">
            <img src="<?php echo $url.'/assets/logo-invoice.png';?>"><br/>
            <img src="https://chart.googleapis.com/chart?chs=200x200&amp;cht=qr&amp;chl=<?php echo $result->shipment->awb;?>" alt="QR code"/>
        </p>
        <div style="height:127px;margin-left:-8px;margin-right:-8px;background-image: url('<?php echo $url.'/assets/bg-invoice.jpg';?>');border:1px solid #d7d7d7;">
            <p style="text-align:center;padding:10px;">
                <span style="color:#626262">Payment Receipt</span>
                <br>
                <!--Phase#2Week#1 - Bayu - START -->
                <?php if($result->pickup_status){ ?>
                    <?php //$total = ($result->payment->transaction_amount); ?>
                    <span style="font-size: 2.5em;color:#626262"><b><?php echo number_format($result->payment->transaction_total_amount,2);?></b></span>
                <?php }else{ ?>
                    <?php $total = ($result->payment->transaction_amount); ?>
                    <span style="font-size: 2.5em;color:#626262"><b><?php echo number_format($total,2);?></b></span>
                <?php } ?>
                <!--Phase#2Week#1 - Bayu - END -->
            </p>
        </div>
<table class='rg-table'>
    <tbody>
    <tr>
        <td class="spacing"></td>
        <td class='text descp'>
            <br>
            Sender
            <br>
            <span style="font-size: 1.5em;color:#626262"><b><?php echo $result->booking_origin_name; ?></b></span>
            <br>
            <?php echo $result->booking_origin_phone; ?>
            <br>
            <?php echo $result->booking_origin_addr_1; ?>
            <br>
            <?php echo $result->booking_origin_city; ?>
        </td>
        <td class='text descp2'><br></td>
        <td class='text descp' colspan="2">
            <br>
            Connote Number
            <br>
            <span style="font-size: 1.5em;color:#626262"><b><?php echo $result->shipment->awb; ?></b></span>
            <br>
            <span style="font-size: 1.0em;"><b><?php echo $result->service_type=="PLT"?"Sameday":$result->service_type=="GLD"?"Next Day":"Reguler"; ?></b></span>
            <br>
            <?php echo date('d-m-Y H:i:s',strtotime($result->created_at)); ?>
            <br><br>
            <!--Phase#2Week#1 - Bayu - START -->
            <?php if($result->pickup_status){
                $arrHari = array(
                    'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
                );
                $arrBulan = array (
            		1 =>   'Januari',
            		'Februari',
            		'Maret',
            		'April',
            		'Mei',
            		'Juni',
            		'Juli',
            		'Agustus',
            		'September',
            		'Oktober',
            		'November',
            		'Desember'
            	);

                $tglSch = $result->schedule_date;
                $tglSchSplit = explode('-', $tglSch);

                echo "Layanan Pickup";
                echo "<br>";
                echo $arrHari[date('w', strtotime($tglSch))].", ".$tglSchSplit[2] . ' ' . $arrBulan[(int)$tglSchSplit[1]] . ' ' . $tglSchSplit[0];
                echo "<br>";
                echo $result->schedule_time;
            } ?>
            <!--Phase#2Week#1 - Bayu - END -->
        </td>
    </tr>
    <tr>
        <td class="spacing"></td>
        <td class='text descp'>
            <br><br>
            Recipient
            <br>
            <span style="font-size: 1.5em;color:#626262"><b><?php echo $result->booking_destination_name; ?></b></span>
            <br>
            <?php echo $result->booking_destination_phone; ?>
            <br>
            <?php echo $result->booking_destination_addr_1; ?>
            <br>
            <?php echo $result->booking_destination_city; ?>

        </td>
        <td class='text descp2'><br></td>
        <td class='text descp' colspan="2">
            <br><br>
            Item Description
            <?php
            foreach ($result->details as $detail) {
            $wch=0;
            ?>
            <br>
            <span style="font-size: 1.5em;color:#626262"><b><?php echo $detail->package_description; ?></b></span>
            <br>
            <table style="margin-left:-5px">
                <tr>
                    <td>No of Pieces</td>
                    <td><?php echo $detail->package_quantity; ?></td>
                </tr>
                <tr>
                    <td>Weight</td>
                    <td>
                        <?php
                        $wch = ($detail->package_volume > $detail->package_weight) ? $detail->package_volume : $detail->package_weight;
                        echo $wch;
                        ?> Kg
                    </td>
                </tr>
                <tr>
                    <td>Origin</td>
                    <td><?php echo $result_shipment->header->org; ?></td>
                </tr>
                <tr>
                    <td>Destination</td>
                    <td><?php echo $result_shipment->header->dest; ?></td>
                </tr>
            </table>
            <br>
            <?php
            }
            ?>
        </td>
    </tr>
    <tr>
        <td colspan="4">
            <hr style="width:103%;float:left;margin-left:-11px">
        </td>
    </tr>
    <tr>
        <td class='spacing'></td>
        <td class='text' colspan="2">Payment Details</td>
    </tr>
    <tr>
        <td class='spacing'></td>
        <td class='text descp'>Shipping Cost</td>
        <td class='text descp2'>:</td>
        <td class='text' style="text-align: right; padding-right: 15%;">IDR <?php echo number_format($result->payment->transaction_amount,2);?></td>
    </tr>

    <!--Phase#2Week#1 - Bayu - START -->
    <?php if($result->pickup_status){ ?>
        <?php if($result->payment->transaction_tax > 0){?>
            <tr>
                <td class='spacing'></td>
                <td class='text descp'>Shipping Tax</td>
                <td class='text descp2'>:</td>
                <td class='text' style="text-align: right; padding-right: 15%;">IDR <?php echo number_format($result->payment->transaction_tax,2);?></td>
            </tr>
        <?php } ?>
        <?php if($result->payment->transaction_voucher_amount > 0){?>
            <tr>
                <td class='spacing'></td>
                <td class='text descp' style="color: red">Shipping Disc</td>
                <td class='text descp2' style="color: red">:</td>
                <td class='text' style="color: red; text-align: right; padding-right: 15%;">IDR <?php echo number_format($result->payment->transaction_voucher_amount,2);?></td>
            </tr>
        <?php } ?>
    <?php } ?>
    <!--Phase#2Week#1 - Bayu - END -->

    <tr>
        <td class='spacing'></td>
        <td colspan="3">
            <hr style="width:85%;float:left">
        </td>
    </tr>

    <!--Phase#2Week#1 - Bayu - START -->
    <?php if($result->pickup_status){ ?>
        <tr>
            <td class='spacing'></td>
            <td class='text descp'><b>Total Amount Paid</b></td>
            <td class='text descp2'><b></b></td>
            <td class='text' style="text-align: right; padding-right: 15%;"><b>IDR <?php echo number_format($result->payment->transaction_total_amount,2);?></b></td>
        </tr>
    <?php }else{ ?>
        <tr>
            <td class='spacing'></td>
            <td class='text descp'><b>Total Amount Paid</b></td>
            <td class='text descp2'><b></b></td>
            <td class='text' style="text-align: right; padding-right: 15%;"><b>IDR <?php echo number_format($total,2);?></b></td>
        </tr>
    <?php } ?>

    <?php
        $leadTime = $result_shipment->header->leadTime;
        $leadTimeText = $leadTime;
        if($leadTime > 1){
            $leadTime2 = $leadTime - 1;
            $leadTimeText = $leadTime2."-".$leadTime;
        }
    ?>
    <tr>
        <td class='spacing'></td>
        <td class='text descp'><b>Estimasi Waktu Pengiriman</b></td>
        <td class='text descp2'><b></b></td>
        <td class='text' style="text-align: right; padding-right: 15%;"><b><?php echo $leadTimeText." Hari"; ?></b></td>
    </tr>
    <!--Phase#2Week#1 - Bayu - END -->

    <tr>
        <td class='spacing'></td>
        <td colspan="3"><br></td>
    </tr>
    <tr>
        <td class='spacing'></td>
        <td colspan="3" class='text descp'><span style="font-size: 1.2em;font-weight: lighter">Dimensions : L x W x H Volume</span></td>
    </tr>
    <tr>
        <td class='spacing'></td>
        <td colspan="3" class='text descp'>
            <table cellspacing="5" cellpadding="10">
                <tr>
                    <td style="background-color: #c7c7c7;color:#fff;text-align:center">L</td>
                    <td style="background-color: #c7c7c7;color:#fff;text-align:center">W</td>
                    <td style="background-color: #c7c7c7;color:#fff;text-align:center">H</td>
                    <td style="background-color: #c7c7c7;color:#fff;text-align:center">COL</td>
                    <td style="background-color: #c7c7c7;color:#fff;text-align:center">VOL</td>
                    <td style="background-color: #c7c7c7;color:#fff;text-align:center">CH</td>
                </tr>

                <?php
                foreach ($result->details as $detail) {
                $wch = 0;
                ?>

                <tr>
                    <td style="color:#000000;text-align:center"><?php echo number_format($detail->package_length,2); ?> </td>
                    <td style="color:#000000;text-align:center"><?php echo number_format($detail->package_width,2); ?> </td>
                    <td style="color:#000000;text-align:center"><?php echo number_format($detail->package_height,2); ?> </td>
                    <td style="color:#000000;text-align:center">1</td>
                    <td style="color:#000000;text-align:center"><?php echo number_format((($detail->package_width*1)*($detail->package_height*1)*($detail->package_length*1))/6000,2); ?> </td>
                    <td style="color:#000000;text-align:center">
                        <?php
                        $wch = ($detail->package_volume > $detail->package_weight) ? $detail->package_volume : $detail->package_weight;
                        echo number_format($wch,2);
                        ?>
                    </td>
                </tr>

                <?php
                }
                ?>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<div class='rg-source' style="margin-top: 50px">
    <p align="center" style="line-height: 50%;"><span class='footer'>This receipt is a legimate proof of transaction</span></p>
    <p align="center" style="line-height: 50%;"><span class='footer'>More information on Tracking, please visit:</span></p>
    <p align="center" style="line-height: 50%;"><span class='footer'><a href="<?php echo $url.'/invoicetnc';
            ?>" style="color:#000000">www.kirimaja.id</a></span></p>
</div>
</div>
</p>
</div>
</body>
</html>
