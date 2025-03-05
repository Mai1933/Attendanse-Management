@extends('headers/header_admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
    <div class="attendance_content">
        <div class="attendance_inner">
            <div class="ttl">
                <h2 class="ttl_content">勤怠詳細</h2>
            </div>
            <form class="information">
                @csrf
                <div class="information_row-first">
                    <div class="row_ttl">
                        <p class="row_ttl-content">名前</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner-name">
                            <p class="row_content-name">西　伶奈</p>
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">日付</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <input type="text" class="row_content-input" value="2023年" name="year">
                            <input type="text" class="row_content-input" value="6月1日" name="date">
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">出勤・退勤</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <input type="text" class="row_content-input" value="09:00" name="work_start">
                            <p class="row_content-character">〜</p>
                            <input type="text" class="row_content-input" value="20:00" name="work_end">
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">休憩</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <input type="text" class="row_content-input" value="12:00" name="break_start">
                            <p class="row_content-character">〜</p>
                            <input type="text" class="row_content-input" value="13:00" name="break_end">
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">休憩2</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <input type="text" class="row_content-input" name="break_start2">
                            <p class="row_content-character">〜</p>
                            <input type="text" class="row_content-input" name="break_end2">
                        </div>
                    </div>
                </div>
                <div class="information_row-last">
                    <div class="row_ttl">
                        <p class="row_ttl-content">備考</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <textarea name="remarks" class="row_content-textarea"></textarea>
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