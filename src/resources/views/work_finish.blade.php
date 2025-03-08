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
                    <p class="works_status-content">休憩中</p>
                </div>
                <p class="works_date">2023年6月1日(木)</p>
                <div class="works_time">
                    <p class="works_time-content">08:00</p>
                </div>
                <p class="works_message">お疲れ様でした。</p>
            </form>
        </div>
    </div>
@endsection