<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common_admin.css') }}">
    @yield('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <title>Attendance Management</title>
</head>

<body>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('storage/logo.svg') }}" alt="icon" class="logo_icon">
        </div>
        <div class="responsive_btn">
            <div class="menu_line"></div>
            <div class="menu_line"></div>
            <div class="menu_line"></div>
        </div>
        <nav class="header_nav">
            <ul class="nav_links">
                <li><a href="/attendance" class="links_content">勤怠</a></li>
                <li><a href="/attendance/list" class="links_content">勤怠一覧</a></li>
                <li><a href="/stamp_correction_request/list" class="links_content">申請</a></li>
                <form action="/logout" method="post">
                    @csrf
                    <li><button type="submit" class="links_content-button">ログアウト</button></li>
                </form>
            </ul>
        </nav>
    </header>
    @yield('content')
    <script src="{{ asset('js/master.js') }}"></script>
</body>

</html>