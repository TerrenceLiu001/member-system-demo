<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>忘記密碼</title>
    <link rel="icon" href="{{ asset('asset/images/favicon.svg') }}" type="image/svg+xml">

    {{-- jquery 3.6.0 --}}
    <script src="{{ asset('asset/package/jquery.min.js') }}"></script> 

    {{-- bootstrap 4.0.0 --}}
    <link rel="stylesheet" href="{{ asset('asset/package/bootstrap.min.css') }}">
   

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('asset/css/member_center.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/forget_password.css') }}">

    {{-- js --}}
    <script type="module" src="{{ asset('asset/js/utilities.js') }}" defer></script>
    <script type="module" src="{{ asset('asset/js/forget_password.js') }}" defer></script>
     
</head>
<body>
    <div class="media_container">
        <div class="main_page">
            <div class="icon_field">
                <img class="desktop_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
                <img class="mobile_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
            </div>
            <div class="forget_field">
                <div class="header_section">
                    <p>忘記密碼</p>
                    <p class="description">系統將會寄送重置密碼頁面至您的電子信箱</p>
                </div>
                <div class="data_group">
                    <div class="label_part">
                        <p> 電子郵件 </p>
                    </div>
                    <div class="input_part">
                        <div class="input_include_verify width_100">
                            <input type="email" class="media_input width_100" id="email" placeholder="test@example.com">
                        </div>
                        <div class="update_status_hint warn" id="email_hint">
                            <img id="email_warn_icon" class="hidden" src="{{ asset('asset/images/warn_password.svg') }}"  alt="warn_logo">
                            <img id="email_success_icon" class="hidden" src="{{ asset('asset/images/check_password.svg') }}" alt="success_logo">
                            <img id="email_fail_icon" class="hidden" src="{{ asset('asset/images/error_password.svg') }}" alt="fail_logo">
                            <p></p>
                        </div>
                    </div>
                </div>
                <div class="btn_field">
                    <button type="button" class="media_btn width_100 disable"><p>送出</p></button>
                    <p>沒有收到密碼重置連結嗎？<a href="#" id="resend_link">再次寄送</a> <span>(15s)</span></p>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>

</html>