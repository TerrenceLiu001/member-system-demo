/*
 * login.js - 負責處理登入/註冊頁面
 *
 */

// 使用 IIFE 包裹，避免全域變數污染
import {CommonUI, Validator, Notify, ContactHintManager, ButtonTimer} from './utilities.js';
(function() {

    const $registerButton = $(".register_btn");
    const $registerButtonText = $registerButton.find('p');
    const $registerEmailInput = $("#register_email");
    const REGISTER_PAUSE_TIME = 10;

    // 處理載入頁面時的訊息
    function handleNotify() {
        if (ERROR_MESSAGE) Notify.show(ERROR_MESSAGE, "error");
        else if (SUCCESS_MESSAGE) Notify.show(SUCCESS_MESSAGE, "success");
    }

    // 將錯誤訊息顯示在指定的欄位中。
    function displayError(message, $container) {
        $container.removeClass('hidden').find('p').text(message);
    }


    // 隱藏所有錯誤訊息欄位
    function hideAllErrors() {
        $(".error_message_field").addClass('hidden');
    }

    // 呼叫後端 
    async function callApi(url, data, method = 'POST') {

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN 
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) throw new Error('網路回應異常');
            return await response.json();

        } catch (error) {
            console.error('API 請求層錯誤:', error);
            throw error;
        }
    }

    // 檢查登入時的資料格式
    function getLoginInput(){

        hideAllErrors();

        const accountType = $("input[type=radio]:checked").attr("id");
        let account = "";
        let phone_identifier_code = "";
        let isValid = true;

        switch (accountType){
            case "mail":
                account = $("#login_email").val(); 
                if (!Validator.isEmail(account)){
                    displayError("格式錯誤", $("#login_account_error"));
                    isValid = false;
                    return;
                }
                break;

            case "phone":
                account = $("#login_phone").val();
                phone_identifier_code = $(".login_field .change_text").text();
                
                if (!Validator.isMobile(phone_identifier_code, account)){
                    displayError("格式錯誤", $("#login_account_error"));
                    isValid = false;
                    return;
                }
                if (!account.startsWith(0)) account = '0'+ account;
                break;
        }

        const password = $("input[name=password]").val();
        if (password === ''){
            displayError("請輸入密碼", $("#login_password_error"));
            isValid = false;
        } 
        
        return (!isValid)? null 
                         : { account: account, password: password, phone_identifier_code: phone_identifier_code };  
    }

    // 處理「登入」流程
    async function login(){

        const $errorHint = $("#login_general_error"); 
        const requestData = getLoginInput();

        if (!requestData) return;
        
        try {
            const res = await callApi('login_run', requestData);

            if (res.code === '302') {
                sessionStorage.setItem("error", res.message); 
                window.location.href = res.url;
                return;
            }
            
            if (res.code === '200') {
                window.location.href = res.url;
                return;
            }
            
            displayError(res.message, $errorHint);

        } catch (error) {
            console.error('Err: ', error);
             displayError('網路錯誤', $errorHint); 
        }
    }

    // 處理「註冊」流程
    async function register() {

        hideAllErrors();    

        const account = $registerEmailInput.val();
        if (!Validator.isEmail(account)) {
            ContactHintManager.update('register_hint','格式錯誤', 'fail');
            return; 
        }

        const url = 'register_run';
        const requestData = {
            account: account
        };

        // 計時器
        ButtonTimer.start(REGISTER_PAUSE_TIME);
        ContactHintManager.update('register_hint','處理中，請耐心等候', 'warn');

        try {
            const res = await callApi(url, requestData);

            if (res.code === 200) {
                Notify.show(res.message, "success");
                ContactHintManager.hide('register_hint');
            } else {
                ContactHintManager.update('register_hint', res.message, 'fail');
                ButtonTimer.stop();
            }
        } catch (error) {

            console.error("註冊請求失敗:", error);
            ContactHintManager.update('register_hint', '註冊請求失敗，請稍後再試', 'fail');
            ButtonTimer.stop();
        }
    }
    
    $(function() { 

        ButtonTimer.init($registerButton[0], $registerButtonText[0]);
        ContactHintManager.injectSection('register_hint', $(".register_hint_container"));

        // 顯示載入頁面時的提示
        handleNotify();

        // 使用通用 UI 互動模組
        CommonUI.init();

        // 監聽「寄送通知的關閉按鈕」
        $(".send_verify_close").on("click", () => Notify.hide());

        // 監聽「註冊」按鈕的點擊事件
        $(".register_btn").on("click", function() {
            if (!$(this).hasClass('disable')) register();    
        });

        $(".login_btn").on("click", function() {
            login();
        });
    }); 
})(); 