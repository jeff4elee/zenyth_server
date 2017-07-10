@extends('layouts.master')

@section('title')
Zenyth Reset Password
@endsection

@section('content')

<script>
    var token = '{{ Session::token() }}';
    var url = '{{ route('api_pw_reset', ['token' => $token]) }}';
</script>

<div class="row">
    <div class="col-md-4 col-md-offset-4" id="body">
        <h3>Reset Password</h3>
            <div class="form-group">
                <label for="password">New Password</label>
                <input class="form-control" type="password" name="password" id="password">
            </div>

            <div class="form-group">
                <label for="password">Confirm New Password</label>
                <input class="form-control" type="password" name="password_confirmation" id="password_confirmation">
            </div>
            <button type="submit" class="btn-primary" id="Submit">Submit</button>
    </div>
</div>


@endsection
