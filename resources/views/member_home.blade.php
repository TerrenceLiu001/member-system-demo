<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>會員中心</title>
    <link rel="icon" href="{{ asset('asset/images/favicon.svg') }}" type="image/svg+xml">

    {{-- jquery 3.6.0 --}}
    <script src="{{ asset('asset/package/jquery.min.js') }}"></script> 

    {{-- bootstrap 4.0.0 --}}
    <script src="{{ asset('asset/package/popper.min.js') }}"></script>
    <script src="{{ asset('asset/package/bootstrap.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('asset/package/bootstrap.min.css') }}">
   

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('asset/css/member_center.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/member_home.css') }}">

</head>
<body>
    <div class="media_container">
        <div class="main_page">
            <div class="icon_field">
                <img class="desktop_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
                <img class="mobile_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
            </div>
            <div class="info_field">
                <div class="user_field">
                    <p>您好</p>
                    <p class="user_account"> {{ $username ?? '訪客' }}</p>
                </div>
                <div class="setting_list_field">
                    <div class="setting_item">
                        <div class="setting_name account_setting">
                            <p>帳戶設定</p>
                            <img class="icon_field" src="{{ asset('asset/images/next.svg') }}" alt="next_arrow">
                        </div>
                        <div class="description"><p>修改個人資訊</p></div>
                    </div>
                    <div class="setting_item">
                        <div class="setting_name">
                            <p>其它設定</p>                        
                            <img class="icon_field" src="{{ asset('asset/images/next.svg') }}" alt="next_arrow">                      
                        </div>
                        <div class="description"><p>施工中</p></div>
                    </div>
                    <div class="logout_setting_item">
                        <img src="{{ asset('asset/images/logout.svg') }}" alt="logout">
                        <p>登出</p>   
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
<script>

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $('.logout_setting_item').on('click', function (e) {
        if ($(e.target).is('p, img')) {
            $.get('/logout', { _token: CSRF_TOKEN }, function () {
                window.location.href = '/login';
            });
        }
    });

    $('.account_setting').on('click', function (e) {
        if ($(e.target).is('p, img')) {
            $.get('/set_account', { _token: CSRF_TOKEN }, function () {
                window.location.href = '/set_account';
            });
        }
    });

</script>
</html>