// utilities.js

// 檢查「欄位」是否符合規則
export const Validator = {

    isEmail(email) {
        const regex = /^[\w.\-]+@([a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,7}$/;
        return regex.test(email);
    },

    isMobile(phone_identifier_code, mobile) {

        switch (phone_identifier_code){
            case "+886":
                return  /^(0?9\d{8})$/.test(mobile);
            case "+81":
                return /^0?(70|80|90)\d{8}$/.test(mobile);
            case "+82":
                return /^(0?1[016789]\d{8})$/.test(mobile);
        }
    },

    isAddress(address) {
        const regex = /^[\p{L}0-9\s.\-]+$/u;
        return regex.test(address);
    },

    isHintValid($hintSelector) {
        
        return ($hintSelector.hasClass('success')) 
                ? true : ($hintSelector.hasClass('fail'))
                    ? false : ($hintSelector.hasClass('warn'))
                        ? false : true;
    } 
}

// 導向其它頁面時，處理紀錄在 Session 裡關於「成功」或「錯誤」的訊息
export const Notify = {

    $container: $(".send_verify_message"),
    $messageText: $(".send_verify_message p"),
    $checkMark: $(".check_mark"),
    $errorMark: $(".error_mark"),

    show(message, type) {

        this.$messageText.text(message);
        this.$container.removeClass("to_fadeIn invalid_link");

        const isError = (type === "error");
        this.$container.toggleClass("invalid_link", isError);
        this.$container.addClass("to_fadeIn");

        this.$checkMark.toggleClass("hidden", isError);
        this.$errorMark.toggleClass("hidden", !isError);

    },

    hide() {
        this.$container.css({ opacity: 0 });
        this.$container.removeClass("to_fadeIn invalid_link");
    }
}

// 處理「通訊變更」的提示欄
export const ContactHintManager = {

    _hintGroup: {},

    injectSection(groupId, $containerDiv) {

        if ( !groupId || !$containerDiv || $containerDiv.length === 0 ) return;        
        this._hintGroup[groupId] = {

            $container:   $containerDiv,
            $iconWarn:    $containerDiv.find(`#${groupId}_warn_icon`),
            $iconSuccess: $containerDiv.find(`#${groupId}_success_icon`),
            $iconFail:    $containerDiv.find(`#${groupId}_fail_icon`),
            $text: $containerDiv.find("p")
        };

        this.hide(groupId);
    },

    update(groupId, message, type){

        const group = this._hintGroup[groupId];
        if (!group) return;

        group.$iconWarn.addClass('hidden');
        group.$iconSuccess.addClass('hidden');
        group.$iconFail.addClass('hidden');

        group.$container.removeClass('success warn fail');

        switch (type) {
            case 'warn':
                group.$iconWarn.removeClass('hidden');
                group.$container.addClass('warn');
                break;
            case 'success':
                group.$iconSuccess.removeClass('hidden');
                group.$container.addClass('success');
                break;
            case 'fail':
                group.$iconFail.removeClass('hidden');
                group.$container.addClass('fail');
                break;
            case 'none':
            default:
                break;    
        }

        group.$text.text(message);
        group.$container.removeClass('hidden');
    },

    hide(groupId) {

        const group = this._hintGroup[groupId];
        if (!group) return;

        group.$container.addClass('hidden');
        group.$text.empty();
        group.$iconWarn.addClass('hidden');
        group.$iconSuccess.addClass('hidden');
        group.$iconFail.addClass('hidden');
        group.$container.removeClass('success warn fail');

    }
}

// 處理「多條規則提示」的欄位 (例如暱稱、密碼)
export const MultiRuleHintManager = {

    _hintRules: {},

    injectRule(ruleId, $containerDiv) {
        if (!ruleId || !$containerDiv || $containerDiv.length === 0) return;

        this._hintRules[ruleId] = {
            $container:   $containerDiv,
            $iconWarn:    $containerDiv.find(`#${ruleId}_warn_icon`),
            $iconSuccess: $containerDiv.find(`#${ruleId}_success_icon`),
            $iconFail:    $containerDiv.find(`#${ruleId}_fail_icon`),
            $text:        $containerDiv.find("p")
        };

        this.hide(ruleId);
    },

    update(ruleId, type) {

        const rule = this._hintRules[ruleId];
        if (!rule) return;

        rule.$iconWarn.addClass('hidden');
        rule.$iconSuccess.addClass('hidden');
        rule.$iconFail.addClass('hidden');

        rule.$container.removeClass('success warn fail');

        switch (type) {
            case 'warn':
                rule.$iconWarn.removeClass('hidden');
                rule.$container.addClass('warn');
                break;
            case 'success':
                rule.$iconSuccess.removeClass('hidden');
                rule.$container.addClass('success');
                break;
            case 'fail':
                rule.$iconFail.removeClass('hidden');
                rule.$container.addClass('fail');
                break;
            case 'none': // 只顯示文字，無圖標無特定顏色
            default:
                break;
        }

        rule.$container.removeClass('hidden');
    },

    hide(ruleId) {

        const rule = this._hintRules[ruleId];
        if (!rule) return;

        rule.$container.addClass('hidden');
        rule.$iconWarn.addClass('hidden');
        rule.$iconSuccess.addClass('hidden');
        rule.$iconFail.addClass('hidden');
        rule.$container.removeClass('success warn fail');

    },

    hideAll() {
        for (const ruleId in this._hintRules) {
            if (Object.hasOwnProperty.call(this._hintRules, ruleId)) {
                this.hide(ruleId);
            }
        }
    }
}

// 通用 UI 互動模組
export const CommonUI = {

    // 初始化，綁定監聽事件
    init() {
        
        // 監聽自定義下拉選單選項的點擊事件
        $(document).on("click", ".customer_option_item", function() {
            CommonUI.updateDropdownContent($(this)); 
        });

        // 監聽密碼顯示/隱藏切換按鈕的點擊事件
        $(".switch_input_btn").on("click", CommonUI.switchPasswordVisibility);

        // 監聽帳戶類型選擇 Radio Button 的點擊事件
        $("input[type=radio]").on("click", function () {
            CommonUI.switchInputAccount($(this)); 
        });
    },

    // 更新「下拉選單」
    updateDropdownContent($selectedItem) {
        const $root = $selectedItem.closest(".customer_select_field");
        const optionId = $selectedItem.data("optionId");
    
        const $targetDisplay = $root.find(".change_text");
        const displayContent = $selectedItem.find(".text_target").text();

        $targetDisplay.data("selectValue", optionId);
        $targetDisplay.attr("data-select-value", optionId); 
        $targetDisplay.text(displayContent);

        if ($targetDisplay.hasClass("preview_text")) {
            $targetDisplay.removeClass("preview_text");
        }

        const $hiddenInput = $selectedItem.closest(".input_part").find("input[type='hidden']");
        if ($hiddenInput.length > 0) $hiddenInput.val(optionId);
    },

    /// 依選項，切換「帳戶」類別
    switchInputAccount($clickedRadio) {
        const accountType = $clickedRadio.attr("id"); 
        const $accountFields = $clickedRadio.closest(".data_group").find(".radio_switch_field .account_field");

        // 遍歷所有帳戶輸入框，根據類型切換 active 類別
        $accountFields.each(function () {
            const $thisField = $(this); 
            if ($thisField.data("type") === accountType) {
                $thisField.addClass("active"); 
            } else {
                $thisField.removeClass("active"); 
            }
        });
    },

    switchPasswordVisibility($clickedBtn) {
        
        const $passwordInput = $(this).closest(".input_part").find("input[type='password'], input[type='text']");
        const $passwordIcon = $(this).find(".icon_password");

        // 切換輸入框的類型 (password <-> text)
        if ($passwordInput.prop("type") === "password") {
            $passwordInput.prop("type", "text");
        } else {
            $passwordInput.prop("type", "password");
        }

        // 切換圖示的 active 類別，以改變其顯示狀態 (例如：眼睛閉合 <-> 眼睛張開)
        $passwordIcon.each(function() {
            $(this).toggleClass("active");  
        });
    }
}

// 倒數計時器
export const ButtonTimer = {

    _timerId: null,
    _buttonElement: null,
    _textElement: null,
    _originalText: '',
    _duration: 0,
    _timeLeft: 0,
    _callbacks: {
        onStart: null,
        onFinish: null
    },


    // 初始化計時器
    init($buttonElement, $textElement, callbacks = {}) {

        this._buttonElement = $buttonElement;
        this._textElement = $textElement || $buttonElement; 
        this._originalText = this._textElement.textContent; 

        this._callbacks = {
            onStart: callbacks.onStart || (() => {}), 
            onFinish: callbacks.onFinish || (() => {})
        }
    },

    start(durationSeconds) {

        if (!this._buttonElement) {
            console.log("ButtonTimer: 按鈕選擇器未被初始");
            return;
        }

        this.stop();
        this._callbacks.onStart();

        this._duration = durationSeconds;
        this._timeLeft = durationSeconds;

        this._buttonElement.classList.add('disable');
        this._textElement.textContent = `${this._timeLeft}s`; 

        this._timerId = setInterval(() => {
            this._timeLeft--;

            if (this._timeLeft > 0) {
                this._textElement.textContent = `${this._timeLeft}s`;
            } else {
                this.stop();
            }
        }, 1000); 
    },

    stop() {
        if (this._timerId) {
            clearInterval(this._timerId);
            this._timerId = null;
        }
        if (this._buttonElement) {
            this._buttonElement.classList.remove('disable'); 
            this._textElement.textContent = this._originalText; 
        }
        this._callbacks.onFinish();
    },

    isRunning() {
        return this._timerId !== null;
    }
}

// Polling
export const Poller = {

    // ========= 內部狀態 =========

    _pollingIntervalId  : null,
    _currentAttempt     : 0,
    _userAccount        : null,
    _lastStartTime      : 0,

    _callbacks: {
        onVerified  : null,
        onCanceled  : null,
        onPending   : null,
        onExpired   : null,
        onError     : null
    },
    

    // ========= 設定參數 =========
    BEARER_TOKEN         : null,
    INTERVAL_MS          : 5000,
    MAX_ATTEMPTS         : 10,
    RESET_COOLDOWN_MS    : 10 * 1000,
    URL: '/api/contact/update/status',

    // ========= 啟動輪詢 =========
    async start(userAccount, callbacks = {}, options = {}) {
        const now = Date.now();
        if (this._pollingIntervalId && (now - this._lastStartTime < this.RESET_COOLDOWN_MS)){
            return;
        }

        this.stop();
        this._lastStartTime  = now;
        this._userAccount    = userAccount;
        this._currentAttempt = 0;
        
        this._callbacks = {
            onVerified: callbacks.onVerified || (() => {}),
            onCanceled: callbacks.onCanceled || (() => {}),
            onPending: callbacks.onPending || (() => {}),
            onExpired: callbacks.onExpired || (() => {}),
            onError: callbacks.onError || (() => {})
        }

        // 載入電子郵件、手機的訊息
        const initialPendingMessage = options.initialPendingMessage || "已寄出電子郵件，請前去信箱完成驗證";
        const pendingMessage        = options.pendingMessage        || "尚未驗證，請前去信箱完成驗證";
        const onVerifiedMessage     = options.verifiedMessage       || "已驗證，電子郵件設定完成";
        const expiredMessage        = options.expiredMessage        || "驗證期限已逾期，請重新點擊確認按鈕";
        const canceledMessage       = options.canceledMessage       || "已取消變更";

        this._callbacks.onPending(initialPendingMessage);
        
        // ========= 開始輪詢 =========
        this._pollingIntervalId = setInterval(async () => {
            this._currentAttempt++;

            if (this._currentAttempt > this.MAX_ATTEMPTS) {
                this.stop(); 
                this._callbacks.onExpired(expiredMessage); 
                return;
            }

            try {
                const response = await fetch(this.URL, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.BEARER_TOKEN}`,
                    },
                    body: JSON.stringify({email: this._userAccount })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({
                        message: `HTTP 錯誤! 狀態碼: ${response.status}`
                    }));

                    const errorMessage = errorData.message || errorData.error   || 
                                         `未知伺服器錯誤 (狀態碼: ${response.status})`;

                    this._callbacks.onError(`伺服器錯誤: ${errorMessage}`);
                    this.stop();
                    return;
                }

                const res = await response.json();

                if (res.status === 'completed' && res.message === 'done') {
                    this._callbacks.onVerified(onVerifiedMessage);
                    this.stop();

                } else if (res.status === 'cancel') {
                    this._callbacks.onCanceled(canceledMessage);
                    this.stop();

                } else {
                    this._callbacks.onPending(pendingMessage);
                }

            } catch (error) {
                this._callbacks.onError("網路連線不穩或伺服器無回應，請稍後再試"); 
                this.stop();
            }

        }, this.INTERVAL_MS);
    },

    // ========= 停止輪詢 =========
    stop() {
        if (this._pollingIntervalId){
            clearInterval(this._pollingIntervalId);
            this._pollingIntervalId = null;
            this._userAccount = null; 
            this._currentAttempt = 0; 
        }
    },

    // ========= 狀態查詢 =========
    isRunning() {
        return this._pollingIntervalId !== null;
    },

};

