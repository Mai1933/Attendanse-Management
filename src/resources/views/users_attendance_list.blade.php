@extends('headers/header_admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance_content">
        <div class="attendance_inner">
            <div class="ttl">
                <h2 class="ttl_content">{{ $user->name }}さんの勤怠</h2>
            </div>
            <div class="information">
                <div class="information_data">
                    <a href="/admin/attendance/staff/{{ $user->id }}/{{ $previousMonth }}" class="data_link">
                        <img src="{{ asset('storage/proceed.png') }}" alt="proceed" class="data_link-img">
                        <p class="data_link-character">前月</p>
                    </a>
                    <div class="data_content">
                        <img src="{{ asset('storage/calender.png') }}" alt="calender" class="data_content-img">
                        <p class="data_link-character">{{ date('Y/m', strtotime($date)) }}</p>
                    </div>
                    <a href="/admin/attendance/staff/{{ $user->id }}/{{ $nextMonth }}" class="data_link">
                        <p>翌月</p>
                        <img src="{{ asset('storage/next.png') }}" alt="next" class="data_link-character"
                            class="data_link-img">
                    </a>
                </div>
                <table class="information_table">
                    <tr class="table_headers">
                        <th class="table_name">日付</th>
                        <th class="table_content">出勤</th>
                        <th class="table_content">退勤</th>
                        <th class="table_content">休憩</th>
                        <th class="table_content">合計</th>
                        <th class="table_content">詳細</th>
                    </tr>
                    @foreach ($works as $work)
                        <tr class="table_contents">
                            <td class="table_content-name">
                                {{ date('m/d', strtotime($work->date)) }}({{ $workDayOfWeek[$work->id] }})
                            </td>
                            <td class="table_content-time">
                                {{ date('H:i', strtotime($work->start_time)) }}
                            </td>
                            <td class="table_content-time">
                                {{ $work->end_time ? date('H:i', strtotime($work->end_time)) : '' }}
                            </td>
                            <td class="table_content-time">{{ $formattedBreakTimes[$work->id] }}</td>
                            <td class="table_content-time">{{ $formattedWorkTimes[$work->id] }}</td>
                            <td class="table_detail">
                                <a href="/admin/attendance/{{ $work->id }}" class="detail_link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection