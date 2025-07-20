<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MemberCenterController;
use App\Http\Controllers\MemberRegisterController;
use App\Http\Controllers\UpdateContactController;
use App\Http\Controllers\ForgotPasswordController;


// 處理「註冊」功能
Route::controller(MemberRegisterController::class)->group(function() 
{
    Route::post('/register_run','registerRun')->name('register_run');  // 處理「註冊」流程
    Route::get('/set_password/{email}/{token}', 'setPassword')->name('set_password');  // 載入「密碼設定」頁面
    Route::post('/create_member','createMember')->name('create_member');  // 加入會員                                      
});

// 處理「密碼」功能
Route::controller(ForgotPasswordController::class)->group(function() 
{
    Route::get('/forgot_password','forgotPassword')->name('forgot_password');  // 載入「忘記密碼」頁面
    Route::post('/forgot_password_run','forgotPasswordRun')->name('forgot_password_run');  // 執行「忘記密碼」流程
    Route::get('/reset_password/{email}/{token}', 'resetPassword')->name('reset_password');  // 載入「重設密碼」頁面
    Route::post('/reset_confirm','resetConfirm')->name('reset_confirm');  // 設定「新密碼」
                                            
});

// 處理「登入」、「帳號設定」
Route::middleware(['member_center_path'])->controller(MemberCenterController::class)->group(function() 
{
    Route::get('/login','login')->name('login');  // 載入「登入註冊」頁面
    Route::post('/login_run','loginRun')->name('login_run');  // 處理「登入」流程  
    Route::get('/member_home','memberHome')->name('member_home');  // 載入「首頁」
    Route::get('/set_account','setAccount')->name('set_account');  // 載入「帳號設定」頁面
    Route::post('/edit_member_data','editMemberData')->name('edit_member_data'); // 編輯「會員資料」
    Route::get('/logout','logout')->name('logout');  // 「登出」
});

// 處理「通訊變更」流程
Route::middleware(['member_center_path'])->controller(UpdateContactController::class)->group(function() 
{
    Route::post('/update_email','updateEmail')->name('update_email'); // 執行「更新『電子郵件』」的流程
    Route::get('/update_contact/{email}/{token}','updateContact')->name('update_contact');  // 載入「確認變更」頁面
    Route::post('/button_confirm','buttonConfirm')->name('button_confirm'); // 執行「變更/取消」通訊帳號流程
    Route::get('/complete_confirm','completeConfirm')->name('complete_confirm');  // 載入「完成變更」頁面
    Route::post('/check_contact_update_status','checkContactUpdateStatus')->name('check_contact_update_status'); // 確認「變更通訊」是否完成
});



// 測試環境用連結

// Route::get('/test', function () {
//     return view('test');  
// });




