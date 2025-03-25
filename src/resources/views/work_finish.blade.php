@extends('headers/header_general_finish')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/works.css') }}">
@endsection

@section('content')
    <div class="works">
        <div class="works_inner">
            <form action="" class="works_form">
                @csrf
                <div class="works_status">
                    <p class="works_status-content">退勤済</p>
                </div>
                <p class="works_date">{{ date('Y年n月j日', strtotime($date)) }}({{ $dayOfWeek }})</p>
                <div class="works_time">
                    <p id="currentTime" class="works_time-content"></p>
                </div>
                <p class="works_message">お疲れ様でした。</p>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/time.js') }}"></script>
@endsection