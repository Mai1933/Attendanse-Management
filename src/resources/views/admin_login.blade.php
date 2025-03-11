@extends('headers/header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <div class="login_content">
        <div class="login_content-inner">
            <form class="login_form" action="/admin/login" method="post">
                @csrf
                <h2 class="form_ttl">管理者ログイン</h2>
                @error('adminLogin')
                    <span class="input_error-login">
                        <p class="input_error_message">{{$errors->first('adminLogin')}}</p>
                    </span>
                @enderror
                <div class="form_inputs">
                    <div class="input_bunch">
                        <span class="bunch_ttl">メールアドレス</span>
                        @error('email')
                            <span class="input_error">
                                <p class="input_error_message">{{$errors->first('email')}}</p>
                            </span>
                        @enderror
                        <input type="text" class="bunch_input" name="email">
                    </div>
                    <div class="input_bunch">
                        <span class="bunch_ttl">パスワード</span>
                        @error('password')
                            <span class="input_error">
                                <p class="input_error_message">{{$errors->first('password')}}</p>
                            </span>
                        @enderror
                        <input type="password" class="bunch_input" name="password">
                    </div>
                </div>
                <button type="submit" class="form_submit">管理者ログインする</button>
            </form>
        </div>
    </div>
@endsection