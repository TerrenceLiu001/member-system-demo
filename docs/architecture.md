# 專案目錄

- [第一章：系統總覽與功能介紹](#第一章系統總覽與功能介紹)
- [第二章：架構理念與模組設計](#第二章架構理念與模組設計)
- [第三章：服務層的檔案結構與功能劃分](#第三章服務層的檔案結構與功能劃分)
- [第四章：抽象流程的實作與應用](#第四章抽象流程的實作與應用)
- [第五章：資料模型與資料層的抽象設計](#第五章資料模型與資料層的抽象設計)
- [第六章：驗證機制與策略模式的應用](#第六章驗證機制與策略模式的應用)
- [第七章：會員註冊與驗證流程的實作](#第七章會員註冊與驗證流程的實作)
- [第八章：總結](#第八章總結)
- [安裝與執行](setup.md)



<br/>
<br/>
<br/>

# 第一章：系統總覽與功能介紹

## 1 - 1 導言
本專案是一套以 **Laravel 12** 開發的會員系統範例，模擬實務上常見的使用者流程：  
**註冊 → 登入 → 編輯個人資料 → 驗證敏感操作（如變更 Email、忘記密碼）**。  

系統設計著重於模組化的拆分，涵蓋註冊驗證、登入授權、會員資料設定及帳號安全保護等功能。  

採用 **自訂的 Token 驗證機制**，將系統功能切分為五個主要模組，並以 **Service Layer 架構** 負責封裝流程邏輯，再結合 **Middleware 驗證邏輯** 控管登入狀態與存取權限，確保受保護頁面之存取安全。 

藉由服務層的設計，我們落實了職責分離，並確保不同模組的流程具備一致性，不僅強化了邏輯與安全性，也為未來的擴充奠定良好基礎。


## 1 - 2 功能模組

本系統功能共分為五個主模組，對應實際操作場景與 Controller 設計如下：

| 功能模組      | Controller                   | 說明  |
|------------ |------------------------------|----------------------------------------------------|
|  會員登入    | `MemberLoginController`      | 提供登入、登出功能
|  會員註冊    | `MemberRegisterController`   | 處理訪客註冊流程，包含電子郵件驗證與密碼設定 |
|  忘記密碼    | `ForgotPasswordController`   | 發送驗證信並重設密碼  |
|  聯絡資訊更新 | `UpdateContactController` + `PollingStatusController` | 使用者更新電子郵件，並透過輪詢機制即時驗證狀態  |
|  會員中心    | `MemberCenterController`    | 登入後的首頁與個人資料編輯功能  |


## 1 - 3 資料表

此會員系統，著要圍繞以下四個資料表為功能核心展開

| 資料表名稱                            | 主要用途                             |
|------------------------------------ |-------------------------------------|
| `member_center_users`               | 儲存會員的基本帳號資料及登入資訊         |
| `member_center_guests`              | 記錄訪客註冊時的暫存資料與驗證狀態       |
| `member_center_password_update`     | 管理忘記密碼流程中的重設請求與驗證       |
| `member_center_user_contact_update` | 負責會員變更 Email 等敏感資訊的驗證狀態  |

---

<br/>
<br/>
<br/>


# 第二章：架構理念與模組設計

## 2 - 1 架構介紹

採用 **分層架構** 設計，區分不同層級的職責，主要架構包含以下幾個層級：

* ### Controller 層 ：
    負責接收並處理 HTTP 請求，與前端使用者互動。

    本專案中，Controller 僅負責處理來自 Request 的資料、調用 Service 執行業務邏輯，並回傳處理結果，使得 Controller 保持結構簡潔、職責單一。

    **✅ 這樣的設計有助於：**

>- 降低程式耦合與重複，Controller 不易肥大  
>- 未來如果要擴充功能或修改流程，僅需更動 Service 層  
>- 更容易進行測試、除錯，利於維護與重構

* ### Service 層（廣義）：
    在本專案中，Service 層不僅負責處理來自 Controller 的請求，更擔任業務流程調度與邏輯執行的核心。  

    為了提升可維護性與擴充性，系統進一步將 Service 層拆分為數個角色，每個角色專責特定邏輯層級，彼此協作完成整體流程：

    #### 主 Service 類：
    - 負責統籌註冊、登入等主要的業務流程
    - 可視為「流程控制中心」，負責調度各種內部模組
    - 本身不處理具體操作細節，而是透過調用其他模組來完成任務

       > **📝 設計理念：** 將主流程與單元操作分離，使得全局流程的控制更為清晰，降低維護成本
    
    #### UnitService 類：
    - 專責處理主流程中的單一操作單元，例如帳號是否存在、建立資料等
    - 通常被特定 Service 所專屬使用，對應各自的業務流程
    - 功能模組化，便於單元測試與重複使用

        > **📝 設計理念：**  
        > 參考 Delegation（委派）模式，將主流程中的單元操作交給專門負責的模組處理，以達到降低主要 Service 的複雜度，提升維護性為主要目的。
    
    #### Orchestrator：
    - 將「寄送驗證信」這種橫跨多個業務流程（註冊、忘記密碼、信箱更新）的邏輯進行抽象化
    - 採用樣板方法模式（Template Method Pattern），定義整體流程步驟
    - 不實作具體細節，而是透過對應的策略物件（Strategy）來完成每一步驟

        > **📝 設計理念：** 將重複的流程抽成樣板，避免重寫相同的程式碼，將步驟標準化並提供可測試的組件
        
    #### Strategy 類：
    - 對應不同業務流程，定義具體的驗證信發送邏輯與使用者處理方式
    - 每個 Strategy 代表一組專屬邏輯，實作樣板中的必要步驟
    - 與 Orchestrator 搭配，實現流程中的可替換性（Pluggability）

        > **📝 設計理念：** 配合樣板模式，實踐 Open/Closed Principle，新增流程僅需實作 Strategy 即可 

    **✅ 這樣的分層設計有助於：**

>- 主要流程與細節操作的邏輯分離，架構清晰，模組權責分明
>- 重複邏輯抽象化，減少冗餘，讓步驟標準化
>- 參考策略與樣板模式，讓流程的彈性增加並提升系統的擴展能力
>- 模組化使得測試更加容易


* ### Repository 層：
    Repository 層負責與資料庫進行直接互動，封裝對資料的操作，避免 Controller 和 Service 直接使用 ORM。  
    透過定義介面（Interface）與 Eloquent 實作，減少重複的程式碼並提升測試能力，區分資料存取與業務流程的界線。

    本專案採用 Repository Pattern，實作 BaseEloquentRepository，將共通的資料操作方法放入其中，  
    並針對特定資料模型實作專屬的 Repository，負責該模型特有的查詢與操作。

    **✅ 這樣的設計有助於：**

>- 封裝資料存取，避免重複的程式碼
>- 提供統一介面，方便未來切換資料來源或進行單元測試
>- 將業務流程中有關資料層複雜操作模組化


* ### Middleware（MemberCenterPathMiddleware）：
    MemberCenterPathMiddleware 是系統中唯一的存取控制中介層，負責保護會員中心相關的路由，防止未登入或未驗證的使用者直接存取。  

    它內建一組「白名單機制」，允許像是登入頁、信箱更新流程等特定路由跳過驗證檢查。  

    對於其他路由，Middleware 會從 Cookie 中讀取 Bearer Token，並引入 `MemberAuthService` 檢查是否有效。若驗證通過，則會將對應的 `User` 實例設入請求中，以便後續 Controller 取得當前登入使用者。

    <br/>

    #### Middleware 流程圖 
    
    ``` 
        使用者發出 Request
            │
            ▼
        [是否為白名單路由？]
            │
            ├─ 是 ─> [進入 Controller]
            └─ 否 ─> [是否有 Token？]
                        │
                        ├─ 否 ─> [導向登入頁，提示請先登入]
                        └─ 是 ─> [呼叫 MemberAuthService 驗證 Token]
                                            │
                                            ├─ 驗證成功 ─> [在 Request 中注入 user，放行]
                                            └─ 驗證失敗 ─> [導向登入頁，提示請先登入]
    ```

    <br/>

    **✅ 這樣的設計有助於：**
    
>- 透過白名單機制保持流程靈活，提升使用者體驗
>- 確保會員中心只被已驗證使用者存取，提升安全性
>- 將驗證集中在 Middleware，簡化 Controller 

<br/>

* ### 架構圖 
                                                                   
    ```
                              +-----------+
                              |  Request  |
                              +-----------+
                                    |
                                    ▼
                            +----------------+
                            |   Middleware   |
                            +----------------+
                                    |
                                    ▼
                            +----------------+
                            |   Controller   |
                            +----------------+
                                    |
                                    ▼
         +------------------------------------------------------+ 
         |  +------------+        Service Layer                 |
         |  |   Service  |                                      |
         |  +------------+    +--------------+    +----------+  | 
         |        ├─────────> | Orchestrator | ─> | Stratgey |  |  
         |        |           +--------------+    +----------+  | 
         |        |                                             | 
         |        |           +--------------+                  | 
         |        └─────────> | Unit Service |                  |
         |                    +--------------+                  |
         +------------------------------------------------------+

                                    |
                                    ▼
                            +----------------+
                            |   Repository   |
                            +----------------+
                                    |
                                    ▼
                            +----------------+
                            |    Database    |
                            +----------------+

    ``` 

## 2 - 2 模組劃分與功能界定

本系統依據使用者操作流程，將功能切分為數個獨立模組，分別負責註冊、登入、會員中心資料維護、忘記密碼與聯絡資訊更新等職責。
每個模組均遵循「單一職責原則」，透過 Service 層協調流程和驗證邏輯，維持良好的模組邊界與維護性。

系統主要有五個核心功能，每個功能再依分層架構，進一步細分相關元件。  
以下為各功能所使用的 Controller、Service、UnitService 及 Strategy/Orchestrator 等元件對應表：

| 功能   | Controller | 主 Service | UnitService | Strategy / Orchestrator  |
|---------|-------------------------|------------------------|--------------------------|---------------------------------|
| 會員註冊 | MemberRegisterController | MemberRegisterService | UnitRegisterService       | RegisterVerificationStrategy, VerificationEmailOrchestrator  |
| 忘記密碼 | ForgotPasswordController | ForgotPasswordService | UnitForgotPasswordService | ForgotPasswordVerificationStrategy, VerificationEmailOrchestrator  |
| 變更通訊 | UpdateContactController | UpdateContactService   | UnitUpdateContactService  | UpdateContactVerificationStrategy, VerificationEmailOrchestrator  |
| 會員登入 | MemberLoginController | MemberLoginService | UnitLoginService | —                                  |
| 會員中心 | MemberCenterController| MemberEditService  | —                | —                                  |

<br/>

**備註**：  
- 忘記密碼、註冊、變更通訊等流程共用 `VerificationEmailOrchestrator` 進行郵件驗證流程的調度。  
- 登入及會員中心編輯功能流程相對簡單，不涉及多步驟驗證或跨模組協作，因此無需使用 Orchestrator 與 Strategy 模式。  

<br/>
<br/>

本系統的五個主要功能以及主要操作的資料表如下：

| 功能名稱      | 功能描述                                             | 資料表（主要操作）     |
|--------------|--------------------------------------------------- |---------------------|
| 會員註冊      | 提供使用者註冊帳號、驗證信箱、建立帳號資料等流程。          | `member_center_guests`             |
| 會員登入      | 提供使用者透過帳號密碼登入系統，取得會員專屬操作權限。       | `member_center_users`              |
| 會員中心      | 允許會員檢視並修改個人資料（如性別、電話、地址等）。        | `member_center_users`               |
| 忘記密碼      | 讓使用者在忘記密碼時，透過信箱驗證重新設定新密碼。          | `member_center_password_update`     |
| 聯絡資訊更新   | 提供會員變更信箱，並重新驗證新資料的正確性。              | `member_center_user_contact_update` |

<br/>
<br/>

其中「聯絡資訊更新」功能，除了驗證信箱、變更郵件地址之外，也實作了輪詢機制 (Polling)，讓前端網頁可以即時的動態更新，  
讓使用者可以有良好的用戶體驗。

以下提供輪詢機制的基本資料：

| 功能名稱 | 功能描述 | Controller | Service | 資料表（主要操作） |
|--------|---------|------------|---------|------------------|
| 狀態輪詢 | 前端透過定時請求得到通訊更新的驗證狀態 | PollingStatusController | PollingStatusService | `member_center_user_contact_update` |

<br/>
<br/>

以上模組與元件分工，確保了各功能的單一責任與良好維護性。各功能在系統架構中具明確定位，  
並透過共用元件（如郵件調度與狀態輪詢服務）保持高內聚、低耦合的模組互動，為後續擴充提供良好基礎。


<br/>

## 2 - 3 設計動機與理念
在早期設計中，主要服務層（Service）程式碼過於龐大，混雜了多種職責。為了提高程式碼的品質，讓之後的維護成本降低，設計動機主要來自以下幾個考量：

* ### 精簡 Service
    * **拆分核心流程與單元操作：** 這些 Service 不僅處理主要業務流程，也包含了許多單元功能，如資料格式驗證、欄位更新等。因此，參考了委派模式（Delegation Pattern），將這些單元操作獨立出來，交由 UnitService 處理。這麼做讓主要服務層可以專注於協調業務流程，而不會因過多的細節而變得臃腫。

    * **封裝資料庫操作：** 為了將服務層與資料層解耦，減少程式碼複雜度，運用了 Repository 模式，將對資料庫的操作封裝起來，讓服務層無需直接處理資料庫的細節。

* ### 消除重複程式碼
    * **提取共通流程：** 在精簡服務層的過程中，發現「會員註冊」、「忘記密碼」和「聯絡資訊更新」等功能，都包含了「發送驗證信」這個高度相似的步驟。為了避免重複實作，我們決定將此共通流程獨立出來。

    * **模組化設計：** 為了解決這個問題，我們參考了樣板方法模式（Template Method Pattern），建立一個流程協調器（VerificationEmailOrchestrator）來封裝共通的步驟。接著，運用策略模式（Strategy Pattern）的概念，針對不同的功能，實作各自的策略元件（Strategy），讓流程能被替換，同時也保證了功能的獨立性

<br/>
<br/>

### 採用策略模式的模組與元件

以下列出系統中採用此設計的模組與其所對應的策略與封裝元件：

| 功能 | 封裝流程編排器 | 策略元件 |
| ------ | ------------------------------- | ------------------------------- |
| 會員註冊   | `VerificationEmailOrchestrator` | `RegisterVerificationStrategy`       |
| 忘記密碼   | `VerificationEmailOrchestrator` | `ForgotPasswordVerificationStrategy` |
| 聯絡資訊更新 | `VerificationEmailOrchestrator` | `UpdateContactVerificationStrategy`  |

這些功能皆包含「檢查請求」 → 「更新並創建紀錄」  → 「準備寄信資料」→ 「寄信」等共通步驟，  
透過策略與流程編排器拆分出可重用模組，避免撰寫重複流程。

<br/>

### 登入與會員中心未使用策略封裝的原因
與上述功能相比，「會員登入」與「會員中心資料維護」的流程較為單一，和其他功能的流程相比，並沒有共通性，  
僅需進行帳密驗證或資料更新，並無驗證信或多階段流程的需求，故採取簡化實作，直接在 Service 或透過 UnitService 處理即可。


<br/>
<br/>
<br/>

# 第三章：服務層的檔案結構與功能劃分

在第二章中，闡述了本專案採用的架構理念，特別是 Service Layer 的分層設計以及主 Service 與 UnitService 的職責劃分。然而，為了實踐此目的需要透過清晰的組織架構來實現。本章將解釋 `app/Services` 目錄中的檔案結構背後的設計理念為何。  

將從整體目錄概覽開始，逐步揭示主 Service 與 UnitService 的檔案存放原因，以及專案如何將不同的策略模式集中管理。透過本章的說明，希望能對專案的程式碼結構有全面的理解，為後續深入探討各項抽象流程與實作細節奠定堅實的基礎。

## 3 - 1 核心服務層目錄概覽

本專案的服務層所有程式碼皆位於 `app/Services` 目錄下。該目錄採用模組化的構思，將不同職責的元件清晰地劃分開來，藉此來提升架構的清晰度與後續的維護效率。以下是服務層核心目錄的結構概覽：
  
```
    app/Services
    ├── Api 
    │    └── PollingStatusService.php   ------>    用於輪詢檢查
    ├── MemberLogin                 ───┐        ┌──────────────────────
    ├── MemberRegister                 │        │  主要實現五個主要功能：      
    ├── UpdateContact                  │        │                      
    ├── ForgotPassword                 │ -----> │  登入、註冊、忘記密碼、  
    ├── Strategies                     │        │  通訊資料變更、會員編輯  
    │    ├── Tokens                    │        └──────────────────────
    │    └── Verification              │
    ├── MemberEditService.php       ───┘
    ├── MemberAuthService.php       ---------->   驗證權限相關服務
    ├── MemberEmailService.php      ---------->   寄送信件服務
    ├── ValidationService.php       ---------->   可重複用的驗證函數
    ├── ServiceRegistry.php     ───────┐  
    └── AbstractUnitService.php ───────┘ ----->   服務層的中央管理員   

    ---------------------------------------------
    註：Strategies/Tokens 用於實作 MemberAuthService.php 中的 Token 驗證流程。
    ---------------------------------------------
```
<br/>

- **ServiceRegistry**： 這是服務層的中央管理員。它負責管理所有核心服務的實例，並透過依賴注入（Dependency Injection）的方式，為需要服務的類別提供存取物件的入口。

- **AbstractUnitService**： 為所有 UnitService 提供了共通的基礎，確保它們都能透過注入 ServiceRegistry 的方式，方便地存取並使用所有核心服務。

- **MemberAuthService**： 作為專案身分驗證的核心服務，它負責處理所有關於 Token 的生成、驗證與管理。

- **ValidationService**： 封裝了專案中多個功能會共用的驗證邏輯，例如電子郵件格式檢查、密碼強度驗證等，以避免程式碼重複。

- **MemberEmailService**： 扮演著 Facade 的角色，為外部提供了與電子郵件相關的簡化介面。將寄送不同類型驗證信件的流程封裝起來，並提供生成驗證連結的輔助方法。

- **PollingStatusService**： 此服務專用於處理「變更通訊資料」的輪詢請求。它負責驗證使用者身分，並查詢資料庫中通訊資料變更紀錄的最新狀態，以提供即時的進度更新。

- **功能性目錄(e.g. MemberLogin, MemberRegister)**： 每個功能性目錄對應一個獨立的業務模組，專門用來放置該功能的主 Service 類別與對應的 UnitService，實現清楚的模組劃分與職責分工。

- **Strategies 目錄**： 此目錄集中管理所有與策略模式相關的檔案，其下分為 Tokens 和 Verification 兩個子目錄，分別處理 Token 策略和郵件驗證策略。

<br/>

透過這樣的目錄結構，可以迅速定位到特定功能的程式碼，並理解每個檔案所扮演的角色，從而大幅降低維護與開發的難度。

<br/>

## 3 - 2 主 Service 與 UnitService 的職責劃分

為了實踐單一職責原則（Single Responsibility Principle），將系統的 Service Layer 設計成「主 Service」與「UnitService」的協作模式。此架構旨在將複雜的流程分解為可管理、可重用的單元，從而使程式更易於理解與維護。

<br/>

### 主 Service：流程的協調者

主 Service 類別位於各自的功能性目錄下，例如 MemberLogin/MemberLoginService.php。它們的主要職責是：  

- **流程控制**： 作為業務流程的調度者，主 Service 負責定義整個功能的執行順序。它會協調多個 UnitService 或其他核心服務，但不處理具體的業務細節。

- **介面統一**： 主 Service 對外提供一個簡潔的介面，例如 MemberLoginService::login()，將後台複雜的執行步驟隱藏起來，讓 Controller 只需要關注調用主 Service，而無需理解內部流程。

<br/>

### UnitService：單一職責的執行單元 

UnitService 類別與其對應的主 Service 放在同一個功能性目錄下，例如 MemberLogin/UnitLoginService.php。它們的主要職責是：

- **專注於單一任務**： 每個 UnitService 都專門負責處理一個獨立、原子性的業務操作，例如資料庫的讀寫、資料的驗證、密碼的雜湊處理等。
- **為主 Service 服務**： UnitService 的存在是為了支持其對應的主 Service 完成複雜流程。它將主 Service 所需的底層細節操作封裝起來，使得主 Service 的邏輯保持精簡且易於閱讀。

<br/>

### 兩者的協作模式

這種分工體現了委派模式（Delegation Pattern）的精髓：主 Service 透過將具體的業務操作「委派」給其專屬的 UnitService，從而保持自身的精簡和清晰。這種模式的好處包括：  

- **高內聚**： 每個 UnitService 都專注於自己的單一職責，程式碼更為緊湊，且與其主 Service 關係緊密。
- **低耦合**： 主 Service 不直接與底層實作細節耦合，而是透過調用 UnitService 來完成任務。
- **易於測試**： 單一職責的 UnitService 類別更容易進行獨立的單元測試。

<br/>

透過這樣的劃分，專案的業務邏輯可以被有效地分解成清晰且可管理的區塊，為之後的維護或擴充打下良好的基礎。

### 實踐範例

**主 Service：MemberRegisterService 範例**  

以下範例展示了 MemberRegisterService 如何作為流程的協調者，將不同的註冊流程委派給  
VerificationEmailOrchestrator 和 UnitRegisterService。

```php
    class MemberRegisterService
    {
        // 注入流程編排器和執行單元
        protected VerificationEmailOrchestrator $orchestrator;
        protected UnitRegisterService $unitService; 

        public function __construct(
            VerificationEmailOrchestrator $orchestrator,
            UnitRegisterService $unitService
        )
        {
            $this->orchestrator = $orchestrator;
            $this->unitService  = $unitService;
        }

        // 開始「註冊」流程的第一步：發送驗證信
        public function initiateRegistrationProcess(Request $request): void
        {
            // 委派任務：將發送驗證信的流程交給 Orchestrator
            $this->orchestrator->dispatchVerification(
                'register', 
                $request
            );
        }

        // 載入「設定密碼」頁面
        public function authorizeSetPasswordPage(string $email, string $token): void
        {
            // 委派任務：驗證 Email
            $this->unitService->ensureAccountValid($email);

            // 委派任務：驗證 Register Token
            $this->unitService->verifyRegisterToken(
                $token, $email
            );
        }

        // 完成「註冊」流程的最後一步
        public function completeRegistrationProcess(
            ?string $email, 
            ?string $password, 
            ?string $confirmed
        ): Cookie
        {
            // 委派任務：驗證所有輸入資料
            $validatedData = $this->unitService->ensureDataValid(
                $email, $password, $confirmed
            );

            // 委派任務：建立新會員
            $user = $this->unitService->createMember($validatedData);

            // 委派任務：設定登入 Cookie
            $cookie = $this->unitService->setCookie($user->bearer_token);

            return $cookie;
        }
    }
```

**UnitService：UnitRegisterService 範例**  

UnitRegisterService 專注於處理主 Service 委派過來的單一、原子性任務。它封裝了具體的實作細節，例如資料庫操作、輸入驗證與 Token 處理，同時也展示了如何調用 ServiceRegistry 中的其他核心服務，以下節選部分程式碼做為概念介紹。

```php
    class UnitRegisterService extends AbstractUnitService
    {
        protected EloquentGuestRepository $guestRepository;

        public function __construct(
            ServiceRegistry $services,
            EloquentGuestRepository $guestRepository
        )
        {
            parent::__construct($services);
            $this->guestRepository = $guestRepository;
        }

        // 專注於單一任務：建立新會員
        public function createMember(array $data): User
        {
            [
                'email'    => $email, 
                'password' => $password, 
                'guest'    => $guest
            ] = $data;
            
            return DB::transaction(function() use ($email, $password, $guest) {
                
                // 將 Guests 中的 Record 標記為 completed
                $this->guestRepository->markStatus($guest, 'completed');

                // 在 Users 中建立 Record
                $user = $this->services->userRepository->create([
                    'email'       => $email,
                    'password'    => bcrypt($password),
                    'guest_id'    => $guest->id,
                ]);

                // 透過 ServiceRegistry 調用其他服務
                $bearerToken = $this->services
                                    ->memberAuthService
                                    ->generateToken('login');

                $this->services->userRepository->handleToken(
                    $user, $bearerToken, 1440
                );

                return $user;
            });
        }

        // 專注於單一任務：驗證 Register Token
        public function verifyRegisterToken(string $token, string $email): void
        {
            // 透過 ServiceRegistry 調用 MemberAuthService
            $guest = $this->services->memberAuthService->verifyToken(
                $token,
                'register'
            );

            if (!$guest || $guest->email !== $email) {
                throw new Exception("無效連結，請重新註冊");
            }
        }
    }
```

<br/>

## 3 - 3 策略模式 (Strategy) 的檔案結構與應用

為了讓業務邏輯更有彈性，也方便日後新增功能，本專案在 app/Services 目錄下設立了 Strategies 資料夾，集中管理所有與策略模式相關的檔案。這樣的設計把「策略的定義」、「實作內容」和「調用方式」清楚區分，讓架構更有條理，也更容易維護與擴充。

### 目錄結構如下：

```
    app/Services/Strategies

        Tokens
        ├── Contracts                 
        │   └── TokenStrategyInterface.php    
        ├── Implementations                 
        │   ├── LoginTokenStrategy.php  
        │   ├── RegisterTokenStrategy.php  
        │   └── ...
        ├── TokenStrategyRegistry.php
        └── AbstractTokenStrategy.php  

        Verification
        ├── Contracts
        │   └── VerificationStrategyInterface.php
        ├── Implementations
        │   ├── ForgotPasswordVerificationStrategy.php
        │   ├── RegisterVerificationStrategy.php
        │   └── ...
        ├── VerificationEmailOrchestrator.php
        └── AbstractVerificationStrategy.php

```

### 各子目錄與元件的職責

- **Contracts 目錄**：存放用來定義 Strategy 必須實作方法的 Interface，確保了程式碼的一致性。例如，TokenStrategyInterface.php 定義了 Token 策略的標準行為，而 VerificationStrategyInterface.php 則定義了驗證流程策略的基本操作。

- **Implementations 目錄**：存放了實現 Contracts 中介面的具體策略（Concrete Strategies）。每個類別都代表一種特定的業務邏輯實作。例如，LoginTokenStrategy.php 專門處理登入 Token 的生命週期與驗證邏輯； RegisterVerificationStrategy.php 則負責註冊郵件驗證的具體實作。

- **TokenStrategyRegistry**：負責集中管理所有 TokenStrategy 實例，透過 get() 方法可依照傳入的鍵（如 register、login）取得對應策略。這種設計統一了策略的註冊與取得流程，類似「服務定位器」的角色。

- **Abstract Strategy 類**：這些類別提供了共用的基礎架構與通用邏輯（如 resolveModel、isExpired），讓具體策略只需專注實作各自的業務邏輯，減少重複程式碼。

- **VerificationEmailOrchestrator**： 屬於「流程編排器」而非策略本身，採用樣板方法模式（Template Method Pattern），定義流程架構，並在過程中根據不同策略動態調用對應方法，達到流程固定、邏輯可變的設計目標。

<br/>

這種將設計模式的相關元件獨立拆分的做法，使得未來新增或修改策略時，無需變動核心業務邏輯，僅需新增或修改 Implementations 中的檔案，並在 Registry 中註冊即可，極大提升了系統的彈性與可擴展性。

<br/>

## 3 - 4 核心服務元件：ServiceRegistry 與 MemberAuthService

在本專案的服務層中，ServiceRegistry 與 MemberAuthService 扮演著至關重要的角色，前者是整個服務層的樞紐，後者則是所有會員身分驗證的核心。這兩個元件的設計是為了確保了服務層的彈性與可擴展性。

<br/>

### ServiceRegistry：服務層的中央管理員

ServiceRegistry 類別的核心職責是作為一個服務容器 (Service Container)。它的存在是為了集中管理所有核心服務（如 ValidationService、MemberEmailService 等）的實例，並透過依賴注入（Dependency Injection）的方式，為需要這些服務的類別提供統一的存取入口。

**設計理念與實踐：**  

- **抽象基底 ( AbstractUnitService )**： 為了讓所有 UnitService 都能方便地存取 ServiceRegistry，我們建立了一個抽象基底 AbstractUnitService。這個類別在建構子中接收 ServiceRegistry 的實例，並將其賦值給一個受保護的屬性 $services。

    ```php
        // AbstractUnitService.php

        abstract class AbstractUnitService
        {
            protected ServiceRegistry $services;

            public function __construct(ServiceRegistry $services)
            {
                $this->services = $services;
            }
        }
    ```
- **依賴注入的實踐**：具體的 UnitService 類別（例如 UnitRegisterService）則繼承自 AbstractUnitService。在建構子中，除了接收特定的 Dependency 外，還會呼叫 `parent::__construct($services)`，將 ServiceRegistry 傳遞給父類別。

    ```php
        // UnitRegisterService.php

        class UnitRegisterService extends AbstractUnitService
        {
            protected EloquentGuestRepository $guestRepository;

            public function __construct(
                ServiceRegistry $services,
                EloquentGuestRepository $guestRepository
            )
            {
                // 透過父類別將 ServiceRegistry 注入
                parent::__construct($services);
                $this->guestRepository = $guestRepository;
            }
        }
    ```
- **統一入口**：透過這樣的設計，UnitRegisterService 及其子類別就可以透過 `$this->services->serviceName` 的方式，輕鬆地調用 ServiceRegistry 中所管理的任何服務。這極大地簡化程式碼，避免每個服務都必須手動注入所有重複的依賴。

<br/>

### MemberAuthService：身分驗證的核心服務

MemberAuthService 是專門處理所有與會員身分驗證相關業務的核心服務。它不僅負責管理 Token 的生成、驗證與生命週期，其內部更是結合了策略模式（Strategy Pattern）的構思。

**設計理念與實踐：**  

- **策略模式的應用** ：MemberAuthService 本身並不處理具體的 Token 驗證或生成細節，而是透過 TokenStrategyRegistry 取得對應的策略實例。例如，在 `verifyToken()` 方法中，會依據傳入的 method 參數（如 'login' 或 'register'）動態地取得不同的 Strategy 來執行驗證。

- **流程骨架與委派** ：在 `verifyToken()` 方法中，定義了固定的驗證流程骨架：
`解析 Token -> 檢查模型是否存在 -> 檢查是否過期`。而其中的每個步驟，都委派給了實例化的 Strategy 去執行，這使得流程本身是固定的，但具體的驗證邏輯可以根據不同的策略而改變。

    ```php
    class MemberAuthService
    {    
        protected TokenStrategyRegistry $tokenStrategyRegistry;

        // 根據不同「方法」驗證 Token 是否有效
        public function verifyToken(
            TokenCapableInterface|string $input, 
            string $method, 
            ?array $scopes = []
        ): ?TokenCapableInterface 
        {
            // 步驟 1: 根據傳入的方法名稱，從註冊表取得對應的策略
            $strategy = $this->tokenStrategyRegistry->get($method);

            // 步驟 2: 解析 Token，並根據策略取得對應的 Model
            $model = is_string($input) ? $strategy->resolveModel($input, $scopes) : $input;

            if (!$model) throw new Exception($strategy->getInvalidMessage()); 
            
            // 步驟 3: 檢查 Token 是否過期
            if ($strategy->isExpired($model)) 
            {
                $strategy->handleExpired($model);
                throw new Exception($strategy->getExpiredMessage());
            }
            return $model; 
        }
    }
    ```

- **解耦與可擴展性** ：此設計使得 MemberAuthService 不關心 Token 具體是如何生成的，也不關心 Token 模型的細節。可以輕鬆地新增或修改 Token 策略，而無需變動 MemberAuthService 的程式碼，盡量符合依賴反轉原則。

- **獨立的 Cookie 處理** ：`setBearerTokenCookie()` 和 `forgetBearerToken()` 方法將 Cookie 的處理邏輯封裝起來，讓其他服務在調用時無需關心底層的 Cookie 函式，這也是單一職責原則的良好實踐。

<br/>

總結來說，ServiceRegistry 提供了穩固的架構基礎，讓各個服務能夠解耦並協同運作；而 MemberAuthService 則透過靈活的設計模式，成為處理複雜身分驗證邏輯的核心引擎。這兩個元件共同確保了整個服務層的健壯與可擴展性。

<br/>
<br/>
<br/>



# 第四章：抽象流程的實作與應用

## 4 - 1 核心模式：調控器（Orchestrator）與策略（Strategy）

在前面章節有提到，為了抽象化共通的業務流程並提高靈活性，採用了 Orchestrator 結合 Strategy 的設計模式。本章將探討這個核心模式的實作細節，揭示如何將抽象理念轉化為可以維護、能夠擴展的程式碼。

<br/>

### 調控器（Orchestrator）：流程的骨架

VerificationEmailOrchestrator 這個模組扮演著「流程調度者」的角色，定義了「發送驗證信」這個流程的固定步驟。  

其中以 dispatchVerification() 作為此模組的公開介面，運接收外部傳入的流程類型，會根據類型從內部策略表中選擇對應的策略，並交給 verificationFlow() 進行統一的流程處理。

```php
    public function dispatchVerification(string $type, Request $request): void
    {
        $strategy = $this->strategies[$type];
        $this->verificationFlow($strategy, $request);
    }
```

Orchestrator 在建構時，會透過 Laravel Container 自動注入所有實作了 VerificationStrategyInterface 的策略，並依據每個策略的 getType() 回傳值，建立策略映射表：
```php
    foreach ($strategies as $strategy) {
        $this->strategies[$strategy->getType()] = $strategy;
    }
```

此設計不再依賴特定策略類別，讓 Orchestrator 對擴展開放、對修改封閉，符合依賴反轉原則（DIP）與開放封閉原則（OCP）。  

接著回到 verificationFlow() ，這個方法就是「寄送信件」的流程樣板。它內部定義了固定的呼叫順序：

* #### 驗證並準備請求 (validateAndPrepareRequest)
* #### 更新並創建紀錄 (createAndUpdateRecord)
* #### 準備連結資訊 (getLinkInfo)
* #### 發送驗證信 (dispatchVerificationEmail)

```php
    private function verificationFlow(
        VerificationStrategyInterface $strategy, Request $request
    ): void
    {
        $data = $strategy->validateAndPrepareRequest($request);
        $record = $strategy->createAndUpdateRecord($data);
        $linkInfo = $strategy->getLinkInfo($record);

        $verificationLink = $this->memberEmailService->generateLink(
            $linkInfo['routeName'],
            $linkInfo['params']
        );

        $strategy->dispatchVerificationEmail($record, $verificationLink);
    }
```

Orchestrator 的核心在於它只定義流程的骨架，不實作任何步驟的具體細節。所有變動的邏輯都透過注入的策略來完成，  
委派給遵循 VerificationStrategyInterface 介面的策略物件。

<br/>

### 策略（Strategy）：可替換的具體實作

VerificationStrategyInterface 是這個模式的靈魂，它定義了 Orchestrator 所需的四個方法。每個具體的策略類別，例如 RegisterVerificationStrategy、ForgotPasswordVerificationStrategy 和 UpdateContactVerificationStrategy，都必須實作這個介面。  

這確保了無論是哪種流程，Orchestrator 都能以一致的方式來呼叫它們。從程式碼中，可以清楚看到每個策略如何實現自己的專屬的業務流程：

* #### validateAndPrepareRequest:  
    在 `RegisterVerificationStrategy` 會檢查信箱是否已經被註冊；而在 `UpdateContactVerificationStrategy`
    則注重於，更新的電子郵件是否有效、與目前的信箱是否相同等專屬判斷。

* #### createAndUpdateRecord:
    在 `RegisterVerificationStrategy` 則負責處理 `member_center_guests` 資料表；而`UpdateContactVerificationStrategy` 則負責處理 `member_center_user_update_contact` 資料表。

* #### 註冊流程的驗證邏輯
    ``` php
        public function validateAndPrepareRequest(Request $request): mixed
        {   
            $email = $request->input('account') ?? throw new Exception('請輸入帳號');

            $this->services->validationService->validateEmail($email);

            if ($this->services->userRepository->findAccount($email)) {
                throw new Exception('此信箱已被註冊，請直接登入');
            }

            return ['email' => $email];
        }
    ```


* #### 聯絡資訊更新的驗證邏輯
    ``` php
        public function validateAndPrepareRequest(Request $request): mixed
        {
            $user = $request->attributes->get('user') ?? throw new Exception("請重新登入");
            $contactType = $this->services->validationService->checkContactType($request);

            $newContact = $request->$contactType;
            $currentContact = $user->$contactType;

            $this->ensureContactValid($newContact, $currentContact, $contactType);
            return [
                'user'         => $user, 
                'new_contact'  => $newContact , 
                'contact_type' => $contactType
            ];
        }
    ```

<br/>
<br/>

### 這種設計實現了「職責分離」：

* **Orchestrator**: 專注於協調流程
* **Strategy**: 專注於處理特定功能的邏輯

當需要新增一個需要驗證信的新功能時，我們只需建立一個新的 NewFeatureVerificationStrategy 並實作 Interface，  
然後在 VerificationEmailOrchestrator::dispatchVerification() 新增方法，就可以直接使用，此設計確保系統可以輕鬆擴展相關功能。


## 4 - 2 非封裝流程的簡化設計考量

雖然 `Orchestrator` + `Strategy` 模式為複雜流程提供了高度的彈性與可維護性，但在某些流程中，過度抽象反而會增加不必要的複雜度。因此，對於流程較為單純的功能，採取了更簡化的實作方式。

### 驗證流程的精簡化

在在初期設計中，曾試圖將整個註冊、忘記密碼等流程（包含「寄送驗證信」、「進入授權頁面」、「完成最終程序」）全部封裝進 Orchestrator。

然而，卻發現這樣做只是將原本 Service 層的程式碼轉移到 Strategy 中，並沒有真正減輕程式碼的複雜度，反而增加了不必要的抽象層級。

經過權衡後，最終決定只將「寄送驗證信」這個高度相似的流程抽象出來。這個決策的核心考量在於：

* **流程差異性：** 雖然「寄送驗證信」是共通的，但「進入授權頁面」和「完成最終程序」這兩個步驟的邏輯在不同功能間差異很大。例如，註冊的最終程序是建立正式會員，而忘記密碼的最終程序是更新密碼。

* **避免過度設計：** 若強行將這些差異性大的流程也封裝進 Orchestrator，將導致 Strategy 變得臃腫且難以維護，違背了設計的初衷。

因此，Orchestrator 只負責處理共通且可替換的「寄送驗證信」流程。而「進入授權頁面」與「完成最終程序」等獨特的業務邏輯，則由各別的 Service 負責，並將單元操作委派給 UnitService，維持了架構的彈性與簡潔。

### 登入與會員中心

「會員登入」與「會員中心資料維護」這兩個功能，因其業務邏輯相對單一，並沒有被納入策略模式的封裝中。

* **會員登入：** 主要流程只包含 「帳號密碼驗證」 和 「產生 Token」 兩個核心步驟。這個流程不涉及多階段的外部驗證（如電子郵件），且步驟固定，因此直接在 MemberLoginService 中處理，並將單元操作委派給 UnitLoginService 即可。

* **會員中心：** 功能僅限於更新個人基本資料（如姓名、電話）。這個流程不具備可替換的步驟，也不與其他模組共用邏輯，所以直接由 MemberEditService 處理，無需使用 Orchestrator 進行流程協調。

<br/>
<br/>
<br/>

# 第五章：資料模型與資料層的抽象設計

本章將詳細介紹本專案的資料模型（Model）與資料庫存取層（Repository）的設計哲學。    

為了解決傳統 MVC 架構中 Model 職責不清的問題，並實現業務邏輯與資料持久化的徹底分離，我們採用了分層抽象（abstract class）與介面導向（interface）的設計模式。

<br/>

## 5 - 1 資料模型（Model）的分層抽象

此專案使用的 Model 不僅僅是資料庫資料表的映射，更是具備方法的物件（object）。為了讓這些方法有共通標準，使程式碼能重複使用，因此將 Model 設計成兩個層次。

### 介面定義（ Interface ）：行為的合約

* **TokenCapableInterface:** 這個介面定義了所有帶有 Token 的 Model 必須具備的最基礎合約。例如，取得 Token 欄位名稱、獲取 Token 過期時間等方法。這確保了所有 Token Model 都遵循同一套公開的規則。

* **TokenStatusInterface:** 此介面繼承自 **TokenCapableInterface**，並擴充了與狀態管理相關的合約，例如透過 proceedTo() 方法來處理狀態轉換。這讓介面設計更具針對性，符合介面隔離原則（Interface Segregation Principle）。

### 抽象類別（ Abstract Class ）：共通實作的基石

* **BaseTokenModel:** 此 Abstract Class 實作了 TokenCapableInterface，封裝了 Token Model 通用的方法。包含 Eloquent Scope，以及對 Token 欄位操作的方法。所有不具備 status 的 Token Model（例如 User）都直接繼承此類別，獲得這些功能。

* **AbstractTokenModel:** 此 Abstract Class 繼承自 **BaseTokenModel**，並實作了 TokenStatusInterface。主要處理帶有 status 的 Token Model。例如 status 轉換的通用操作，避免在這些 Model 重複撰寫相同的程式碼。

<br/>

透過這樣的抽象設計， Model 的結構清晰且易於擴展。如果未來新增一種不帶狀態的 Token Model，只需繼承 BaseTokenModel 即可；若需帶有 status，則繼承 AbstractTokenModel。

<br/>

## 5 - 2 資料庫存取層（Repository）的分層抽象

Repository 層作為 Service 層與 Model 層之間的唯一橋樑，其核心職責是封裝所有資料存取細節。我們同樣採用了分層抽象，確保 Repository 的職責單一，且讓程式碼可以重複使用。

### 介面定義（ Interface ）：資料存取的合約

* **BaseTokenRepositoryInterface:** 此 Interface 定義了最基礎的 CRUD 操作，如 create()、save() 和 delete()。它定義了 Repository 的共通合約，使 Service 層只需認識介面合約（Interface Contract），無需關注具體實作細節。

* **StatusTokenRepositoryInterface:** 此介面繼承自 **BaseTokenRepositoryInterface**，並新增了與狀態相關的查詢方法，例如 markStatus()，使介面更專注於 status 變更的責任，契合介面隔離原則的設計精神。


### 抽象類別（ Abstract Class ）：封裝 Eloquent 實作

* **BaseEloquentRepository:** 此抽象類別實作了 BaseTokenRepositoryInterface，並封裝了所有 Repository 的共通 Eloquent 實作細節。所有具體 Repository 都繼承此類別。

* **AbstractEloquentRepository:** 此類別繼承自 **BaseEloquentRepository** 並實作 StatusTokenRepositoryInterface，專門處理帶有 status 的 Repository 共通邏輯。它封裝了例如 cancelPending() 這樣的高階操作，避免了在多個子類中撰寫重複的程式碼。

<br/>

透過此設計，Service 層完全不必關心底層是用何種 ORM 進行資料存取。如果未來要更換資料庫技術，只需修改 Repository 內部的實作，而無需變動任何一個 Service 內的業務邏輯。

<br/>

## 5 - 3 Model 和 Repository 相關圖表 

<br/>

### Model 繼承與介面實作總覽

| Model 類別  | 抽象類別（Absteact Class） | 介面實作 (Interface)|
| :---                    | :---                  | :---                     |
| **`User`**              |  `BaseTokenModel`     |  `TokenCapableInterface` |
| **`Guest`**             |  `AbstractTokenModel` |  `TokenStatusInterface`  |
| **`PasswordUpdate`**    |  `AbstractTokenModel` |  `TokenStatusInterface`  |
| **`UserContactUpdate`** |  `AbstractTokenModel` |  `TokenStatusInterface`  |

<br/>

### Repository 繼承與介面實作總覽

| Repository 類別  | 抽象類別（Absteact Class）| 介面實作 (Interface) |
| :---                                   | :---                          | :---                             |
| **`EloquentUserRepository`**           |  `BaseEloquentRepository`     |  `BaseTokenRepositoryInterface`  |
| **`EloquentGuestRepository`**          |  `AbstractEloquentRepository` | `StatusTokenRepositoryInterface` |
| **`EloquentPasswordUpdateRepository`** |  `AbstractEloquentRepository` | `StatusTokenRepositoryInterface` |
| **`EloquentContactUpdateRepository`**  |  `AbstractEloquentRepository` | `StatusTokenRepositoryInterface` |

<br/>

### 繼承關係圖       

```             
      TokenCapableInterface  <──── extends ───────  TokenStatusInterface 
                ▲                                           ▲
                |                                           | 
    implements  |                                           | implements 
                |                                           |                                              
          BaseTokenModel  <─────── extends ───────   AbstractTokenModel

      BaseEloquentRepository <──── extends ────── AbstractEloquentRepository
                |                                           |
    implements  |                                           | implements
                |                                           |
                ▼                                           ▼
   BaseTokenRepositoryInterface <─ extends ───  StatusTokenRepositoryInterface
```

<br/>
<br/>
<br/>

# 第六章：驗證機制與策略模式的應用

本章將詳細介紹本專案的 Token 驗證機制，這不僅是身分驗證的核心，也實現無狀態（Stateless）、可擴展、安全性的關鍵。設計時參考了樣板方法模式（Template Method Pattern）與策略模式（Strategy Pattern），讓不同類型的 Token（如註冊、登入）能以各自獨立的單元操作進行處理，同時保持程式碼的整潔與一致性。

<br/>

## 6 - 1 Token 驗證機制的核心設計理念

此系統的 Token 驗證機制採用 Stateless 設計，伺服器不需儲存任何 Session 資訊。當使用者帶著 Token 發來請求時，伺服器只負責驗證 Token 本身的有效性，這讓系統更容易擴充其他功能。

設計時將 Token 驗證的流程抽象化為以下幾個步驟，並用程式碼來實現：

- **Token 的生成：** 為不同的業務流程，生成各自的 Token。
- **Token 的驗證：** 檢查 Token 是否有效，並判斷是否過期。
- **Token 的處理：** 根據 Token 的驗證結果，執行對應的操作，例如清除過期的 Token 或刷新登入狀態。

<br/>


## 6 - 2 策略模式與樣板方法模式的實現

為了實現高彈性、低耦合的驗證機制，結合了兩種設計模式：樣板方法模式和策略模式。  

MemberAuthService::verifyToken() 方法作為樣板，定義了所有 Token 驗證都必須遵循的固定流程。而這個流程中需要變動的每個步驟，則委託給不同的策略類別來完成。

- ### TokenStrategyInterface：定義驗證合約

    這是整個驗證流程的核心合約。它定義了所有 Token 策略都必須具備的公開方法，確保不論是何種 Token 類型，它們都能以統一的方式被處理。這個介面包含了：

    - `resolveModel()`： 根據 Token 字串查找對應的 Model。
    - `isExpired()`： 檢查 Model 的 Token 是否過期。
    - `handleExpired()`： 處理 Token 過期後的邏輯。
    - `generateToken()`： 產生一個新的 Token 字串。

- ### AbstractTokenStrategy：共通的方法

    為了避免重複程式碼，所以選擇建立了一個抽象策略類別 AbstractTokenStrategy。   

    它實作了 TokenStrategyInterface 中大部分合約方法。透過這個 Abstract Class，只需在具體的策略類別中，實作少數專屬的方法，例如指定對應的 Model 類別，就能輕鬆建立新的 Token 策略。


- ### TokenStrategyRegistry：策略模式的中央管理員
    TokenStrategyRegistry 是實現策略模式的關鍵元件。它扮演著中央管理員的角色，負責管理所有具體的策略類別。  

    在設計中，TokenStrategyRegistry 採用建構子注入 (Constructor Injection) 的方式，將所有可用的策略實例化並儲存在內部。這樣當 MemberAuthService 需要處理特定 Token 時，它只需透過 get() 方法，就能輕鬆地取得對應的策略實例。

    這種設計帶來了兩個主要優勢：  

    **低耦合**： MemberAuthService 不需知道每一個具體策略的存在，它只依賴於 TokenStrategyRegistry 這個管理員。   
    **高彈性**： 如果需要新增或修改 Token 策略，只需在 TokenStrategyRegistry 中進行調整，無需改其他的程式碼。

<br/>

### 具體策略類別

| 策略類別  | 對應 Model | 主要職責與特點 |
| :---                             | :---                | :---                                            |
| **`LoginTokenStrategy`**         | `User`              | 處理登入用的 Token，會返回專屬於登入的過期訊息。        |
| **`PasswordTokenStrategy`**      | `PasswordUpdate`    | 處理忘記密碼流程的 Token，返回專屬於密碼重設的過期訊息。 |
| **`RegisterTokenStrategy`**      | `Guest`             | 處理新用戶註冊驗證的 Token，返回專屬於註冊的過期訊息。   |
| **`UpdateContactTokenStrategy`** | `UserContactUpdate` | 處理更新聯絡資訊的 Token，返回專屬於通訊變更的過期訊息。 |

<br/>

### Token 類型與應用場景總覽

| Token 類型 | 應用場景 | 處理流程 |
| :---                       | :---       | :--- |
| **`bearer_token`**         | 登入驗證    | 用於使用者登入後的身分驗證。每次成功的請求都會重新產生 Token，以維持登入狀態並確保安全性。 |
| **`register_token`**       | 註冊驗證    | 用於新使用者註冊後的信箱驗證。一旦使用者透過 Token 完成驗證，該 Token 便會立即失效，防止被二次使用。 |
| **`password_token`**       | 密碼重設    | 用於忘記密碼的重設流程。這個 Token 有效期較短，且在密碼重設成功後會立即失效。 |
| **`update_contact_token`** | 聯絡資訊變更 | 用於變更使用者信箱或手機號碼等聯絡資訊。這個 Token 確保只有合法使用者能變更自己的重要資訊。 |

<br/>
<br/>
<br/>

# 第七章：會員註冊與驗證流程的實作

本章將透過一個完整的業務場景——會員註冊與驗證流程，來展示我們前幾章所設計的架構如何實際應用。這個流程將涵蓋從使用者提交註冊資訊，到後端處理、寄送驗證信，以及最終完成帳號驗證的整個過程。

<br/>

## 7 - 1 註冊流程的整體架構

整個註冊流程被拆解為三個獨立但相互協作的階段，以確保程式碼職責單一，流程清晰易懂。

- **啟動註冊流程**： 使用者在前端填寫信箱並提交，請求會送至 MemberRegisterController::registerRun。在這個階段，系統會啟動驗證流程，產生 register_token、暫存使用者資料，並寄送驗證信。

- **授權設定密碼頁面**： 使用者收到驗證信後，點擊信中的連結。這個請求會送至 MemberRegisterController::setPassword，由後端驗證 Token 的有效性，如果通過，則載入設定密碼的頁面。

- **完成註冊**： 使用者填寫完密碼並送出後，請求會送至 MemberRegisterController::completeRegistration。在這個階段，系統會進行最終的 Token 驗證與資料庫寫入，正式完成註冊並導向成功頁面。


<br/>

## 7 - 2 啟動註冊驗證流程： Orchestrator 與策略模式

註冊流程的核心，是透過 VerificationEmailOrchestrator 和 RegisterVerificationStrategy 協同完成。  
這種設計模式的應用，將流程控制與具體邏輯徹底分離。

**VerificationEmailOrchestrator：流程的編排者**  
- 這個類別扮演著編排者（Orchestrator）的角色，它定義了「寄送驗證信」的固定流程骨架   
  例如：驗證請求 -> 建立紀錄 -> 寄送郵件
- 透過 dispatchVerification() 方法，它會根據傳入的類型（例如 register），動態取得對應的策略物件，  
  並執行固定的 verificationFlow()。這完美體現了樣板方法模式。

<br/>

**RegisterVerificationStrategy：註冊驗證策略的具體實作**
- 此類別是策略模式的具體實作，它專注於「註冊」這個單一流程。它實作了 VerificationStrategyInterface 的方法，
  並將具體的邏輯封裝在其中。
- validateAndPrepareRequest()：驗證使用者輸入的信箱是否符合格式且尚未被註冊。
- createAndUpdateRecord()：這個方法是流程的關鍵。它會： 
    
    - 將該信箱之前尚未完成的註冊紀錄標記為取消（cancelPending）
    - 建立新的 Guest 紀錄並將狀態設為 pending
    - 透過 MemberAuthService 產生一個唯一的 register_token
    - 利用 EloquentGuestRepository 將 Token 儲存到 Guest 紀錄中
- dispatchVerificationEmail()：將帶有驗證連結的郵件寄送給使用者

<br/>

這段流程的程式碼展示了各個服務是如何協同工作的：MemberRegisterService 呼叫 Orchestrator，Orchestrator 呼叫 RegisterVerificationStrategy，而 Strategy 再回頭呼叫 MemberAuthService 和 EloquentGuestRepository 來完成具體操作。


<br/>

## 7 - 3 授權與完成註冊：Token 的最終驗證

當使用者收到驗證信並點擊連結後，流程進入第二個階段，由 MemberRegisterController 負責處理。這個階段的程式碼展示了 MemberAuthService 如何被應用，以確保整個流程的安全性。

### 載入設定密碼頁面 (setPassword)

當使用者點擊驗證連結時，請求會導向 MemberRegisterController::setPassword 方法。此時，系統會執行以下驗證：

- **Token 有效性驗證**： UnitRegisterService::verifyRegisterToken 方法會呼叫 MemberAuthService，使用 RegisterTokenStrategy 來驗證傳入的 token 是否有效且未過期。

- **信箱匹配驗證**： 同時，系統也會比對 URL 中的信箱與 Token 查詢到的 Guest 紀錄信箱是否一致，以防止惡意連結。

<br/>
如果任何一項驗證失敗，使用者會被導向登入頁面並收到錯誤訊息。只有在驗證成功後，系統才會安全地渲染設定密碼的頁面。

### 完成註冊與資料庫寫入 (completeRegistration)

使用者在設定密碼頁面填寫完新密碼後，提交請求至 MemberRegisterController::completeRegistration。這是註冊流程的最後一個關鍵步驟，由 UnitRegisterService::createMember 方法處理：

- **核心業務邏輯**： 這個方法的核心邏輯被封裝在一個`資料庫交易 (DB::transaction)` 中，確保資料庫操作不會變更到一半後失敗，只會全部完成或全部失敗。它會執行以下動作：  
    
    - 將原有的 Guest 紀錄狀態標記為 completed
    - 在 User 資料表中建立一筆新的正式會員紀錄
    - 為新會員產生一個登入用的 bearer_token，並儲存在 User 紀錄中

<br/>

這個步驟再次體現了你的 Token 生命週期管理。當註冊完成後，register_token 所對應的 Guest 紀錄狀態被標記為 completed，使得這個 Token 無法再次被使用。同時，系統為新會員產生一個全新的 bearer_token 用於登入，實現了不同 Token 的職責分離。

<br/>
<br/>
<br/>

# 第八章：總結

透過本專案，我成功地將抽象的設計理念轉化為實際可運作的程式碼，有效地解決了傳統單體架構中常見的職責不清與維護困難等問題。這個專案的核心價值與能力展現在以下幾個方面：

- **物件導向設計（OOP）的實踐**：  
    我遵循了 SOLID 原則，特別是單一職責原則（SRP）和依賴反轉原則（DIP）。透過將業務邏輯、資料存取與驗證流程等職責分離，確保了每個類別都只專注於單一任務。同時，藉由介面（interface）和抽象類別（abstract class），實現了高層模組不依賴於低層模組的具體實作，而是依賴於抽象，使得架構更具彈性。

- **設計模式的靈活應用**：
    本專案採用策略模式（Strategy Pattern）與樣板方法模式（Template Method Pattern）的結合應用。MemberAuthService 作為樣板方法，定義了固定的驗證流程骨架；而不同的 TokenStrategy 類別則作為具體策略，封裝了各自的驗證邏輯。這種設計讓系統在維護與擴充時，只需專注於新增或修改單一策略，而無需動到核心流程。

- **清晰的 Service-Repository 分層架構**：
    我設計了一個嚴謹的分層架構，將業務邏輯（Service）、資料庫操作（Repository）和模型（Model）徹底分離。Service 層透過介面與 Repository 層互動，完全不必關心底層是用何種 ORM 或資料庫技術。這種解耦設計，極大地提升了專案的可測試性與可維護性。

透過這個專案，我有幸能將對軟體設計模式和架構的理解付諸實踐，並從中學習如何將理論知識應用於解決實際業務問題。這套高度模組化且易於擴展的架構，為未來的功能開發與系統維護奠定了基礎。我期待能帶著這份經驗與對技術的熱情，持續精進，為團隊和專案貢獻自己的力量。
