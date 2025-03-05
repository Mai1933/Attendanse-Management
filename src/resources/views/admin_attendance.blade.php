@extends('headers/header_admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance_content">
        <div class="attendance_inner">
            <div class="ttl">
                <h2 class="ttl_content">2023年6月1日の勤怠</h2>
            </div>
            <div class="information">
                <div class="information_data">
                    <a href="" class="data_link">
                        <img src="{{ asset('storage/proceed.png') }}" alt="proceed" class="data_link-img">
                        <p class="data_link-character">前日</p>
                    </a>
                    <div class="data_content">
                        <img src="{{ asset('storage/calender.png') }}" alt="calender" class="data_content-img">
                        <p class="data_link-character">2023/06/01</p>
                    </div>
                    <a href="" class="data_link">
                        <p>翌日</p>
                        <img src="{{ asset('storage/next.png') }}" alt="next" class="data_link-character" class="data_link-img">
                    </a>
                </div>
                <table class="information_table">
                    <tr class="table_headers">
                        <th class="table_name">名前</th>
                        <th class="table_content">出勤</th>
                        <th class="table_content">退勤</th>
                        <th class="table_content">休憩</th>
                        <th class="table_content">合計</th>
                        <th class="table_content">詳細</th>
                    </tr>
                    <tr class="table_contents">
                        <td class="table_content-name">山田 太郎</td>
                        <td class="table_content-time">09:00</td>
                        <td class="table_content-time">09:00</td>
                        <td class="table_content-time">09:00</td>
                        <td class="table_content-time">09:00</td>
                        <td class="table_detail">
                            <a href="" class="detail_link">詳細</a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection