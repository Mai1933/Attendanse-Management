@extends('headers/header_admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/approve.css') }}">
@endsection

@section('content')
    <div class="attendance_content">
        <div class="attendance_inner">
            <div class="ttl">
                <h2 class="ttl_content">修正申請詳細</h2>
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
                            <p type="text" class="row_content-input" name="year">2023年</p>
                            <p type="text" class="row_content-input" name="date">6月1日</p>
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">出勤・退勤</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <p type="text" class="row_content-input" name="work_start">09:00</p>
                            <p class="row_content-character">〜</p>
                            <p type="text" class="row_content-input" name="work_end">20:00</p>
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">休憩</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <p type="text" class="row_content-input" name="break_start">12:00</p>
                            <p class="row_content-character">〜</p>
                            <p type="text" class="row_content-input" name="break_end">13:00</p>
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">休憩2</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <p type="text" class="row_content-input" name="break_start2"></p>
                            <p class="row_content-character">〜</p>
                            <p type="text" class="row_content-input" name="break_end2"></p>
                        </div>
                    </div>
                </div>
                <div class="information_row-last">
                    <div class="row_ttl">
                        <p class="row_ttl-content">備考</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <p name="remarks" class="row_content-textarea">電車遅延のため</p>
                        </div>
                    </div>
                </div>
                <div class="information_button">
                    <button type="submit" class="button-submit">承認</button>
                </div>
            </form>
        </div>
    </div>
@endsection