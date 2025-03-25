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
            <form class="information" action="/stamp_correction_request/approve/{{ $application->work_id }}" method="post">
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
                            <input type="text" class="row_content-input" name="year"
                                value="{{ date('Y', strtotime($application->date)) }}年">
                            </input>
                            <input type="text" class="row_content-input" name="date"
                                value="{{ date('n月j日', strtotime($application->date)) }}">
                            </input>
                        </div>
                    </div>
                </div>
                <div class="information_row">
                    <div class="row_ttl">
                        <p class="row_ttl-content">出勤・退勤</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <input type="text" class="row_content-input" name="work_start"
                                value="{{ date('H:i', strtotime($application->start_time)) }}">
                            </input>
                            <p class="row_content-character">〜</p>
                            <input type="text" class="row_content-input" name="work_end"
                                value="{{ date('H:i', strtotime($application->end_time)) }}">
                            </input>
                        </div>
                    </div>
                </div>
                @foreach ($breakingApplications as $breakingApplication)
                    <div class="information_row">
                        <div class="row_ttl">
                            <p class="row_ttl-content">休憩</p>
                        </div>
                        <div class="row_content">
                            <div class="row_content-inner">
                                <input type="text" class="row_content-input" name="break_start"
                                    value="{{ date('H:i', strtotime($breakingApplication->start_time)) }}">
                                </input>
                                <p class="row_content-character">〜
                                </p>
                                <input type="text" class="row_content-input" name="break_end"
                                    value="{{ date('H:i', strtotime($breakingApplication->end_time)) }}">
                                </input>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="information_row-last">
                    <div class="row_ttl">
                        <p class="row_ttl-content">備考</p>
                    </div>
                    <div class="row_content">
                        <div class="row_content-inner">
                            <input name="remarks" class="row_content-textarea" value="{{ $application->remarks }}">
                            </input>
                        </div>
                    </div>
                </div>
                @if ($application->status === '承認済み')
                    <div class="information_caution">
                        <p class="caution-content"></p>
                    </div>
                @else
                    <div class="information_button">
                        <button type="submit" class="button-submit">承認</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection