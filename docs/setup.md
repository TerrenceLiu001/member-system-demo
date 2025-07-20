## 環境需求

- PHP 8.1+
- Laravel 12
- MySQL / SQLite
- Composer
- Node.js + npm


## 安裝教學

### 1. Clone 專案

```bash
    git clone https://github.com/TerrenceLiu001/member-system-demo.git
```

### 2. 安裝套件

```bash
    composer install
    npm install
    npm run dev
```

### 3. 複製並修改環境設定檔

   - 3.1  複製環境設定檔 (` .env.example `)

        ```bash
        cp .env.example .env
        ```

   - 3.2  請執行以下指令，會自動生成 APP_KEY 寫入 ` .env ` 中

        ```bash
        php artisan key:generate
        ```

   - 3.3  請點開 ` .env ` ，並修改以下資料

       - 資料庫設定

           - 預設為 MySQL，請在「資料庫」設定以下欄位 ：   
            DB_DATABASE（資料庫名稱）、DB_USERNAME（使用者）、DB_PASSWORD（密碼）

 
            ```bash
                # ================================================
                # 資料庫設定（請依照你的本地環境設定）
                # ================================================

                DB_DATABASE=database_name
                DB_USERNAME=database_user
                DB_PASSWORD=database_password
            ```
       - 郵件參數設定 

           - 預設為使用 google 的 smtp，請「郵件」設定以下欄位：    
            MAIL_USERNAME （郵件伺服器帳號）、MAIL_PASSWORD （郵件伺服器密碼/應用程式密碼）

           - 若您使用其他郵件服務（如 Mailtrap、SendGrid 等），請依需求修改下列參數：

            ```bash
                # ================================================
                # 郵件設定（可用 Gmail 或 Mailtrap）
                # ================================================

                MAIL_MAILER=smtp
                MAIL_SCHEME=null
                MAIL_HOST=smtp.gmail.com
                MAIL_PORT=587
                MAIL_USERNAME=your_email@example.com
                MAIL_PASSWORD=your_password
                MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
                MAIL_FROM_NAME="${APP_NAME}"
            ```
### 4. 執行資料庫遷移

```bash
    php artisan migrate 
```

## 運行專案

   請先在終端機執行以下指令，啟動虛擬機  
    
    php artisan serve
    
    
   啟動後，在網址列中輸入： http://localhost:8000/login  即可使用系統

