@extends('headers/header_admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
    <div class="attendance_content">
        <div class="attendance_inner">
            <div class="ttl">
                <h2 class="ttl_content">スタッフ一覧</h2>
            </div>
            <div class="information">
                <table class="information_table">
                    <tr class="table_headers">
                        <th class="table_name">名前</th>
                        <th class="table_content">メールアドレス</th>
                        <th class="table_content">月次勤怠</th>
                    </tr>
                    @foreach ($users as $user)
                        <tr class="table_contents">
                            <td class="table_content-name">{{ $user->name }}</td>
                            <td class="table_content-time">{{ $user->email }}</td>
                            <td class="table_detail">
                                <a href="/admin/attendance/staff/{{ $user->id }}" class="detail_link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection