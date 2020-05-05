@extends('auth.layouts')
@section('title', trans('auth.register'))
@section('css')
    <link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" type="text/css" rel="stylesheet">
    <link href="/assets/custom/Plugin/sweetalert2/sweetalert2.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
    <form action="/register" method="post" id="register-form">
        @if(\App\Components\Helpers::systemConfig()['is_register'])
            @if($errors->any())
                <div class="alert alert-danger">
                    <span>{{$errors->first()}}</span>
                </div>
            @endif
            @csrf
            <input type="hidden" name="register_token" value="{{Session::get('register_token')}}"/>
            <input type="hidden" name="aff" value="{{Session::get('register_aff')}}"/>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="text" class="form-control" name="username"
                        value="{{Request::old('username') ? : Request::get('username')}}" required/>
                <label class="floating-label" for="username">{{trans('auth.username')}}</label>
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                @if($emailList)
                    <div class="input-group">
                        <input type="text" class="form-control" autocomplete="off" name="emailHead"
                                value="{{Request::old('emailHead')}}" id="emailHead" required/>
                        <label class="floating-label" for="emailHead">{{trans('auth.email')}}</label>
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-indigo-600 text-white">@</span>
                        </div>
                        <select class="form-control" name="emailTail" id="emailTail" data-plugin="selectpicker"
                                data-style="btn-outline-primary">
                            @foreach($emailList as $email)
                                <option value="{{$email->words}}">{{$email->words}}</option>
                            @endforeach
                        </select>
                        <input type="text" name="email" id="email" hidden/>
                    </div>
                @else
                    <input type="email" class="form-control" autocomplete="off" name="email"
                            value="{{Request::old('email')}}" id="email" required/>
                    <label class="floating-label" for="email">{{trans('auth.email')}}</label>
                @endif
            </div>
            @if(\App\Components\Helpers::systemConfig()['is_activate_account'] == 1)
                <div class="form-group form-material floating" data-plugin="formMaterial">
                    <div class="input-group" data-plugin="inputGroupFile">
                        <input type="text" class="form-control" name="verify_code"
                                value="{{Request::old('verify_code')}}" required/>
                        <label class="floating-label" for="verify_code">{{trans('auth.captcha')}}</label>
                        <span class="input-group-btn">
                            <button class="btn btn-success" id="sendCode" onclick="sendVerifyCode()">
                                {{trans('auth.request')}}
                            </button>
                        </span>
                    </div>
                </div>
            @endif
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="password" class="form-control" autocomplete="off" name="password" required/>
                <label class="floating-label" for="password">{{trans('auth.password')}}</label>
            </div>
            <div class="form-group form-material floating" data-plugin="formMaterial">
                <input type="password" class="form-control" autocomplete="off" name="confirmPassword" required/>
                <label class="floating-label" for="confirmPassword">{{trans('auth.confirm_password')}}</label>
            </div>
            @if(\App\Components\Helpers::systemConfig()['is_invite_register'])
                <div class="form-group form-material floating" data-plugin="formMaterial">
                    <input type="password" class="form-control" name="code"
                            value="{{Request::old('code') ? : Request::get('code')}}"
                            @if(\App\Components\Helpers::systemConfig()['is_invite_register'] == 2) required @endif/>
                    <label class="floating-label"
                            for="code">{{trans('auth.code')}}@if(\App\Components\Helpers::systemConfig()['is_invite_register'] == 1)
                            ({{trans('auth.optional')}}) @endif</label>
                </div>
                @if(\App\Components\Helpers::systemConfig()['is_free_code'])
                    <p class="hint">
                        <a href="/free" target="_blank">{{trans('auth.get_free_code')}}</a>
                    </p>
                @endif
            @endif
            @switch(\App\Components\Helpers::systemConfig()['is_captcha'])
                @case(1)<!-- Default Captcha -->
                <div class="form-group form-material floating input-group" data-plugin="formMaterial">
                    <input type="text" class="form-control" name="captcha" required/>
                    <label class="floating-label" for="captcha">{{trans('auth.captcha')}}</label>
                    <img src="{{captcha_src()}}" class="float-right"
                            onclick="this.src='/captcha/default?'+Math.random()" alt="{{trans('auth.captcha')}}"/>
                </div>
                @break
                @case(2)<!-- Geetest -->
                <div class="form-group form-material floating" data-plugin="formMaterial">
                    {!! Geetest::render() !!}
                </div>
                @break
                @case(3)<!-- Google reCaptcha -->
                <div class="form-group form-material floating" data-plugin="formMaterial">
                    {!! NoCaptcha::display() !!}
                    {!! NoCaptcha::renderJs(session::get('locale')) !!}
                </div>
                @break
                @case(4)<!-- hCaptcha -->
                <div class="form-group form-material floating" data-plugin="formMaterial">
                    {!! HCaptcha::display() !!}
                    {!! HCaptcha::renderJs(session::get('locale')) !!}
                </div>
                @break
                @default
            @endswitch
            <div class="form-group mt-20 mb-20">
                <div class="checkbox-custom checkbox-primary">
                    <input type="checkbox" name="term" id="term" {{Request::old('term') ? 'checked':''}} />
                    <label for="term">{{trans('auth.accept_term')}}
                        <button class="btn btn-xs btn-primary" data-target="#tos" data-toggle="modal"
                                type="button">{{trans('auth.tos')}}</button>
                        &
                        <button class="btn btn-xs btn-primary" data-target="#aup" data-toggle="modal"
                                type="button">{{trans('auth.aup')}}</button>
                    </label>
                </div>
            </div>
        @else
            <div class="alert alert-danger">
                <span>
                    {{trans('auth.system_maintenance')}}
                </span>
            </div>
        @endif
        <a href="/login"
                class="btn btn-danger btn-lg {{\App\Components\Helpers::systemConfig()['is_register']? 'float-left': 'btn-block'}}">{{trans('auth.back')}}</a>
        @if(\App\Components\Helpers::systemConfig()['is_register'])
            <button type="submit" class="btn btn-primary btn-lg float-right">{{trans('auth.register')}}</button>
        @endif
    </form>
