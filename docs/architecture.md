# 專案目錄

- [1️ 系統總覽與功能介紹](#1️-系統總覽與功能介紹)
- [2️ 註冊模組與認證機制](#2️-註冊模組與認證機制)
- [3️ 會員中心與登入模組](#3️-會員中心與登入模組)
- [4️ 聯絡資訊變更與輪詢模組](#4️-聯絡資訊變更與輪詢模組)
- [5️ 忘記密碼與重設密碼模組](#5️-忘記密碼與重設密碼模組)
- [安裝與執行](setup.md)




# 1️ 系統總覽與功能介紹

本專案是一套以 **Laravel 12** 開發的會員系統範例，模擬實務常見的使用者流程：  
**註冊 → 登入 → 編輯個人資料 → 驗證敏感操作（如變更 Email、忘記密碼）**。  

系統設計著重於模組分離與安全機制，涵蓋完整的會員功能，包含註冊驗證、登入權限、資料設定與帳號保護機制。  

專案採用 **自訂 Token 驗證機制**，將系統功能切分為四大主要模組，並以 **Service Layer 架構** 搭配 **Middleware 驗證邏輯** 實作。  
藉此有效落實職責分離與流程一致性，並兼顧使用者操作的安全需求與後續擴充彈性。

---

## 🔧 四大功能模組

本系統功能共分為四個主模組，對應實際使用者操作場景與 Controller 設計如下：

| 功能模組       | Controller                        | 說明                                               |
|----------------|-----------------------------------|----------------------------------------------------|
| 1️⃣ 註冊功能   | `MemberRegisterController`         | 處理訪客註冊流程，包含 Email 驗證與密碼設定         |
| 2️⃣ 忘記密碼   | `ForgotPasswordController`         | 發送驗證信並使用 token 重設密碼                    |
| 3️⃣ 變更 Email | `UpdateContactController` + `PollingStatusController` | 使用者更新 Email 並前端輪詢驗證狀態   |
| 4️⃣ 會員中心   | `MemberCenterController`           | 提供登入後首頁與個人資料編輯功能                   |

---

### 📘 功能模組詳解

#### 1. 註冊功能模組
- 訪客填寫 Email → 系統發送驗證信
- 驗證成功後，導向設定密碼頁完成註冊
- 實作 email 驗證 + 訪客 token 流程
- Controller：`MemberRegisterController`

#### 2. 忘記密碼模組
- 使用者點選忘記密碼 → 系統發送重設連結
- 驗證 token 後導向設定新密碼頁
- Controller：`ForgotPasswordController`

#### 3. 變更 Email 模組
- 登入後可提出變更 Email 申請
- 系統發送驗證信 → 前端輪詢後台驗證狀態
- Controller：`UpdateContactController`  
- 輪詢 API：`PollingStatusController`

#### 4. 會員中心模組
- 登入後進入首頁，可瀏覽個人資料
- 提供暱稱、性別、年齡層的資料編輯功能
- Controller：`MemberCenterController`



---

## 🔐 驗證機制與安全架構

本系統採用 **Token-based 驗證設計**，捨棄 Laravel 原生登入機制，改以自訂安全流程，確保各功能模組的驗證邏輯獨立且可重複使用，  
提升系統安全與維護便利性。

系統採用自訂 Token-based 驗證機制，靈活因應多階段與多類型的認證需求，如註冊、密碼重設與 Email 變更。  
此設計不僅保持流程清晰與模組獨立，也展現對基礎驗證技術的實作能力與理解，避免直接套用框架預設，提升系統的可維護性與擴展性。


### ✅ 主要 Token 類型與使用場景

| Token 類型             | 使用場景                          | 儲存位置                             |
|------------------------|---------------------------------|-------------------------------------|
| `register_token`       | 註冊流程中 Email 驗證連結           | `member_center_guests`              |
| `password_token`       | 忘記密碼流程的驗證連結               | `member_center_password_update`     |
| `update_contact_token` | 會員 Email 變更驗證連結              | `member_center_user_contact_update` |
| `bearer_token`         | 登入後身份驗證，放在 HTTP Header 中 | `member_center_users`               |

### 🔄 驗證流程與實作方式

- **登入功能**使用自訂 Middleware 來攔截請求，驗證 Bearer Token 的有效性與授權，確保會員專區的安全訪問。

- **其他流程（註冊驗證、忘記密碼、Email 變更**為多階段且狀態複雜的 Token 驗證，  
  **由 Service 層在 Controller 內統一處理**，根據 Token 狀態判斷流程是否允許繼續。

- 由於這些流程涉及「一次性驗證」、「過期判斷」及「狀態管理」等業務邏輯，使用 Middleware 直接攔截並不合適，  
  且相關路由多為開放狀態（非登入限制），故採用 Service 層集中處理更為靈活。

- 此設計使驗證機制在 Controller 與 Service 之間保持一致且可擴充，方便未來新增驗證規則或整合更複雜的權限控管。

---

模擬實務開發中對流程嚴謹與系統安全的要求，架構出兼具安全性、模組化與擴展性的系統。

---

# 2️ 註冊模組與認證機制

## 1. 流程概述

本系統的會員註冊流程如下：  
訪客輸入 Email 後，系統會在 `member_center_guests` 資料表中建立訪客暫存資料，並產生 `register_token`，同時寄送包含驗證連結的註冊信。  
使用者點擊驗證連結後，系統驗證 token 的有效性與狀態，若通過驗證則允許設定密碼，完成後正式建立會員帳號（User），並產生登入用的 `bearer_token`。

整個流程確保資料的一致性與安全性，並透過 Service Layer 將業務邏輯與 Controller 解耦。

---

## 2. Controller 與 Service 職責劃分

| 元件                     | 角色與職責                                  | 主要方法                                  |
|--------------------------|-----------------------------------|-----------------------------------------------|
| `MemberRegisterController` | 負責處理 HTTP 請求，控制流程的進行  | `registerRun()`, `setPassword()`, `createMember()` |
| `MemberRegisterService`  | 專注於註冊相關的商業邏輯：驗證輸入、寄送信件、建立會員資料 | `isRequestValid()`, `prepareVerification()`, `authorizeSetPasswordAccess()`, `createMember()` |

---

## 3. 主要程式碼片段說明

### 註冊請求驗證與寄送驗證信

```php
    public function registerRun(Request $request)
    {
        MemberRegisterService::isRequestValid($request->post("account"));
        MemberRegisterService::prepareVerification($request->post("account"));

        return response()->json([
            'code' => 200,
            'message' => '驗證信已寄出，請前往信箱完成開通流程'
        ]);
    }
```

- isRequestValid()：檢查 Email 格式是否正確，且該帳號尚未被註冊。
- prepareVerification()：建立訪客資料（Guest）與對應的註冊 token，並寄出驗證信。

### 密碼設定頁面授權

```php
    public function setPassword(Request $request)
    {
        $email = $request->route('email');
        $token = $request->route('token');

        MemberRegisterService::authorizeSetPasswordAccess($email, $token);

        return view('set_password', compact('email'));
    }
```

- 透過 authorizeSetPasswordAccess() 驗證 token 是否有效且對應正確的 Email，防止非法存取。

### 建立正式會員帳號

```php
    public function createMember(Request $request)
    {
        MemberRegisterService::validateSetRequest($request->email, $request->password, $request->password_confirmed);

        $user = MemberRegisterService::createMember($request->email, $request->password);
        $cookie = MemberAuthService::setBearerTokenCookie($user->bearer_token, 2880);

        return redirect()->route('set_account')->cookie($cookie);
    }
```
## 4. Token 狀態管理

為確保註冊驗證流程的安全性與一致性，系統設計 `register_token` 的狀態管理機制，並儲存在 `member_center_guests` 資料表中。

每筆 token 都擁有獨立的過期時間與狀態欄位，避免重複驗證或非法操作，有效提升系統的資料完整性與操作安全。

### 🔐 Token 狀態類型

| 狀態         | 說明                                                |
|--------------|-----------------------------------------------------|
| `pending`    | 初始狀態，表示驗證信已寄出，等待使用者驗證         |
| `completed`  | 使用者已成功驗證，流程結束                         |
| `expired`    | Token 已超過設定期限，自動失效                     |
| `cancel`     | 同一帳號重複申請註冊時，會取消先前未完成的 token   |

### 🚀 驗證安全邏輯說明

每筆 Token 都有時效性與狀態限制，系統在驗證時將執行以下檢查流程：

#### ✅ 驗證時，系統會檢查：
  -  Token 是否存在
  -  是否超過有效期限（已過期則視為無效）
  -  狀態是否為 pending（避免重複使用或已被撤銷）
  -  是否對應正確的 Email（防止任意拼湊 URL 存取）

#### 🔒 驗證結果處理：
- **驗證成功後 →** 狀態更新為 completed，禁止再次使用
- **驗證失敗 →** 拋出例外，引導使用者重新申請流程

#### 💡 擴充應用說明：
此驗證機制確保 **每次驗證連結皆為一次性使用** ，
有效防止過期、重複請求與非法操作，也為其他模組（如「忘記密碼」、「變更 Email」）
提供一致且可擴充的驗證邏輯參考。

---

# 3️ 會員中心與登入模組

## 1. 模組概述

會員中心模組提供登入驗證與個人資料管理功能，採用自訂 Bearer Token 驗證機制，確保登入身份的安全性，並透過 Service 層清楚劃分登入、編輯與登出等職責。

登入後的使用者可瀏覽首頁、設定暱稱、性別、年齡層等資料，並可進一步操作變更 Email 等敏感動作。

---

## 2. Controller 與 Service 職責對照

| 元件                      | 主要角色與職責                           | 代表方法                              |
|---------------------------|---------------------------------------|----------------------------------------|
| `MemberCenterController`  | 處理登入請求、顯示頁面與會員資料編輯     | 登入流程、首頁載入、資料編輯、登出        |
| `MemberLoginService`      | 驗證帳密、產生登入 token、登出清除 token | 驗證登入請求、設定登入狀態、登出           |
| `MemberEditService`       | 驗證與更新會員資料（暱稱、聯絡方式等）   | 請求驗證、更新會員資料                    |

---

## 3. 登入流程與驗證機制

登入時，系統會驗證帳號與密碼格式，支援 Email 或手機號碼登入。  
驗證成功後，系統產生 Bearer Token，並寫入資料庫與 HTTP Cookie，作為後續認證依據。

```php
    public function loginRun(Request $request)
    {
        $user = MemberLoginService::verifyLoginRequest($request);
        if (!$user) {
            return response()->json(['code' => '401', 'message' => '帳號或密碼錯誤']);
        }

        $user = MemberLoginService::setLogin($user);
        $cookie = MemberAuthService::setBearerTokenCookie($user->bearer_token, 2880);

        return response()->json(['code' => '200', 'message' => '登入成功'])->cookie($cookie);
    }
```

---

## 4. Bearer Token 認證邏輯

登入成功後，系統會產生並更新 Bearer Token 及其過期時間，確保用戶身份驗證的安全。  
所有登入後的路由都會透過 Middleware 驗證此 Token 的有效性與狀態。

```php
    try 
    {
        $token = $request->cookie('bearer_token');
        if (!$token) {
            return redirect()->route('login')->with('error', '請先登入');
        }

        $user = MemberAuthService::validateUserLogin($token);
        if (!$user) {
            return redirect()->route('login')->with('error', '登入狀態已過期，請重新登入');
        }

        $request->attributes->set('user', $user);
        return $next($request);

    } catch (Exception $e) 
    {
        return redirect()->route('login')->with('error', $e->getMessage());
    }
```
這段程式碼是 Middleware 內負責認證的部分

---

## 5. 資料編輯流程與驗證

會員可在登入後編輯個人資料，包括暱稱、性別、地址、手機等資訊。  
系統會驗證 Email 是否有效且已完成驗證，並確認手機與地址格式正確，暱稱不得為空。  
資料更新過程使用 Transaction 保證資料一致性與安全。

---

## 6. 登出流程

登出時，系統會清除資料庫中的 Bearer Token 與過期時間，並回傳清除 Cookie，確保用戶登出狀態完整。

---

此模組採用身份驗證與服務分層設計，實現可擴展與能維護的會員中心功能。  
登入、編輯與登出程式碼獨立分離，便於未來功能擴充與維護。

---

# 4️ 聯絡資訊變更與輪詢模組

## 1. 模組概述

聯絡資訊變更模組負責處理會員的 Email（未來可擴充手機號碼）更新流程，包含：

- 發起變更請求與驗證
- 寄送驗證信
- 使用驗證連結完成更新
- 取消變更請求
- 輪詢變更狀態以回饋前端

此模組強調資料一致性與安全性，避免重複或錯誤操作，並支援中斷與恢復流程。

---

## 2. Controller 與 Service 職責對照

| 元件                    | 主要角色與職責                             | 代表方法                         |
|-------------------------|-----------------------------------------|----------------------------------|
| `UpdateContactController` | 處理變更 Email 請求、載入驗證頁面與確認頁面 | `updateEmail()`, `updateContact()`, `updateConfirm()`, `cancelConfirm()`, `completeConfirm()` |
| `ContactUpdateService`   | 驗證請求有效性、建立變更請求、寄送驗證信、驗證 Token、完成與取消變更流程 | `isRequestValid()`, `prepareUpdateForEmail()`, `authorizeUpdateContactAccess()`, `finishConfirm()`, `cancelRequest()` |
| `PollingStatusController` | 輪詢前端查詢變更狀態                     | `checkContactUpdateStatus()`    |
| `PollingStatusService`   | 驗證輪詢請求，查詢並回傳變更狀態           | `isRequestValid()`, `checkUpdateStatus()` |

---

## 3. 變更流程說明

### 3.1 申請變更

使用者登入後，透過 `updateEmail()` 發起 Email 變更申請。  
系統驗證請求有效性，檢查新 Email 格式、是否與現有相同或已被使用，若通過則建立變更請求紀錄，並寄出包含驗證連結的信件。

### 3.2 驗證連結存取

使用者點擊驗證信中的連結，進入 `updateContact()` 頁面。  
系統驗證 Token 與 Email 是否匹配、是否有效，並回傳對應變更資料，供使用者確認。

### 3.3 確認或取消變更

使用者可選擇確認 (`updateConfirm()`) 或取消 (`cancelConfirm()`) 變更。  
確認後，系統會更新會員資料，並標記變更完成狀態。  
取消則將變更狀態改為取消，並通知使用者。

### 3.4 完成變更

完成後跳轉至 `completeConfirm()` 頁面，提示使用者變更成功並建議重新登入。

---

## 4. 輪詢變更狀態

前端可透過 `PollingStatusController::checkContactUpdateStatus()` 進行輪詢，查詢變更請求的最新狀態。  
輪詢服務會驗證身份，查詢對應變更紀錄，判斷是否已完成變更，並回傳狀態與結果。

---

## 5. 重要安全機制

- 所有驗證 Token 皆有過期時間，超時自動標記為過期，防止惡意或過期使用。
- 變更請求建立時，若同一用戶已有待處理請求，會自動取消先前請求，避免重複變更導致狀態錯亂。
- Token 與 Email、使用者 ID 必須完全匹配，防止跨帳號操作。
- 變更流程皆使用資料庫交易（Transaction）確保資料一致性。
- 變更成功後，相關狀態與驗證時間更新，避免重複操作。

---

## 6. 使用的資料表與模型

| 模型                 | 對應資料表                       | 功能說明               |
|----------------------|---------------------------------|------------------------|
| `User`               | `member_center_users`            | 正式會員資料           |
| `UserContactUpdate`   | `member_center_contact_updates` | 聯絡資訊變更請求紀錄   |

---

## 7. 範例程式碼片段

```php
    // 發起 Email 變更申請
    public function updateEmail(Request $request)
    {
        try {
            $contactType = ContactUpdateService::isRequestValid($request);
            if ($contactType === 'email') {
                ContactUpdateService::prepareUpdateForEmail($request);
            } else {
                throw new Exception("功能尚未開通");
            }
            return response()->json(['code' => 200, 'message' => 'success']);
        } catch (Exception $e) {
            return response()->json(['code' => 400, 'message' => $e->getMessage()]);
        }
    }
```

此章節完整涵蓋聯絡資訊變更模組的設計理念、流程與安全考量，能清楚表達系統對敏感資料變更的嚴謹管理與用戶體驗。

---

# 5️ 忘記密碼與重設密碼模組


## 1. 模組概述

忘記密碼模組提供會員重置密碼的完整流程，包括：

- 驗證帳號有效性（Email）
- 建立並管理密碼重置請求
- 寄送含驗證連結的重設密碼郵件
- 驗證重置請求與使用者權限
- 設定並更新新密碼

模組設計強調安全驗證與流程完整性，確保使用者身份與密碼重置的安全性。

---

## 2. Controller 與 Service 職責對照

| 元件                      | 主要角色與職責                             | 代表方法                             |
|---------------------------|-----------------------------------------|-------------------------------------|
| `ForgotPasswordController` | 處理載入重設密碼頁面、發起重置請求、驗證並設定新密碼 | `forgotPassword()`, `forgotPasswordRun()`, `resetPassword()`, `resetConfirm()` |
| `ForgotPasswordService`    | 驗證帳號、建立重置請求、寄送郵件、驗證 Token、更新密碼 | `isRequestValid()`, `prepareVerification()`, `authorizeResetPasswordAccess()`, `validateResetRequest()`, `resetPassword()` |

---

## 3. 忘記密碼流程說明

### 3.1 發起重置請求

使用者輸入 Email，呼叫 `forgotPasswordRun()`，系統驗證 Email 格式與會員身份，  
通過後建立重置密碼請求紀錄並產生 Token，寄出包含驗證連結的重置郵件。

### 3.2 載入重設密碼頁面

使用者點擊郵件內連結，呼叫 `resetPassword()`，系統驗證 Email 與 Token 的有效性，  
確認後顯示重設密碼頁面。

### 3.3 設定新密碼

使用者輸入新密碼與確認密碼，呼叫 `resetConfirm()`。  
系統驗證 Token 與密碼格式一致性，通過後執行密碼更新流程，並自動登入（更新 Bearer Token）。

---

## 4. 重要安全機制

- Token 設計有過期時間，超時自動標記為過期，防止重複與惡意使用。
- 變更請求透過資料庫交易（Transaction）確保資料一致性與安全。
- 重設密碼前嚴格驗證 Email 與 Token 的匹配性。
- 新密碼必須符合規格要求（如密碼長度與確認一致性）。

---

## 5. 使用的資料表與模型

| 模型             | 對應資料表                    | 功能說明               |
|------------------|------------------------------|------------------------|
| `User`           | `member_center_users`          | 正式會員資料           |
| `PasswordUpdate` | `member_center_password_update` | 密碼重置請求紀錄       |

---

## 6. 範例程式碼片段

```php
    // 執行忘記密碼流程，寄出重設密碼信
    public function forgotPasswordRun(Request $request)
    {
        try {
            ForgotPasswordService::isRequestValid($request->email);
            ForgotPasswordService::prepareVerification($request->email);
            return response()->json([
                'code' => 200, 
                'message' => '變更密碼信件已寄出，請前往信箱查收'
            ]); 
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()]);
        }
    }
```
此章節完整呈現忘記密碼與重設密碼模組的功能架構與安全設計，利於未來維護與擴充。

---