<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>完成變更</title>
    <link rel="icon" href="{{ asset('asset/images/favicon.svg') }}" type="image/svg+xml">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('asset/css/member_center.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/complete_confirm.css') }}">

</head>
<body>
    <div class="media_container">
        <div class="main_page">
            <div class="icon_field">
                <img class="desktop_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">
                <img class="mobile_logo" src="{{ asset('asset/images/logo.svg') }}" alt="logo">           
            </div>
            <div class="form_field">
                <div class="header_section">
                    <p>變更完成</p>
                    <p class="description">已完成變更！請重新登入</p>
                </div>
                <button type="button" class="media_btn"><p>確認</p></button>
            </div>
        </div>
    </div>
</body>
<script>

    const button = document.getElementsByClassName('media_btn')[0];
    button.addEventListener('click', function() {        
        window.location.href = "{{ route('login') }}";
    });

</script>