@extends('headers/header_general')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance_content">
        <div class="attendance_inner">
            <div class="ttl">
                <h2 class="ttl_content">勤怠一覧</h2>
            </div>
            <div class="information">
                <div class="information_data">
                    <a href="/attendance/list/{{ $year }}/{{ $previousMonth }}" class="data_link">
                        <img src="{{ asset('storage/proceed.png') }}" alt="proceed" class="data_link-img">
                        <p class="data_link-character">前月</p>
                    </a>
                    <div class="data_content">
                        <img src="{{ asset('storage/calender.png') }}" alt="calender" class="data_content-img">
                        <p class="data_link-character">{{ $month }}</p>
                    </div>
                    <a href="/attendance/list/{{ $year }}/{{ $nextMonth }}" class="data_link">
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
                    @if (!empty($workDayOfWeek))
                        @foreach ($works as $work)
                            <tr class="table_contents">
                                <td class="table_content-name">
                                    {{ date('m/d', strtotime($work->date))}}({{ $workDayOfWeek[$work->id] }})
                                </td>
                                <td class="table_content-time">
                                    {{ $work->start_time ? date('H:i', strtotime($work->start_time)) : '' }}
                                </td>
                                <td class="table_content-time">
                                    {{ $work->end_time ? date('H:i', strtotime($work->end_time)) : '' }}
                                </td>
                                <td class="table_content-time">{{ $formattedBreakTimes[$work->id] }}</td>
                                <td class="table_content-time">{{ $formattedWorkTimes[$work->id] }}</td>
                                <td class="table_detail">
                                    <a href="/attendance/{{ $work->id }}" class="detail_link">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="table_content-time text-center">該当する勤怠データはありません。</td> <!-- メッセージを表示 -->
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection