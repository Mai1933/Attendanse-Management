@extends('headers/header_general')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/works.css') }}">
@endsection

@section('content')
    <div class="works">
        <div class="works_inner">
            <form action="" class="works_form">
                @csrf
                <div class="works_status">
                    <p class="works_status-content">休憩中</p>
                </div>
                <p class="works_date">{{ date('Y年n月j日', strtotime($date)) }}({{ $dayOfWeek }})</p>
                <div class="works_time">
                    <p id="currentTime" class="works_time-content"></p>
                </div>
                <div class="works_buttons">
                    <a href="/attendance/return" class="works_buttons-break">休憩戻</a>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/time.js') }}"></script>
@endsection