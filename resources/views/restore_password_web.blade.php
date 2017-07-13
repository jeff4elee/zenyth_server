<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Reset Password</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ URL::to('src/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="{{ URL::to('src/css/main.css') }}" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="container">
    <script src="{{ URL::to('src/js/resetpassword.js') }}">
        var token = '{{ Session::token() }}';
        var url = '{{ route('api_pw_reset', ['token' => $token]) }}';
    </script>

    <div class="form-signin">
        <h2 class="form-signin-heading">Reset your password</h2>
        <label for="inputEmail" class="sr-only">Password</label>
        <input type="password" id="password" class="form-control" placeholder="Password" required autofocus>
        <label for="inputPassword" class="sr-only">Confirm Password</label>
        <input type="password" id="password_confirmation" class="form-control" placeholder="Confirm Password" required>

        <button class="btn btn-lg btn-primary btn-block" type="submit" id="Submit">Reset Password</button>
    </div>

</div> <!-- /container -->

</body>
</html>