@endsection
@section('modal')
    <div class="modal fade modal-info text-left" id="tos" aria-hidden="true" aria-labelledby="tos" role="dialog"
            tabindex="-1">
        <div class="modal-dialog modal-simple modal-sidebar modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close mr-15" data-dismiss="modal" aria-label="Close"
                            style="position:absolute;">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">{{\App\Components\Helpers::systemConfig()['website_name']}}
                        - {{trans('auth.tos')}} <small>2019年11月28日10:49</small></h4>
                </div>
                <div class="modal-body">
                    @include('auth.docs.tos')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-block bg-red-500 text-white mb-25"
                            data-dismiss="modal">{{trans('auth.close')}}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-info text-left" id="aup" aria-hidden="true" aria-labelledby="aup" role="dialog"
            tabindex="-1">
        <div class="modal-dialog modal-simple modal-sidebar modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close mr-15" data-dismiss="modal" aria-label="Close"
                            style="position:absolute;">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">{{\App\Components\Helpers::systemConfig()['website_name']}}
                        - {{trans('auth.aup')}} <small>2019年11月28日10:49</small></h4>
                </div>
                <div class="modal-body">
                    @include('auth.docs.aup')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-block bg-red-500 text-white mb-25"
                            data-dismiss="modal">{{trans('auth.close')}}</button>
                </div>
            </div>
        </div>
    </div>
    @endsection
@section('script')
	<!--[if lt IE 11]>
    <script src="/assets/custom/Plugin/sweetalert2/polyfill.min.js" type="text/javascript"></script>
    <![endif]-->
    <script src="/assets/custom/Plugin/sweetalert2/sweetalert2.min.js" type="text/javascript"></script>
    <script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/js/Plugin/bootstrap-select.js" type="text/javascript"></script>
    <script type="text/javascript">
        @if($emailList)
		function getEmail() {
			let email = $("#emailHead").val().trim();
			const emailTail = $("#emailTail").val();
			if (email === '') {
				swal.fire({title: '{{trans('auth.email_null')}}', type: 'warning', timer: 1500});
				return false;
			}
			email += '@' + emailTail;
			$("#email").val(email);
			return email;
		}
        @endif

		// 发送注册验证码
		function sendVerifyCode() {
			let flag = true; // 请求成功与否标记
			let email = $("#email").val().trim();
            @if($emailList)
				email = getEmail();
            @endif

			if (email === '') {
				swal.fire({title: '{{trans('auth.email_null')}}', type: 'warning', timer: 1500});
				return false;
			}

			$.ajax({
				type: "POST",
				url: "/sendCode",
				async: false,
				data: {_token: '{{csrf_token()}}', email: email},
				dataType: 'json',
				success: function (ret) {
					if (ret.status === "success") {
						swal.fire({title: ret.message, type: 'success'});
						$("#sendCode").attr('disabled', true);
						flag = true;
					} else {
						swal.fire({title: ret.message, type: 'error', timer: 1000, showConfirmButton: false});
						$("#sendCode").attr('disabled', false);
						flag = false;
					}
				},
				error: function () {
					swal.fire({title: '发送失败', type: 'error'});
					flag = false;
				}
			});

			// 请求成功才开始倒计时
			if (flag) {
				// 60秒后才能重新申请发送
				let left_time = 60;
				const tt = window.setInterval(function () {
					left_time--;
					if (left_time <= 0) {
						window.clearInterval(tt);
						$("#sendCode").removeAttr('disabled').text('{{trans('auth.request')}}');
					} else {
						$("#sendCode").text(left_time + ' s');
					}
				}, 1000);
			}
		}

		$('#register-form').submit(function (event) {
            @if($emailList)
			getEmail();
            @endif

            @switch(\App\Components\Helpers::systemConfig()['is_captcha'])
            @case(3)
			// 先检查Google reCAPTCHA有没有进行验证
			if ($('#g-recaptcha-response').val() === '') {
				swal.fire({title: '{{trans('auth.required_captcha')}}', type: 'error'});
				return false;
			}
            @break
            @case(4)
			// 先检查Google reCAPTCHA有没有进行验证
			if ($('#h-captcha-response').val() === '') {
				swal.fire({title: '{{trans('auth.required_captcha')}}', type: 'error'});
				return false;
			}
            @break
            @default
            @endswitch
		});
    </script>
@endsection
