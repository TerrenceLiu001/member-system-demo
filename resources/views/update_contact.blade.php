<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>變更確認</title>
    <link rel="icon" href="{{ asset('asset/images/favicon.svg') }}" type="image/svg+xml">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('asset/css/member_center.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/update_contact.css') }}">
    
</head>
<body>
    <div class="media_container">  
        <div class="main_page">
            <div class="icon_field">
                <img class="desktop_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
                <img class="mobile_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
            </div>
            <div class="form_field">
                <div class="header_section"><p>變更確認</p></div>
                <hr class="dash_line">
                <div class="info_field">
                    <p>確認是否將電子郵件從</p>
                    <p><span class="origin">{{ $email }}</span>變更為</p>
                    <p><span class="update">{{ $new_contact }}</span></p>
                </div>
                 <hr class="dash_line">
            </div>
            <div class="btn_field">
                <form method="POST" action="{{ route('cancel_confirm') }}">
                    @csrf 
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button type="submit" class="media_btn cancel "><p>取消變更</p></button>
                </form>
                <form method="POST" action="{{ route('update_confirm') }}">
                    @csrf 
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button type="submit" class="media_btn"><p>確認</p></button>
                </form>
            </div>
        </div>
    </div>
</body>