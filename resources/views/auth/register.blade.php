@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Register</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('register') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('nickname') ? ' has-error' : '' }}">
                            <label for="nickname" class="col-md-4 control-label">{{ trans('user.nickname') }}</label>

                            <div class="col-md-6">
                                <input id="nickname" type="text" class="form-control" name="nickname" value="{{ old('nickname') }}" required autofocus>

                                @if ($errors->has('nickname'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('nickname') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">{{ trans('user.name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">{{ trans('user.email') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('gender_id') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">{{ trans('user.gender') }}</label>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="gender_id" value="1" {{ old('gender_id') == 1 ? 'checked' : '' }}>
                                    {{ trans('app.male') }}
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="gender_id" value="2" {{ old('gender_id') == 2 ? 'checked' : '' }}>
                                    {{ trans('app.female') }}
                                </label>

                                @if ($errors->has('gender_id'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gender_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">{{ trans('auth.password') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control" name="password" required>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default toggle-password" type="button" data-target="password">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </span>
                                </div>                                

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password-confirm" class="col-md-4 control-label">{{ trans('auth.password_confirmation') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default toggle-password" type="button" data-target="password-confirm">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </span>
                                </div>                                
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('captcha') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">{{ __('Captcha') }}</label>
                            <div class="col-md-6">
                                <div class="captcha d-flex align-items-center mb-2">
                                    <span id="captcha-img">{!! captcha_img('flat') !!}</span>
                                    <button type="button" class="btn btn-sm btn-secondary ml-2" id="reload-captcha">
                                        &#x21bb;
                                    </button>
                                </div>
                                <input type="text" class="form-control" name="captcha" required placeholder="Enter Captcha">
                                @if ($errors->has('captcha'))
                                    <span class="help-block"><strong>{{ $errors->first('captcha') }}</strong></span>
                                @endif
                            </div>
                        </div>                                            

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Register
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.getElementById('reload-captcha').addEventListener('click', function () {
        fetch('/reload-captcha')
            .then(res => res.json())
            .then(data => {
                document.getElementById('captcha-img').innerHTML = data.captcha;
            });
    });
</script>
<script>
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>
@endsection
