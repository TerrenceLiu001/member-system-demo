/** 
 * forgot_password.js : 
 * 處理「忘記密碼」頁面的載入，及表單送處
 * 
 */

import {Validator, ContactHintManager, ButtonTimer} from './utilities.js';

(function() {

    const $emailInput = $("#email");
    const $resetButton = $('.media_btn');
    const $timeSelector = $(".btn_field").find('span');
    const $resendLink = $('#resend_link');

    const init = () => {

        // 初始化計時器
        ButtonTimer.init( $resetButton.get(0), $timeSelector.get(0), { 
                            onStart: handleStart, 
                            onFinish: handleFinish
                        });

        // 初始化訊息提示
        ContactHintManager.injectSection('email', $("#email_hint"));

        // 顯示初始提示
        ContactHintManager.update('email', "請輸入您註冊時的電子信箱", "warn");

        // 綁定 Input 的監聽
        $emailInput.on('input', updateEmailHint);

        // 點擊「驗證」按鈕後, 觸發流程
        $resetButton.on('click', handleResetPassword);

        $resendLink.on('click', function (e) {
            e.preventDefault(); 
            if ($resetButton.hasClass('disable')) return;
            handleResetPassword();
        });    
    }

    // 更新「email」的提示訊息
    const updateEmailHint = () => {

        if ( ButtonTimer.isRunning())  return; 

        const email = $emailInput.val();
        ContactHintManager.hide('email');
        $resetButton.addClass('disable');

        if (email == '') {
            ContactHintManager.update('email', "請輸入您註冊時的電子信箱", "warn"); 
        } else if (!Validator.isEmail(email)) {
            ContactHintManager.update('email', "格式錯誤", "fail"); 
        } else {
            ContactHintManager.update('email', "格式正確，確認後請點擊送出", "warn");
            $resetButton.removeClass('disable'); 
        }
    }

    // 點擊「完成」按鈕
    const handleResetPassword = async () => {

        const email = $emailInput.val();
        if ($resetButton.hasClass('disable')) return;

        const url = '/forgot_password_run';
        const requestData = {
            email: email
        };

        ButtonTimer.start(15);
        ContactHintManager.update('email', '處理中，請稍候', "warn" );

        try {
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    'Content-type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify(requestData)
            }); 
            
            if (!response.ok) {
                const errorData = await response.json().catch( () => ({
                    message: `HTTP 錯誤! 狀態碼: ${response.status}`
                }));
                const errorMessage = errorData.message || errorData.error   || 
                                    `未知伺服器錯誤 (狀態碼: ${response.status})`;
                ContactHintManager.update('email', `發送失敗: ${errorMessage}`, 'fail');
                ButtonTimer.stop();
                return;
            }
            const res = await response.json();
            console.log(res);
            if (res.code === 200) {
                ContactHintManager.update('email', '信件已寄出，請前去信箱完成密碼變更', 'success');
            } else {
                ButtonTimer.stop(); 
                 ContactHintManager.update('email', res.message, "fail");
            }
        }
        catch (error) {
            console.log("請求失敗: ", error);
            ButtonTimer.stop(); 
            ContactHintManager.update('email', '系統忙碌中', "fail");
        }
    }

    // 計時器的 callback functin
    const handleStart = () => {
        $resetButton.addClass('disable');
    };

    // 計時器的 callback functin
    const handleFinish = () => {
        updateEmailHint();
    };

    $(function(){

        init();
        
    })


})();