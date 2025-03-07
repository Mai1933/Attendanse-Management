@extends('headers/header_general')

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
                        <div class="content_information">
                            <p class="table_content-small">承認待ち</p>
                            <p class="table_content-small">西玲奈</p>
                            <p class="table_content">2023/06/01</p>
                            <p class="table_content">遅延のため</p>
                            <p class="table_content">2023/06/02</p>
                            <p class="table_content-small">
                                <a href="" class="detail_link">詳細</a>
                            </p>
                        </div>
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
                        <div class="content_information">
                            <p class="table_content-small">承認済み</p>
                            <p class="table_content-small">入間美兎</p>
                            <p class="table_content">2023/06/01</p>
                            <p class="table_content">遅延のため</p>
                            <p class="table_content">2023/06/02</p>
                            <p class="table_content-small">
                                <a href="" class="detail_link">詳細</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection