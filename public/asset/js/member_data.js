/** 
 * member_data.js : 
 * 處理「會員資料」頁面的載入，及表單送處
 * 
 */
import {CommonUI, Validator, Notify, ContactHintManager, MultiRuleHintManager, ButtonTimer, Poller} from './utilities.js';

(function() {

    const $usernameInput = $("#username");
    const $emailInput = $("#email");
    const $mobileInput = $("#mobile");
    const $addressInput = $("#address");
    const $NotifyClose = $(".send_verify_close"); 

    const $emailVerifyBtn = $("#email_btn");
    const $mobileVerifyBtn = $("#mobile_btn");
    const $saveBtn = $("#save_btn");

    const $emailVerifyBtnText = $emailVerifyBtn.find('p');
    const $mobileVerifyBtnText = $mobileVerifyBtn.find('p');

    const usernameRegexRule = [
        {regex: /^[A-Za-z]+(?: [A-Za-z]+)*$/, ruleId: 'username_rule1'},
        {regex: /^.{2,20}$/, ruleId: 'username_rule2' }
    ];


    // 初始化物件
    const init = () => {
        
        /**  初始化引入的模組  */

        ContactHintManager.injectSection('email', $("#email_hint"));
        ContactHintManager.injectSection('mobile', $("#mobile_hint")); 

        MultiRuleHintManager.injectRule('username_rule1', $("#username_hint_rule1"));
        MultiRuleHintManager.injectRule('username_rule2', $("#username_hint_rule2"));

        ButtonTimer.init( $emailVerifyBtn[0], 
                          $emailVerifyBtnText[0], { 
                          onStart: lockInput, onFinish: unlockInput
                        });


        /**  綁定監聽事件  */

        // 監聽「寄送通知的關閉按鈕」
        $NotifyClose.on("click", () => Notify.hide());

        // 更新「暱稱」的提示訊息
        $usernameInput.on('input', function(){
            updateUsernameHint($(this), usernameRegexRule);
        })

        // 更新「Email」的提示訊息
        $emailInput.on('input', updateEmailHint)

        // 更新「mobile」的提示訊息
        $mobileInput.on('input', updateMobileHint)

        // 更新「mobile」的提示訊息
        $addressInput.on('input', updateAddressHint)

        // 點擊「驗證」按鈕後, 觸發流程
        $emailVerifyBtn.on('click', handleUpdateEmail);


        // 提交表單
        $saveBtn.on('click', (event) => {
            if ($saveBtn.hasClass('disable'))  event.preventDefault();
            submitForm();
        })
    }

    // 處理重新導向的訊息
    const handleNotify = () => {
        if (ERROR_MESSAGE){
            Notify.show(ERROR_MESSAGE, "success");
            sessionStorage.removeItem('error');
        } 
    }

    // 渲染初始頁面
    const renderInitialHint = () => {

        // 處理訊息
        handleNotify();

        // 將從 blade 讀取下來的「會員資料（initialFormData)」載入到欄位之中
        initialFormDataArray.forEach(inputDisplay => {
            applyInitialSelect(inputDisplay.name, inputDisplay.value);
        });

        // 檢查「電子郵件」、「手機」欄位，更新提示
        updateEmailHint($("#email")); 

        // 更新「暱稱」的提示
        updateUsernameHint($usernameInput,usernameRegexRule);

        // 更新「儲存」按鈕
        toggleButtom();
    }

    // 更新「暱稱」是否符合格式
    const updateUsernameHint = ($input, rules) => {

        const username = $input.val().trim();
        MultiRuleHintManager.hideAll();
        let allRulesPassed = true;

        rules.forEach(rule => {

            const ruleId = rule.ruleId;
            const regex = rule.regex;

            if (regex.test(username)) {
                MultiRuleHintManager.update(ruleId, 'success');
            } else {
                if (username.length > 0) {
                    MultiRuleHintManager.update(ruleId, 'fail');
                } else {
                    MultiRuleHintManager.update(ruleId, 'warn');
                }
                allRulesPassed = false; 
            }
        })

        toggleButtom();
    }

    // 自動將資料填入選單
    const applyInitialSelect = (id, value) => {

        if (!value) return;
        const $targetOption = $(`.customer_option_item[data-option-id='${value}'`)
            .filter(function(){
                return $(this).closest(".customer_select_field").find(`#${id}`).length > 0;
            });
        
        if ($targetOption.length) $targetOption.trigger("click");
    }

    // 將輸入欄位「鎖定」
    const lockInput = () => {
        $emailInput.prop('readonly', true);
        $emailInput.addClass('readonly-bg');
    }

    // 將輸入欄位「解鎖」
    const unlockInput = () => {
        $emailInput.prop('readonly', false);
        $emailInput.removeClass('readonly-bg');
    }

    // 動態更新「手機號碼」的提示
    const updateMobileHint = () => {

        // -----------為未來預留的功能 ------
        $mobileVerifyBtn.addClass('disable'); 
        // -------------------------------

        const mobile = $mobileInput.val();

        const phone_code = $("#country").text();
        ContactHintManager.hide('mobile');

        if (mobile === '') {
            // ContactHintManager.update('mobile', "請輸入手機號碼。", "none");
        } else if (!Validator.isMobile(phone_code, mobile)) {
            ContactHintManager.update('mobile', "格式錯誤。", "fail");
        } else if (mobile === MOBILE) { 
            // ContactHintManager.update('mobile', "已驗證", "success");
        } else {
            // ContactHintManager.update('mobile', "請點擊驗證鈕，系統將寄送驗證碼。", "warn");
            $mobileVerifyBtn.removeClass('disable'); 
        }
        toggleButtom();
    }

    // 動態更新「電子郵件」的提示
    const updateEmailHint = () => {

        if (ButtonTimer.isRunning() || Poller.isRunning()) { 
            return; 
        }

        const email = $emailInput.val();
        ContactHintManager.hide('email');
        $emailVerifyBtn.addClass('disable');

        if (!Validator.isEmail(email)) {
            ContactHintManager.update('email', "格式錯誤", "fail"); 
        } else if (email === EMAIL) {
            ContactHintManager.update('email', "已驗證，電子郵件設定完成", "success"); 
        } else {
            ContactHintManager.update('email', "請點擊驗證鈕，系統將寄送變更通知信，需於信件中確認後完成變更", "warn");
            $emailVerifyBtn.removeClass('disable'); 
        }
        toggleButtom();
    }

    // 動態更新「地址」的提示
    const updateAddressHint = () => {

        const $addressHint = $("#address_hint");

        const isValid = (!$addressInput.val()) ? true : ( Validator.isAddress($addressInput.val()) );
        if (!isValid) {
            $("#address_fail_icon").removeClass("hidden");
            $addressHint.find("p").removeClass("hidden");
            $addressHint.addClass('fail');

        } else {
            $("#address_fail_icon").addClass("hidden");
            $addressHint.find("p").addClass("hidden");
            $addressHint.removeClass('fail');
        }
        toggleButtom();
    }
    
    // 「點擊」驗證按鈕後的流程
    const handleUpdateEmail = async () => {

        const newEmail = $emailInput.val();
        if ($emailVerifyBtn.hasClass('disable')) return;

        const url = '/update_email';
        const requestData = {
            email: newEmail
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
            })

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
            if (res.code === 200) {
                Poller.BEARER_TOKEN = BEARER_TOKEN;
                Poller.start(newEmail, {
                    onPending: (message) => {
                        ContactHintManager.update('email', message, "warn" );
                    },
                    onVerified: (message) => {
                        ContactHintManager.update('email', message, "success" );  
                        ButtonTimer.stop();
                        EMAIL = newEmail;
                        $emailVerifyBtn.addClass('disable'); 
                        toggleButtom();
                    },
                    onCanceled: (message) => {
                        ContactHintManager.update('email', message, "success");
                        ButtonTimer.stop();
                        $emailInput.val(EMAIL);
                        $emailVerifyBtn.addClass('disable'); 
                        toggleButtom();                         
                    },
                    onExpired: (message) => {
                        ContactHintManager.update('email', message, "warn");
                        ButtonTimer.stop(); 
                    },
                    onError: (message) => {
                        ContactHintManager.update('email', message, "fail");
                        ButtonTimer.stop(); 
                    }
                })
            } else {
                ContactHintManager.update('email', res.message, "fail");
                ButtonTimer.stop(); 
            }            
        }
        catch (error){
            console.log("發送更新 Email 請求失敗:", error);
            ButtonTimer.stop();             
        }
    }

    // 更新「儲存」按鈕狀態
    const toggleButtom = () => {
        
        const isEmailValid = Validator.isHintValid($("#email_hint"));
        const isMobileValid = Validator.isHintValid($("#mobile_hint"));
        const isAddressValid = Validator.isHintValid($("#address_hint"));
        const isUsernameValid = Validator.isHintValid($("#username_hint_rule1")) 
                             && Validator.isHintValid($("#username_hint_rule2"));
                             
        if (!isUsernameValid || !isEmailValid || !isMobileValid || !isAddressValid)  $saveBtn.addClass('disable');
        else $saveBtn.removeClass('disable');
    }

    // 「點擊」儲存按鈕後提交表單
    const submitForm = async () => {

        //  "{{ route('edit_member_data') }}"
        const url = '/edit_member_data';
        const formData = {

            username: $usernameInput.val().trim(),
            gender: $("#hidden_gender").val(),
            age_group: $("#hidden_age").val(),
            country: $("#hidden_country").val(),
            mobile: $mobileInput.val(),
            email: $emailInput.val(),
            address: $addressInput.val().trim()

        };

        try{
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": CSRF_TOKEN,
                    "Accept": "application/json"
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const errorData = await response.json().catch( () => ({
                        message: `HTTP 錯誤! 狀態碼: ${response.status}`
                    }));
                const errorMessage = errorData.message || errorData.error   || 
                                     `未知伺服器錯誤 (狀態碼: ${response.status})`;
                console.log(errorMessage);
                return;
            }

            const res = await response.json();
            if (res.code === 200) {
                window.location.href = res.url;
            } else {
                Notify.show(res.message, "error");
            }

        } catch (err) {
            console.error("AJAX 更新失敗:", err);
        }
    }

    $(function(){

        // 使用通用 UI 互動模組
        CommonUI.init();

        // 初始化物件
        init();

        // 渲染頁面載入時的訊息提示
        renderInitialHint();
    })
})();