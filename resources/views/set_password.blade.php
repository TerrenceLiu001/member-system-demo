<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>設定密碼</title>
    <link rel="icon" href="{{ asset('asset/images/favicon.svg') }}" type="image/svg+xml">

    {{-- jquery 3.6.0 --}}
    <script src="{{ asset('asset/package/jquery.min.js') }}"></script>

    {{-- bootstrap 4.0.0 --}}
    <link rel="stylesheet" href="{{ asset('asset/package/bootstrap.min.css') }}">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('asset/css/member_center.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/password_setting.css') }}">

    {{-- js --}}
    <script type="module" src="{{ asset('asset/js/utilities.js') }}" defer></script>
    <script type="module" src="{{ asset('asset/js/set_password.js') }}" defer></script>

</head>

<body>
    <div class="media_container">
        <div class="main_page">
            <div class="icon_field">
                <img class="desktop_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
                <img class="mobile_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
            </div>
            <div class="form_field">
                <div class="header_section"><p>設定密碼</p></div>
                <form action="{{ route('create_member') }}" method="POST">
                    @csrf
                    <div class="data_group">
                        <div class="label_part"><p>電子郵件</p></div>
                        <div class="input_part">
                            <input type="text" class="media_input width_100 readonly-bg" name="email"  value = "{{ $email }}" readonly>
                        </div>
                    </div>
                    <hr class="dash_line">
                    <div class="data_group">
                        <div class="label_part"><p>輸入密碼</p></div>
                        <div class="input_part">
                            <input type="password" class="media_input width_100" name="password" id='password' placeholder="請輸入密碼">
                            <button type="button" class="switch_input_btn">
                                <div class="icon_password hide_password active">
                                    <img src="{{ asset('asset/images/hide_password.svg') }}" alt="icon_eye">
                                </div>
                                <div class="icon_password show_password">
                                    <img src="{{ asset('asset/images/show_password.svg') }}" alt="icon_eye">
                                </div>     
                            </button>
                            <div class="update_status_hint" id="password_rule1">
                                <img id="password_rule1_warn_icon" src="{{ asset('asset/images/warn_password.svg') }}" alt="warn_logo" class="hidden">
                                <img id="password_rule1_success_icon" src="{{ asset('asset/images/check_password.svg') }}" alt="checked_logo" class="hidden">
                                <img id="password_rule1_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" alt="error_logo" class="hidden">
                                <p>包含一個大寫字母</p>
                            </div>
                            <div class="update_status_hint" id="password_rule2">
                                <img id="password_rule2_warn_icon" src="{{ asset('asset/images/warn_password.svg') }}" alt="warn_logo" class="hidden">
                                <img id="password_rule2_success_icon" src="{{ asset('asset/images/check_password.svg') }}" alt="checked_logo" class="hidden">
                                <img id="password_rule2_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" alt="error_logo" class="hidden">
                                <p>包含一個小寫字母</p>
                            </div>                            
                            <div class="update_status_hint" id="password_rule3">
                                <img id="password_rule3_warn_icon" src="{{ asset('asset/images/warn_password.svg') }}" alt="warn_logo" class="hidden">
                                <img id="password_rule3_success_icon" src="{{ asset('asset/images/check_password.svg') }}" alt="checked_logo" class="hidden">
                                <img id="password_rule3_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" alt="error_logo" class="hidden">
                                <p>包含一個數字</p>
                            </div> 
                            <div class="update_status_hint" id="password_rule4">
                                <img id="password_rule4_warn_icon" src="{{ asset('asset/images/warn_password.svg') }}" alt="warn_logo" class="hidden">
                                <img id="password_rule4_success_icon" src="{{ asset('asset/images/check_password.svg') }}" alt="checked_logo" class="hidden">
                                <img id="password_rule4_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" alt="error_logo" class="hidden">
                                <p>至少八個字元</p>
                            </div>        
                        </div>
                    </div>
                    <div class="data_group">
                        <div class="label_part"><p>再次輸入密碼</p></div>
                        <div class="input_part">
                            <input type="password" class="media_input width_100" name="password_confirmed" id ='confirmed_password' placeholder="請再次輸入密碼">
                            <button type="button" class="switch_input_btn">
                                <div class="icon_password hide_password active">
                                    <img src="{{ asset('asset/images/hide_password.svg') }}" alt="icon_eye">
                                </div>
                                <div class="icon_password show_password">
                                    <img src="{{ asset('asset/images/show_password.svg') }}" alt="icon_eye">
                                </div> 
                            </button>
                            <div class="update_status_hint" id="confirmed_password_hint">
                                <img id="confirmed_password_warn_icon" src="{{ asset('asset/images/warn_password.svg') }}" alt="warn_logo" class="hidden">
                                <img id="confirmed_password_success_icon" src="{{ asset('asset/images/check_password.svg') }}" alt="checked_logo" class="hidden">
                                <img id="confirmed_password_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" alt="error_logo" class="hidden">
                                <p></p> 
                            </div>
                        </div>                                 
                    </div>
                    <button type="submit" class="media_btn disable"><p>確認</p></button>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    const CSRF_TOKEN = $("input[name=_token]").val();
</script>

</html>