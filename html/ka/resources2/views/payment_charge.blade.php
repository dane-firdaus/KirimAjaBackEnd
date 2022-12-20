<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Charge Payment</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
</head>
<body>
    <form id="paymentForm" action="{{$dokuPay}}" method="post">
        <input type="hidden" name="MALLID" value="7982">
        <input type="hidden" name="CHAINMERCHANT" value="NA">
        <input type="hidden" name="AMOUNT" value="{{$amount}}">
        <input type="hidden" name="PURCHASEAMOUNT" value="{{$amount}}">
        <input type="hidden" name="TRANSIDMERCHANT" value="{{$transId}}">
        <input type="hidden" name="WORDS" value="{{$words}}">
        <input type="hidden" name="REQUESTDATETIME" value="{{$requestDate}}">
        <input type="hidden" name="CURRENCY" value="360">
        <input type="hidden" name="PURCHASECURRENCY" value="360">
        <input type="hidden" name="SESSIONID" value="{{$transId}}">
        <input type="hidden" name="NAME" value="{{$name}}">
        <input type="hidden" name="EMAIL" value="{{$email}}">
        <input type="hidden" name="MOBILEPHONE" value="{{$mobile}}">
        <input type="hidden" name="BASKET" value="{{$basket}}">
    </form>
    <script>
        $(document).ready(function () {
            $('#paymentForm').submit();
        });
    </script>
</body>
</html>