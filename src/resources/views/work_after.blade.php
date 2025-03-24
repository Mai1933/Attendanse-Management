@extends('headers/header_general')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/works.css') }}">
@endsection

@section('content')
    <div class="works">
        <div class="works_inner">
            <form action="" method="" class="works_form">
                @csrf
                <div class="works_status">
                    <p class="works_status-content">出勤中</p>
                </div>
                <p class="works_date">{{ date('Y年n月j日', strtotime($date)) }}({{ $dayOfWeek }})</p>
                <div class="works_time">
                    <p class="works_time-content">{{ $time }}</p>
                </div>
                <div class="works_buttons">
                    <a href="/attendance/complete" class="works_buttons-work">退勤</a>
                    <a href="/attendance/break" class="works_buttons-break">休憩入</a>
                </div>
            </form>
        </div>
    </div>
@endsection