@extends('headers/header_admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/applications.css') }}">
@endsection

@section('content')
    <div class="application">
        <div class="application_inner">
            <div class="ttl">
                <h2 class="ttl_content">申請一覧</h2>
            </div>
            <div class="content">
                <input id="waiting" type="radio" name="tab_item" checked>
                <label class="tab_item" for="waiting">承認待ち</label>
                <input id="approved" type="radio" name="tab_item">
                <label class="tab_item" for="approved">承認済み</label>
                <div class="tab_content" id="waiting_content">
                    <div class="waiting_content-table">
                        <div class="content_headers">
                            <p class="table_content-small">状態</p>
                            <p class="table_content-small">名前</p>
                            <p class="table_content">対象日時</p>
                            <p class="table_content">申請理由</p>
                            <p class="table_content">申請日時</p>
                            <p class="table_content-small">詳細</p>
                        </div>
                        @foreach ($waitingWorkings as $waitingWorking)
                            <div class="content_information">
                                <p class="table_content-small">{{ $waitingWorking->status }}</p>
                                <p class="table_content-small">{{ $user[$waitingWorking->work_id]->name }}</p>
                                <p class="table_content">
                                    {{ date('Y/m/d', strtotime($waitingOldWork[$waitingWorking->work_id]->date)) }}</p>
                                <p class="table_content">{{ $waitingWorking->remarks }}</p>
                                <p class="table_content">{{ date('Y/m/d', strtotime($waitingWorking->date)) }}</p>
                                <p class="table_content-small">
                                    <a href="" class="detail_link">詳細</a>
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="tab_content" id="approved_content">
                    <div class="approved_content-table">
                        <div class="content_headers">
                            <p class="table_content-small">状態</p>
                            <p class="table_content-small">名前</p>
                            <p class="table_content">対象日時</p>
                            <p class="table_content">申請理由</p>
                            <p class="table_content">申請日時</p>
                            <p class="table_content-small">詳細</p>
                        </div>
                        @foreach ($completedWorkings as $completedWorking)
                            <div class="content_information">
                                <p class="table_content-small">{{ $completedWorking->status }}</p>
                                <p class="table_content-small">{{ $user[$completedWorking->work_id]->name }}</p>
                                <p class="table_content">
                                    {{ date('Y/m/d', strtotime($completedOldWork[$completedWorking->work_id]->date)) }}</p>
                                <p class="table_content">{{ $completedWorking->remarks }}</p>
                                <p class="table_content">{{ date('Y/m/d', strtotime($completedWorking->date))}}</p>
                                <p class="table_content-small">
                                    <a href="" class="detail_link">詳細</a>
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection