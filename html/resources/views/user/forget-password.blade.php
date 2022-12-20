<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="{{ Storage::url('css/bootstrap.min.css') }}">

	<title>Buat Password Baru • KirimAja</title>
	<style>
		html,
		body {
			height: 100%;
			background-image: url('{{ Storage::url('artwork/ka-pattern.png') }}');
			background-position: center;
			background-repeat: no-repeat;
			background-size: cover;
		}

		body {
			display: -ms-flexbox;
			display: flex;
			-ms-flex-align: center;
			align-items: center;
			padding-top: 40px;
			padding-bottom: 40px;
			background-color: #f5f5f5;
			color: #ffffff;
		}

		.form-signin, .block {
			width: 100%;
			max-width: 330px;
			padding: 15px;
			margin: auto;
		}

		.form-signin .checkbox {
			font-weight: 400;
		}

		.form-signin .form-control {
			position: relative;
			box-sizing: border-box;
			height: auto;
			padding: 10px;
			font-size: 16px;
		}

		.form-signin .form-control:focus {
			z-index: 2;
		}

		.form-signin input[type="email"] {
			margin-bottom: -1px;
			border-bottom-right-radius: 0;
			border-bottom-left-radius: 0;
		}

		.form-signin input[name="newPassword"] {
			margin-bottom: -1px;
			border-bottom-right-radius: 0;
			border-bottom-left-radius: 0;
		}

		.form-signin input[name="repeatNewPassword"] {
			margin-bottom: 10px;
			border-top-left-radius: 0;
			border-top-right-radius: 0;
		}
	</style>
	<script text="text/javascript">
		function checkForm(form) {
			if(form.newPassword.value != "" && form.newPassword.value != form.repeatNewPassword.value) {
				alert("Mohon maaf, password yang anda masukkan pada bagian Ulangi Password Baru tidak sama.");
      			form.repeatNewPassword.focus();
				return false
			}
			
			return true;
		}
	</script>
</head>

<body class="text-center">
	@if (isset($passwordValidated))
	<div class="block">
		<img class="mb-4" src="{{ Storage::url('appicon.png') }}" alt="" width="72" height="72">
		<p class="mb-3 font-weight-normal">Password berhasil diubah, silahkan masuk dengan password baru anda.</p>
	</div>
	@else
	<form action="" method="POST" class="form-signin" onsubmit="return checkForm(this);">
		{{ csrf_field() }}
		<img class="mb-4" src="{{ Storage::url('appicon.png') }}" alt="" width="72" height="72">
		<h1 class="h3 mb-3 font-weight-normal">Buat Password Baru</h1>
		<label for="inputEmail" class="sr-only">Password</label>
		<input type="password" id="inputPassword" name="newPassword" class="form-control" placeholder="Password Baru" required="">
		<label for="reinputPassword" class="sr-only">Re-enter Password</label>
		<input type="password" id="reinputPassword" name="repeatNewPassword" class="form-control" placeholder="Ulangi Password Baru" required="">
		
		<button class="btn btn-lg btn-primary btn-block" type="submit">Ganti Password</button>
		<p class="mt-5 mb-3">© 2020 KirimAja</p>
	</form>
	@endif
	
	<!-- Optional JavaScript -->
	<!-- jQuery first, then Popper.js, then Bootstrap JS -->
	<!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
		integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
		crossorigin="anonymous"></script>
	<script src="{{ Storage::url('js/bootstrap.bundle.min.js') }}"></script> -->
</body>

</html>