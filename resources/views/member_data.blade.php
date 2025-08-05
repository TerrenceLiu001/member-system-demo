<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>帳戶設定</title>
    <link rel="icon" href="{{ asset('asset/images/favicon.svg') }}" type="image/svg+xml">

    {{-- jquery 3.6.0 --}}
    <script src="{{ asset('asset/package/jquery.min.js') }}"></script> 

    {{-- bootstrap 4.0.0 --}}
    <script src="{{ asset('asset/package/popper.min.js') }}"></script>
    <script src="{{ asset('asset/package/bootstrap.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('asset/package/bootstrap.min.css') }}">
   

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('asset/css/member_center.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/member_data.css') }}">

    {{-- js --}}
    <script type="module" src="{{ asset('asset/js/utilities.js') }}" defer></script>
    <script type="module" src="{{ asset('asset/js/member_data.js') }}" defer></script>

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
            <hr class="dash_line">
            <div class="form_field">
                <div class="header_section">
                    <img src="{{ asset('asset/images/back.svg') }}" alt="back" onclick="history.back();" >
                    <p>帳戶設定</p>
                </div>
                <form >
                    @csrf
                    <div class="data_group">
                        <div class="label_part"><p>暱稱 (必填)</p></div>
                        <div class="input_part">
                            <input type="text" class="media_input width_100" name="username" id="username" value="{{ $user->username ?? ''}}" placeholder="請輸入暱稱">
                            <div class="update_status_hint" id="username_hint_rule1">
                                <img id="username_rule1_warn_icon" src="{{ asset('asset/images/warn_password.svg') }}" alt="warn_logo" class="hidden">
                                <img id="username_rule1_success_icon" src="{{ asset('asset/images/check_password.svg') }}" alt="checked_logo" class="hidden">
                                <img id="username_rule1_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" alt="error_logo" class="hidden">
                                <p>限輸入英文，單字間隔至多一個空白</p>
                            </div>
                            <div class="update_status_hint" id="username_hint_rule2">
                                <img id="username_rule2_warn_icon" src="{{ asset('asset/images/warn_password.svg') }}" alt="warn_logo" class="hidden">
                                <img id="username_rule2_success_icon" src="{{ asset('asset/images/check_password.svg') }}" alt="checked_logo" class="hidden">
                                <img id="username_rule2_fail_icon" src="{{ asset('asset/images/error_password.svg') }}" alt="error_logo" class="hidden">
                                <p>長度為 2 到 20 字元</p>
                            </div>
                        </div>
                    </div>
                    <div class="two_column_field">
                        <div class="data_group">
                            <div class="label_part"><p>性別</p></div>
                            <div class="input_part">
                                <div class="customer_select_field width_100">
                                    <button type="button" class="dropdown-toggle media_custom_select" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <p class="preview_text change_text" data-select-value="" id="gender">請選擇性別</p>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end media_custom_option" aria-labelledby="dropdownMenuButton">
                                        <a class="customer_option_item" data-option-id="male">
                                            <p class="country_name text_target">男性</p>
                                        </a>
                                        <a class="customer_option_item" data-option-id="female">
                                            <p class="country_name text_target">女性</p>
                                        </a>
                                        <a class="customer_option_item" data-option-id="unknown">
                                            <p class="country_name text_target">未知</p>
                                        </a>
                                    </div>
                                </div>
                                <input type="hidden" name="gender" id="hidden_gender" value ="{{ $user->gender ?? '' }}">
                            </div>
                        </div>
                        <div class="data_group">
                            <div class="label_part"><p>年齡</p></div>
                            <div class="input_part">
                                <div class="customer_select_field width_100">
                                    <button type="button" class="dropdown-toggle media_custom_select" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <p class="preview_text change_text" data-select-value="" id="age_group">請選擇年齡</p>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end media_custom_option" aria-labelledby="dropdownMenuButton">
                                        <a class="customer_option_item" data-option-id="under_20">
                                            <p class="country_name text_target">20歲以下</p>
                                        </a>
                                        <a class="customer_option_item" data-option-id="between_21_30">
                                            <p class="country_name text_target">21-30歲</p>
                                        </a>
                                        <a class="customer_option_item" data-option-id="between_31_40">
                                            <p class="country_name text_target">31-40歲</p>
                                        </a>
                                        <a class="customer_option_item" data-option-id="between_41_50">
                                            <p class="country_name text_target">41-50歲</p>
                                        </a>
                                        <a class="customer_option_item" data-option-id="between_51_60">
                                            <p class="country_name text_target">51-60歲</p>
                                        </a>
                                        <a class="customer_option_item" data-option-id="below_61">
                                            <p class="country_name text_target">61歲以上</p>
                                        </a>
                                    </div>
                                </div>
                                <input type="hidden" name="age_group" id="hidden_age" value ="{{ $user->age_group ?? '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="data_group">
                        <div class="label_part"><p>電子郵件</p></div>
                        <div class="input_part">
                            <div class=" input_include_verify">
                                <input type="text" class="media_input width_100" name='email' id="email" value="{{ $user->email ?? '' }}" placeholder="請輸入電子郵件">
                                <button type="button" class="media_btn verify_type disable" id="email_btn"><p>驗證</p></button> 
                            </div>
                            <div class="update_status_hint" id="email_hint">
                                <img id="email_warn_icon" class="hidden" src="{{ asset('asset/images/warn_password.svg') }}"  alt="warn_logo">
                                <img id="email_success_icon" class="hidden" src="{{ asset('asset/images/check_password.svg') }}" alt="success_logo">
                                <img id="email_fail_icon" class="hidden" src="{{ asset('asset/images/error_password.svg') }}" alt="fail_logo">
                                <p></p>
                            </div>
                        </div>
                    </div>
                    <div class="data_group">
                        <div class="label_part"><p>手機號碼</p></div>
                        <div class="input_part">
                            <div class="account_field phone_input width_100">
                                <div class="customer_select_field phone_code width_100">
                                    <button type="button" class="dropdown-toggle media_custom_select" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <p class="change_text" data-select-value="TWN" id="country">+886</p>                                    
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
                                <input type="hidden" name="country" id="hidden_country" value ="{{ $user->country ?? 'TWN' }}">
                                <input type="text" class="media_input width_100" name='mobile' id="mobile" value="{{ $user->mobile ?? '' }}"placeholder="請輸入手機號碼">
                            </div>
                            <div class="update_status_hint" id="mobile_hint">
                                <img id="mobile_warn_icon" class="hidden" src="{{ asset('asset/images/warn_password.svg') }}"  alt="warn_logo">
                                <img id="mobile_success_icon" class="hidden" src="{{ asset('asset/images/check_password.svg') }}" alt="success_logo">
                                <img id="mobile_fail_icon" class="hidden" src="{{ asset('asset/images/error_password.svg') }}" alt="fail_logo">
                                <p></p>
                            </div>
                        </div>
                    </div>
                    <div class="data_group">
                        <div class="label_part"><p>地址</p></div>
                        <div class="input_part">
                            <input type="text" class="media_input width_100" name="address" id="address" value="{{ $user->address ?? '' }}" placeholder="請輸入地址">
                            <div class="update_status_hint" id="address_hint">
                                <img id="address_fail_icon" class="hidden" src="{{ asset('asset/images/error_password.svg') }}" alt="fail_logo">
                                <p class="hidden" >只能使用中文、英文、空白、數字、點 (.)、連字號 (-)</p>
                            </div>
                        </div>
                    </div>
                    <button id="save_btn" type = "button" class="media_btn disable width_280 margin_right_auto margin_left_auto margin_top_16">
                        <p>儲存</p>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // const ERROR_MESSAGE   = "{{ session('error') }}";
        const ERROR_MESSAGE   = sessionStorage.getItem("error");
        const CSRF_TOKEN = $("input[name=_token]").val();
        const BEARER_TOKEN = "{{ $user->bearer_token }}";
        const initialFormDataArray = [
            { name: 'gender', value: "{{ $user->gender ?? '' }}" },
            { name: 'age_group', value: "{{ $user->age_group ?? '' }}" },
            { name: 'country', value: "{{ $user->country ?? '' }}" }
        ];
        let EMAIL =  "{{ $user->email }}" 
        let MOBILE = "{{ $user->mobile ?? '' }}"
    </script>
    
</body>
</html>