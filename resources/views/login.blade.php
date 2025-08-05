<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>登入註冊頁面</title>
    <link rel="icon" href="{{ asset('asset/images/favicon.svg') }}" type="image/svg+xml">

    {{-- jquery 3.6.0 --}}
    <script src="{{ asset('asset/package/jquery.min.js') }}"></script>

    {{-- bootstrap 4.0.0 --}}
    <script src="{{ asset('asset/package/popper.min.js') }}"></script>
    <script src="{{ asset('asset/package/bootstrap.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('asset/package/bootstrap.min.css') }}">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('asset/css/member_center.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/login.css') }}">

    {{-- js --}}
    <script type="module" src="{{ asset('asset/js/utilities.js') }}" defer></script>
    <script type="module" src="{{ asset('asset/js/login.js') }}" defer></script>

</head>

<body>
    <div class="media_container">
        <div class="send_verify_field">
            <div class="send_verify_message">
                <img class="check_mark" src="{{ asset('asset/images/check_mark.svg') }}">
                <img class="error_mark hidden" src="{{ asset('asset/images/error_mark.svg') }}">
                <p></p>
                <img class="send_verify_close" src="{{ asset('asset/images/send_verify_close.svg') }}">
            </div>
        </div>
        <div class="main_page">
            <div class="icon_field">
                <img class="desktop_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
                <img class="mobile_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
            </div> 
            <div class="form_field">
                <div class="login_field">
                    <div class="header_section">
                        <p>登入</p>
                    </div>
                    <form>
                        <div class="data_group">
                            <div class="label_part">
                                <div class="radio_part">
                                    <label><input type="radio" name="login_type" class="media_input" id="mail" checked>電子郵件</label>
                                </div>
                                <div class="radio_part">
                                    <label><input type="radio" name="login_type" class="media_input" id="phone">手機號碼</label>
                                </div>
                            </div>
                            <div class="input_part flex_direction_column">
                                <div class="radio_switch_field">
                                    <div class="account_field mail_input active" data-type="mail">
                                        <input type="email" class="media_input width_100" id="login_email" placeholder="請輸入電子郵件">
                                    </div>
                                    <div class="account_field phone_input" data-type="phone">
                                        <div class="customer_select_field phone_code">
                                            <button type="button" class="dropdown-toggle media_custom_select" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <p class="change_text" data-select-value="TWN">+886</p>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end media_custom_option" aria-labelledby="dropdownMenuButton">
                                                <a class="customer_option_item" data-option-id="TWN">
                                                    <p class="country_name">台灣</p>
                                                    <p class="phone_identifier_code text_target">+886</p>
                                                </a>
                                                <a class="customer_option_item" data-option-id="JPN">
                                                    <p class="country_name">日本</p>
                                                    <p class="phone_identifier_code text_target">+81</p>
                                                </a>
                                                <a class="customer_option_item" data-option-id="KOR">
                                                    <p class="country_name">南韓</p>
                                                    <p class="phone_identifier_code text_target">+82</p>
                                                </a>                                                                                                    
                                            </div>
                                        </div>                                        
                                        <input type="text" class="media_input width_100" id="login_phone" placeholder="請輸入手機號碼">
                                    </div>
                                </div> 
                                <div class="error_message_field fail hidden width_100" id="login_account_error">
                                    <img id="register_hint_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" width="16" height="20" alt="fail_logo">
                                    <p></p>
                                </div>
                            </div>
                        </div>
                        <div class="data_group">
                            <div class="label_part"><p>密碼</p></div>
                            <div class="input_part">
                                <input type="password" class="media_input width_100" name="password" placeholder="請輸入密碼">
                                <button type="button" class="switch_input_btn">
                                    <div class="icon_password hide_password active">
                                        <img src="{{ asset('asset/images/hide_password.svg') }}" alt="icon_eye">
                                    </div>
                                    <div class="icon_password show_password">
                                        <img src="{{ asset('asset/images/show_password.svg') }}" alt="icon_eye">
                                    </div>                                    
                                </button>
                            </div>
                            <div class="other_part">
                                <div class="error_message_field hidden fail" id="login_password_error">
                                    <img id="register_hint_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" width="16" height="20" alt="fail_logo">
                                    <p></p>
                                </div>
                                <div></div>
                                <div class="forget_password_part">
                                    <a href="{{ route('forgot_password') }}">忘記密碼</a>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="media_btn login_btn"><p>登入</p></button>
                        <div class="error_message_field hidden fail" id="login_general_error">
                            <img id="register_hint_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" width="16" height="20" alt="fail_logo">
                            <p></p>
                        </div>
                    </form>
                </div>
                <hr class="dash_line">
                <div class="register_field">
                    <div class="header_section"><p>註冊</p></div>
                    <div class="data_group">
                        <div class="label_part"><p>電子郵件</p></div>
                        <div class="input_part">
                            <div class="radio_switch_field concurrent">
                                <div class="account_field mail_input active" data-type="mail">
                                    <input type="email" class="media_input width_100" id="register_email" placeholder="請輸入電子郵件">
                                </div>
                            </div>
                            <button type="button" class="media_btn register_btn"><p>確認</p></button>
                        </div>
                        <div class="error_message_field register_hint_container hidden">
                            <img id="register_hint_warn_icon" class ='hidden' src="{{ asset('asset/images/warn_password.svg') }}" width="16" height="20" alt="warn_logo">
                            <img id="register_hint_fail_icon" class ='hidden' src="{{ asset('asset/images/error_password.svg') }}" width="16" height="20" alt="fail_logo">
                            <p></p> 
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>

    <script>

        const SUCCESS_MESSAGE = "{{ session('success') }}";
        const ERROR_MESSAGE   = "{{ session('error')   }}";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    </script>
</body>
</html>