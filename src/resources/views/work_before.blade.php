@extends('headers/header_general')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/works_before.css') }}">
@endsection

@section('content')
    <div class="works">
        <div class="works_inner">
            <form action="/attendance" method="post" class="works_form">
                @csrf
                <div class="works_status">
                    <p class="works_status-content">勤務外</p>
                </div>
                @error('attendance')
                    <span class="works_error-login">
                        <p class="works_error_message">{{$errors->first('attendance')}}</p>
                    </span>
                @enderror
                <p class="works_date">{{ date('Y年n月j日', strtotime($date)) }}({{ $dayOfWeek }})</p>
                <div class="works_time">
                    <p class="works_time-content">{{ $time }}</p>
                </div>
                <button type="submit" class="works_buttons-work">出勤</button>
            </form>
        </div>
    </div>
@endsection