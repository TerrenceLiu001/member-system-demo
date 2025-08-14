# Member System Demo：基於 Laravel 的模組化會員系統

## 目錄

- [專案核心亮點](#專案核心亮點)
- [功能總覽](#功能總覽)
- [技術使用](#技術使用)
- [環境建置](#環境建置)
- [雲端部署](#雲端部署)
- [技能對應與實務經驗](#技能對應與實務經驗)
- [專案架構圖](#專案架構圖)
- [功能模組](#功能模組)
- [資料表設計](#資料表設計)
- [架構說明](#架構說明)
- [聯絡方式](#聯絡方式)

這是一個以 Laravel 12 開發的完整會員系統範例，旨在展示如何運用分層架構與多種設計模式，解決實務中複雜的業務流程與驗證邏輯。本專案遵循 SOLID 原則 與 高內聚、低耦合 的設計理念，實現了可擴展且易於維護的程式碼。

核心功能涵蓋：會員註冊、登入、Email 驗證、忘記密碼與資料編輯等，並採用自訂 Token 驗證機制，提供安全與流暢的使用者體驗。

---

## 專案核心亮點

- **分層架構設計**   
  採用 Service-Repository 的分層架構，將業務邏輯與資料庫操作徹底分離。Controller 專注於處理請求與調用服務，讓程式碼清楚易讀、容易維護與並能夠進行測試。Service 層進一步細分為主 Service、UnitService、Orchestrator 與 Strategy 等角色，以實現職責分明與邏輯協同。 

- **抽象流程與設計模式**   
  將「寄送驗證信」等共通流程獨立出來，由 Orchestrator 負責統籌，再搭配策略模式（Strategy Pattern），針對註冊、忘記密碼、Email 變更等不同情境實作對應策略。這樣的設計巧妙結合樣板方法模式與策略模式，不僅減少重複程式碼，也提升系統的彈性與擴充能力。

- **物件導向設計（OOP）與 SOLID 原則**  
  專案應用了 SOLID 原則，透過 Interface 與 Abstract Class 來實踐依賴反轉原則（Dependency Inversion Principle, DIP），確保高層模組不依賴低層模組的具體實作，使得整體架構方便日後擴展與維護。每個模組也遵循單一職責原則（Single Responsibility Principle, SRP），以維持良好的模組邊界。 

- **彈性 Token 驗證機制**  
  實作一套基於策略模式的 Token 驗證機制，將不同類型的 Token（如註冊、登入、密碼重設）處理流程封裝在各自的 Strategy 中。這種設計讓驗證流程彼此獨立、職責單一，便於擴充與維護，並確保系統的安全性與無狀態（Stateless）特性。 


## 功能總覽

- 會員註冊：包含 Email 驗證與密碼設定流程。
- 登入 / 登出：透過自訂的 Bearer Token 進行身分驗證
- 忘記密碼：安全的密碼重設流程。
- 個人資料設定（暱稱、性別、年齡層）
- Email 變更驗證：透過 Email 驗證確認變更，並搭配 AJAX 輪詢提供即時狀態更新。

---

## 技術使用

- **後端框架**：Laravel 12（PHP）
- **前端模板**：Blade 模板引擎, jQuery (AJAX), HTML/CSS
- **資料庫**：MySQL 8.0+
- **容器化**：Docker / Docker Compose
- **部署環境**：GCP Cloud Run
- **版本控制**：Git

---

## 環境建置

### 1. 環境要求

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

### 2. 複製專案與設定環境變數

首先複製專案到本地機，並接著設定環境變數。

```bash
  # clone 專案
  git clone https://github.com/TerrenceLiu001/member-system-demo.git

  # 進入專案，並複製「環境檔」
  cd member-system-demo
  cp .env/example .env
```
  
請編輯 `.env` 檔案，填入你的環境變數。

### 3. 啟動容器

在專案根目錄下，執行以下指令來建置並啟動所有服務。

```bash
  # 建立 images 和 container 並執行 
  docker-compose up --build -d
```

### 4. 進入容器並設定專案

進入 app 容器，執行專案所需的設定指令。

```bash
  docker exec -it app sh
```

進入容器後，請執行以下指令：

```bash
  # 產生 Laravel 應用程式金鑰
  php artisan key:generate

  # 執行資料庫遷移
  php artisan migrate
```

### 5. 訪問應用程式

現在，應用程式應該已經在運行中。可以在瀏覽器中訪問： [http://localhost:8081](http://localhost:8081)

---

## 雲端部署

本專案已針對雲端部署進行容器化配置，以下為部署至 GCP 的流程：  

### 1. 建置 images 並推送至 Artifact Registry

將 APP 的程式碼與相關套件，打包成一個可部署的映像檔(images)，並推送到 GCP 的映像檔倉庫(Artifact Registry)。  
Docker 相關檔案位於 `./docker/production/` 此生產環境中。

```bash
  # 在專案「根目錄」下建置 images 
  docker build -t [地區]-docker.pkg.dev/[專案 ID]/[倉庫名稱]/[映像檔名稱]:[Tag] -f ./docker/production/Dockerfile .

  # 請確保已透過 gcloud auth configure-docker 的指令完成驗證
  docker push [地區]-docker.pkg.dev/[專案ID]/[倉庫名稱]/[映像檔名稱]:[Tag]
```

### 2. 配置 Cloud SQL 與連線設定

在部署前，確保 Cloud Run 能夠安全、有效地連線到 Cloud SQL 資料庫。  

- IAM 權限： 在 Cloud SQL 的「連線」設定中，確保你的 Cloud Run 服務帳戶擁有 Cloud SQL 客戶 (Cloud SQL Client) 的 IAM 角色，以允許連線。  

- 連線名稱： Cloud SQL 實例的「連線名稱」(Connection name)，它將用於 Cloud Run 的設定。

### 3. 部署至 Cloud Run

在 Cloud Run 上啟動你的容器映像檔，並配置必要的環境變數與服務連線。

### 4. 部署成果

Member System Demo 的雲端部署 - [成果連結](https://member-center-app-54079994772.asia-east1.run.app/login)

- 測試帳號 / 密碼 : test834@test.com / Test0000 
- 測試帳號 / 密碼 : test258@test.com / Test0000 
---

## 技能對應與實務經驗

以下為我在本專案中展現的技術能力


| 技能項目 | 實作經驗與說明 |
| :--- | :--- |
| **PHP / Laravel 框架** | 專案基於 **Laravel 12** 框架，透過 **Service Layer** 實現業務邏輯與控制器職責分離，確保程式碼可讀性與可維護性。 |
| **軟體架構與設計模式**   | 運用 **Orchestrator** 結合 **策略模式（Strategy Pattern）**，將重複流程抽象化。在資料層則採用 **Repository Pattern**，實現業務邏輯與資料持久化解耦。 |
| **物件導向設計 (OOP)** | 專案中應用了 **SOLID 原則**，透過介面與抽象類別來實踐依賴反轉，使架構更具彈性與擴充性。 |
| **MySQL 資料庫**      | 具備 **Migration** 經驗，能進行資料庫結構的版本控制，並使用 **Seeder** 建立測試資料，簡化了環境建置流程。 |
| **Eloquent ORM**     | 在專案中，我透過 **Repository Pattern** 封裝了 Eloquent 的資料存取細節，將複雜的查詢邏輯與業務流程分離。 |
| **API 設計**          | 實作 Email 驗證狀態查詢 API，遵循 **RESTful** 設計原則，並以 **JSON** 格式回應，便於前端與後端協作。 |
| **非同步處理**         | 運用 **jQuery** 與 **AJAX** 技術，實作無頁面刷新的輪詢機制，提升使用者體驗。 |
| **版本控制**           | 熟悉以**功能分支**為主的開發流程，能將個人開發的程式碼提交至 `dev` 分支，並通過合併請求（Merge Request）的方式更新至 `main` 分支。|
| **技術文件撰寫**       | 本專案文件包含架構設計、程式碼說明與安裝教學，能清晰闡述設計理念，有助於他人理解與維護。 |


---

## 專案架構圖

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


---

## 功能模組

| 功能模組      | Controller                   | 說明  |
|------------ |------------------------------|----------------------------------------------------|
|  會員登入    | `MemberLoginController`      | 提供登入、登出功能
|  會員註冊    | `MemberRegisterController`   | 處理訪客註冊流程，包含電子郵件驗證與密碼設定 |
|  忘記密碼    | `ForgotPasswordController`   | 發送驗證信並重設密碼  |
|  聯絡資訊更新 | `UpdateContactController` + `PollingStatusController` | 使用者更新電子郵件，並透過輪詢機制即時驗證狀態  |
|  會員中心    | `MemberCenterController`    | 登入後的首頁與個人資料編輯功能  |

---

## 資料表設計

| Table 名稱                        |説明                                                   |
|----------------------------------|-------------------------------------------------------|
| member_center_guests             | 記錄尚未設定密碼的註冊訪客（含註冊驗證信 token）             |
| member_center_users              | 正式會員資料（含登入用 bearer token）                     |
| member_center_user_contact_update| Email/手機變更請求資料，追蹤變更狀態與驗證信（目前實作 Email）|
| member_center_password_update    | 忘記密碼的驗證 token 與狀態紀錄                           |

---
## 架構說明

- 🧩 系統架構與模組設計 👉 [`docs/architecture.md`](docs/architecture.md)
---

## 聯絡方式

若您對此專案或我的履歷有興趣，歡迎聯絡我：

- Email：dreaninvain@gmail.com
- GitHub: [TerrenceLiu001](https://github.com/TerrenceLiu001)
