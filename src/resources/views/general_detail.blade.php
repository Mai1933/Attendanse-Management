@extends('headers/header_general')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
    <div class="attendance_content">
        <div class="attendance_inner">
            <div class="ttl">
                <h2 class="ttl_content">勤怠詳細</h2>
            </div>
            @if ($errors->any())
                <div class="error_messages">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li class="error_message">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class="information" action="/attendance/{{ $work->id }}" method="post">
                @csrf
                <div class="information_row-first">
                    <div class="row_ttl">
                        <p class="row_ttl-content">名前</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner-name">
                            <p class="row_content-name">{{ $user->name }}</p>
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">日付</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <input type="text" class="row_content-input" value="{{ $year }}" name="year">
                            <input type="text" class="row_content-input" value="{{ $date }}" name="date">
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">出勤・退勤</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <input type="time" class="row_content-input"
                                value="{{ date('H:i', strtotime($work->start_time)) }}" name="work_start">
                            <p class="row_content-character">〜</p>
                            <input type="time" class="row_content-input"
                                value="{{ $work->end_time ? date('H:i', strtotime($work->end_time)) : '' }}" name="work_end">
                        </div>
                    </div>
                </div>
                @foreach ($breakings as $index => $breaking)
                    <div class="information_row">
                        <div class="row_ttl">
                            <p class="row_ttl-content">休憩</p>
                        </div>
                        <div class="row_content">
                            <div class="row_content-inner">
                                <input type="time" class="row_content-input"
                                    value="{{ date('H:i', strtotime($breaking->start_time)) }}"
                                    name="break_start[{{ $index }}]">
                                <p class="row_content-character">〜</p>
                                <input type="time" class="row_content-input"
                                    value="{{$breaking->end_time ? date('H:i', strtotime($breaking->end_time)) : ''}}"
                                    name="break_end[{{ $index }}]">
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="information_row-last">
                    <div class="row_ttl">
                        <p class="row_ttl-content">備考</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner-textarea">
                            <textarea name="remarks" class="row_content-textarea">{{ $work->remarks }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="information_button">
                    <button type="submit" class="button-submit">修正</button>
                </div>
            </form>
        </div>
    </div>
@endsection