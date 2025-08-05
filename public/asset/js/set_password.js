/**
 * set_password.js - 處理「設定密碼」頁面的函數
 * 
 */

import {CommonUI, ContactHintManager, MultiRuleHintManager} from './utilities.js';

(function() {
        
    const $pwdInput1 = $("#password") ;
    const $pwdInput2 = $("#confirmed_password") ;
    const $btn = $(".media_btn"); 

    const passwordRules = [
        { regex: /[A-Z]/, ruleId: 'password_rule1' },
        { regex: /[a-z]/, ruleId: 'password_rule2' },
        { regex: /\d/,    ruleId: 'password_rule3' },
        { regex: /^.{8,}$/, ruleId: 'password_rule4' }
    ];

    // 初始化
    const init = () => {

        // 初始化 MultiRuleHintManager
        passwordRules.forEach(rule => {
            MultiRuleHintManager.injectRule(rule.ruleId, $(`#${rule.ruleId}`));
        });

        // 初始化 ContactHintManager
        ContactHintManager.injectSection('confirmed_password', $("#confirmed_password_hint")); 

        // 檢查「密碼」欄位是否符合格式
        $pwdInput1.on('input', updatePasswordHints)

        // 檢查「確認密碼」欄位是否符合格式
        $pwdInput2.on('input', updatePasswordHints)

        // 提交表單
        $('form').on('submit', (event) => {
            if ($btn.hasClass('disable'))  event.preventDefault();
        })

    }

    // 檢查所有密碼規則是否都通過
    const checkAllPasswordRules = (password) => {
        if (!password || password.length === 0) return false;
        return passwordRules.every(rule => rule.regex.test(password));
    }

    // 更新「按鈕」狀態
    const toggleButtom = () => {

        const password = $pwdInput1.val();
        const confirmedPassword = $pwdInput2.val();

        const isValid = checkAllPasswordRules(password);
        const isMatched = (
            password.length > 0 && 
            confirmedPassword.length > 0 && 
            password === confirmedPassword
        );

        (isValid && isMatched)?  $btn.removeClass('disable') : $btn.addClass('disable');
    }

    // 更新「確認密碼」欄位的提示
    const updateConfirmedHint = () => {

        const password = $pwdInput1.val();
        const confirmedPassword = $pwdInput2.val();

        ContactHintManager.hide('confirmed_password'); 

        if (confirmedPassword.length === 0) {
            ContactHintManager.update('confirmed_password', "請再次輸入密碼", "warn");
        } else if (password === confirmedPassword) {
            ContactHintManager.update('confirmed_password', "密碼相符", "success");
        } else {
            ContactHintManager.update('confirmed_password', "密碼不符", "fail");
        }

        toggleButtom(); 
    };   
    
    // 更新「密碼」欄位的提示
    const updatePasswordHints = () => {

        const password = $pwdInput1.val();
        MultiRuleHintManager.hideAll();

        passwordRules.forEach(rule => {
            const isMatched = rule.regex.test(password);
            if (isMatched) {
                MultiRuleHintManager.update(rule.ruleId, 'success');
            } else {
                if (password.length > 0) { 
                    // MultiRuleHintManager.update(rule.ruleId, 'fail');
                    MultiRuleHintManager.update(rule.ruleId, 'warn');
                } else { 
                    MultiRuleHintManager.update(rule.ruleId, 'warn');
                }
            }
        });

        updateConfirmedHint();
    }

    $(function(){

        // 初始化物件，以及綁定監聽
        init();

        // 渲染頁面
        updatePasswordHints();

        // 使用通用 UI 互動模組
        CommonUI.init();

    })


})();