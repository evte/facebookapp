# YuriApp - 多租户管理系统

基于 FilamentPHP v4 和 stancl/tenancy 构建的多租户 SaaS 应用，支持域名识别和独立数据库。

## 系统特性

- ✅ **FilamentPHP v4** - 现代化的后台管理面板
- ✅ **多租户架构** - 基于 stancl/tenancy 的完整多租户解决方案
- ✅ **独立顶级域名** - 支持完全独立的域名（如 `client-a.com`、`client-b.net`）
- ✅ **域名识别** - 自动根据访问域名识别租户
- ✅ **域名健康检查** - 自动检测 DNS、SSL 和可访问性状态
- ✅ **独立数据库** - 每个租户拥有独立的数据库，数据完全隔离
- ✅ **应用凭证** - 每个租户独立的 App ID 和 App Secret 用于 API 认证
- ✅ **中央管理后台** - 超级管理员可以管理所有租户
- ✅ **租户独立后台** - 每个租户拥有自己的管理后台
- ✅ **白标签支持** - 租户可使用自己的品牌域名

## 系统架构

### 1. 中央管理后台（Admin Panel）
- 路径: `/admin`
- 功能: 管理所有租户、配置域名、查看系统数据
- 访问域名: `localhost` 或配置的中央域名

### 2. 租户后台（Tenant Panel）
- 路径: `/tenant`
- 功能: 租户自己的管理系统
- 访问域名: 租户配置的独立域名

## 快速开始

### 1. 安装依赖

```bash
composer install
```

### 2. 配置环境变量

复制 `.env.example` 到 `.env` 并配置数据库：

```env
APP_NAME=YuriApp
APP_URL=http://localhost

# 中央数据库配置
DB_CONNECTION=central
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yuriapp_central
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. 运行迁移

```bash
php artisan migrate
```

### 4. 创建管理员账户

```bash
php artisan tinker
```

在 tinker 中执行：

```php
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password')
]);
```

### 5. 启动开发服务器

```bash
php artisan serve
```

访问 `http://localhost:8000/admin` 登录中央管理后台。

## 租户管理

### 创建新租户

1. 登录中央管理后台 `/admin`
2. 进入"租户管理"
3. 点击"创建"按钮
4. 填写租户信息：
   - **租户ID**: 唯一标识符（例如: `tenant1`）
   - **租户名称**: 显示名称
   - **邮箱**: 联系邮箱
5. 配置域名（**支持独立顶级域名**）：
   - **开发环境**: `tenant1.localhost`
   - **生产环境**: `client-domain.com`（租户自己的域名）
6. 保存后系统会自动：
   - 创建独立数据库
   - 运行租户数据库迁移
   - 配置域名路由

### 配置域名访问

#### 方式一：本地测试域名（开发环境）

使用快捷脚本：

```bash
./add-tenant-domain.sh tenant1.com
```

或手动编辑 `/etc/hosts`：

```
127.0.0.1 tenant1.com
127.0.0.1 client-site.net
```

#### 方式二：独立顶级域名（生产环境）

租户使用自己的域名：

1. 租户在其域名 DNS 设置添加 A 记录指向服务器 IP
2. 在中央后台为租户配置该域名
3. 系统自动识别并路由到正确的租户

**详细配置请查看**: [独立域名配置指南](CUSTOM_DOMAINS.md)

访问租户后台：
- 开发环境: `http://tenant1.com:8000/tenant`（使用 php artisan serve）
- Nginx 环境: `http://tenant1.com/tenant`
- 生产环境: `https://client-domain.com/tenant`

## 数据库结构

### 中央数据库（yuriapp_central）

存储租户信息和域名配置：

- `tenants` - 租户表
  - `id` - 租户ID
  - `name` - 租户名称
  - `email` - 邮箱
  - `app_id` - 应用ID（API认证）
  - `app_secret` - 应用密钥（API认证）
  - `created_at` / `updated_at` - 时间戳

- `domains` - 域名表
  - `id` - 主键
  - `domain` - 域名
  - `tenant_id` - 关联租户

- `users` - 管理员用户表

### 租户数据库（自动创建）

每个租户的独立数据库：

- 数据库名格式: `tenant{tenant_id}`
- 包含租户自己的业务数据表
- 完全隔离，互不影响

## 开发指南

### 添加租户资源

在租户后台添加新的资源：

```bash
php artisan make:filament-resource Product --panel=tenant
```

资源文件将创建在 `app/Filament/Tenant/Resources/` 目录下。

### 添加中央资源

在中央管理后台添加新的资源：

```bash
php artisan make:filament-resource SystemSetting --panel=admin
```

### 租户迁移

创建租户数据库迁移：

```bash
php artisan make:migration create_products_table
```

将迁移文件移动到 `database/migrations/tenant/` 目录，系统会在创建租户时自动运行。

## 生产环境部署

### 1. 域名配置

在生产环境中，需要配置泛域名解析：

```
*.yourdomain.com -> Your Server IP
```

### 2. 环境变量

更新 `.env` 文件：

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# 配置中央域名
TENANCY_CENTRAL_DOMAINS=yourdomain.com,www.yourdomain.com
```

### 3. 更新配置

编辑 `config/tenancy.php`：

```php
'central_domains' => [
    'yourdomain.com',
    'www.yourdomain.com',
],
```

## 技术栈

- **Laravel 11** - PHP 框架
- **FilamentPHP v4** - 管理面板
- **stancl/tenancy** - 多租户包
- **Livewire** - 前端交互
- **Tailwind CSS** - 样式框架

## 默认登录凭据

- **邮箱**: `admin@example.com`
- **密码**: `password`

⚠️ **重要**: 生产环境请立即修改默认密码！

## 📚 相关文档

- [独立域名配置指南](CUSTOM_DOMAINS.md) - 配置独立顶级域名
- [域名健康检查](DOMAIN_HEALTH_CHECK.md) - 检测域名配置状态
- [应用凭证使用](APP_CREDENTIALS.md) - API 认证和集成
- [Nginx 配置指南](NGINX_SETUP.md) - Nginx 详细配置

## 许可证

MIT License

## 支持

如有问题，请提交 Issue 或联系技术支持。
