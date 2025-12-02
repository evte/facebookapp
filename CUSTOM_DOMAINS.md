# 独立顶级域名配置指南

YuriApp 支持为每个租户配置完全独立的顶级域名。

## 🌐 域名架构说明

### 传统子域名模式（不推荐）
```
admin.example.com     → 中央后台
tenant1.example.com   → 租户1
tenant2.example.com   → 租户2
```

### ✅ 独立顶级域名模式（推荐）
```
admin.yourdomain.com  → 中央后台
client-a.com          → 租户1 的独立域名
awesome-site.net      → 租户2 的独立域名
my-business.org       → 租户3 的独立域名
```

## 🔧 工作原理

1. **Nginx 配置**：使用 `default_server` 捕获所有域名请求
2. **Laravel Tenancy**：根据请求的域名识别租户
3. **数据库切换**：自动切换到对应租户的数据库
4. **完全隔离**：每个租户数据完全独立

## 📝 本地开发环境配置

### 步骤 1: 添加测试域名到 hosts

使用提供的脚本：

```bash
./add-tenant-domain.sh tenant1.com
./add-tenant-domain.sh client-site.net
./add-tenant-domain.sh awesome-app.org
```

或手动编辑 `/etc/hosts`：

```bash
sudo nano /etc/hosts
```

添加：

```
127.0.0.1 tenant1.com
127.0.0.1 client-site.net
127.0.0.1 awesome-app.org
```

### 步骤 2: 在中央后台创建租户

1. 访问 `http://localhost/admin`
2. 登录后点击 "Tenants"
3. 创建新租户，填写：
   - **租户ID**: `tenant1`
   - **名称**: `第一个租户`
   - **邮箱**: `contact@tenant1.com`

### 步骤 3: 配置租户域名

在租户编辑页面的"域名配置"部分：

1. 点击"添加域名"
2. 输入完整域名：`tenant1.com`
3. 保存

系统会自动：
- ✅ 保存域名到数据库
- ✅ 配置路由识别
- ✅ 启用该域名的租户访问

### 步骤 4: 访问租户后台

```bash
# 测试域名解析
ping tenant1.com

# 浏览器访问
http://tenant1.com/tenant
```

## 🚀 生产环境部署

### 方案 A: 每个租户独立域名

**适用场景**：给客户提供白标签（White Label）服务

**配置步骤**：

#### 1. DNS 配置

在每个租户的域名 DNS 服务商处添加 A 记录：

```
类型: A
主机: @
值: 你的服务器IP
TTL: 3600
```

示例（在域名注册商如 GoDaddy、Cloudflare）：

| 类型 | 名称 | 内容 | TTL |
|------|------|------|-----|
| A | @ | 123.45.67.89 | 3600 |
| A | www | 123.45.67.89 | 3600 |

#### 2. 中央域名配置

为中央后台配置专用域名，编辑 `.env`：

```env
APP_URL=https://admin.yoursaas.com
```

更新 `config/tenancy.php`：

```php
'central_domains' => [
    'admin.yoursaas.com',
    'www.admin.yoursaas.com',
],
```

更新 Nginx 配置 `/opt/homebrew/etc/nginx/servers/yuriapp.conf`：

```nginx
server {
    listen 80;
    server_name admin.yoursaas.com www.admin.yoursaas.com;
    # ... 其他配置
}
```

#### 3. SSL 证书配置

为中央域名和每个租户域名配置 SSL：

```bash
# 安装 certbot
brew install certbot

# 为中央域名申请证书
sudo certbot --nginx -d admin.yoursaas.com

# 为租户域名申请证书
sudo certbot --nginx -d tenant1.com
sudo certbot --nginx -d client-site.net
```

**自动化脚本**（在创建租户时自动申请证书）：

编辑 `app/Filament/Resources/Tenants/Pages/CreateTenant.php`：

```php
protected function afterCreate(): void
{
    $tenant = $this->record;

    // 创建数据库和运行迁移
    $tenant->createDatabase();
    $tenant->run(function () {
        artisan('migrate', ['--force' => true]);
    });

    // 自动申请 SSL 证书（生产环境）
    if (app()->environment('production')) {
        foreach ($tenant->domains as $domain) {
            $this->requestSSLCertificate($domain->domain);
        }
    }
}

private function requestSSLCertificate(string $domain): void
{
    try {
        exec("sudo certbot --nginx -d {$domain} --non-interactive --agree-tos -m admin@yoursaas.com", $output, $returnCode);

        if ($returnCode === 0) {
            logger()->info("SSL certificate obtained for {$domain}");
        }
    } catch (\Exception $e) {
        logger()->error("Failed to obtain SSL for {$domain}: " . $e->getMessage());
    }
}
```

### 方案 B: 泛域名 + 独立域名混合

**适用场景**：提供免费子域名 + 付费自定义域名

**配置示例**：

```
免费用户:
- tenant1.yoursaas.com
- tenant2.yoursaas.com

付费用户:
- custom-domain.com
- another-domain.net
```

**Nginx 配置**：

```nginx
# 中央后台
server {
    listen 80;
    server_name admin.yoursaas.com;
    # ... 配置
}

# 泛域名（免费租户）
server {
    listen 80;
    server_name *.yoursaas.com;
    # ... 配置
}

# 独立域名（付费租户）- 捕获所有其他域名
server {
    listen 80 default_server;
    server_name _;
    # ... 配置
}
```

## 🔐 安全配置

### 1. 限制中央域名访问

确保只有中央域名可以访问 `/admin`：

编辑 `app/Providers/Filament/AdminPanelProvider.php`：

```php
->middleware([
    // ... 其他中间件
    \App\Http\Middleware\EnsureCentralDomain::class,
])
```

创建中间件：

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next)
    {
        $centralDomains = config('tenancy.central_domains');

        if (!in_array($request->getHost(), $centralDomains)) {
            abort(404);
        }

        return $next($request);
    }
}
```

### 2. 防止域名劫持

在 `config/tenancy.php` 中验证域名：

```php
'bootstrappers' => [
    // 确保这个在最前面
    \Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
    // ... 其他 bootstrappers
],
```

## 📊 域名管理最佳实践

### 1. 域名验证

在允许租户添加域名前，验证域名所有权：

```php
// 在保存域名前验证
public function verifyDomainOwnership(string $domain): bool
{
    $verificationCode = Str::random(32);

    // 要求租户添加 TXT 记录
    // TXT _yuriapp-verify.{domain} = {verificationCode}

    $records = dns_get_record("_yuriapp-verify.{$domain}", DNS_TXT);

    foreach ($records as $record) {
        if ($record['txt'] === $verificationCode) {
            return true;
        }
    }

    return false;
}
```

### 2. 域名状态管理

在 `domains` 表添加状态字段：

```php
// Migration
Schema::table('domains', function (Blueprint $table) {
    $table->enum('status', ['pending', 'verified', 'active', 'suspended'])
          ->default('pending');
    $table->string('verification_code')->nullable();
    $table->timestamp('verified_at')->nullable();
});
```

### 3. 多域名支持

一个租户可以有多个域名：

```
tenant1:
  - tenant1.com (主域名)
  - www.tenant1.com
  - tenant1.net (备用域名)
```

## 🎯 测试清单

本地开发环境测试：

- [ ] 中央后台可通过 `localhost` 访问
- [ ] 租户域名 `tenant1.com` 已添加到 hosts
- [ ] 访问 `http://tenant1.com/tenant` 显示租户后台
- [ ] 不同域名加载不同租户数据
- [ ] 域名在中央后台可以增删改

生产环境测试：

- [ ] DNS A 记录已配置
- [ ] SSL 证书已安装
- [ ] HTTPS 访问正常
- [ ] 域名验证流程正常
- [ ] 域名切换无数据泄露

## 💡 常见问题

### Q: 租户能使用自己已有的域名吗？

**A**: 可以！租户需要：
1. 在其域名 DNS 设置中添加 A 记录指向你的服务器
2. 在你的平台创建租户并配置该域名
3. 等待 DNS 传播（通常 24-48 小时）

### Q: 如何处理 www 和非 www 域名？

**A**: 两种方案：
1. 允许租户同时添加两个域名
2. 使用 Nginx 重定向：

```nginx
server {
    listen 80;
    server_name www.tenant1.com;
    return 301 http://tenant1.com$request_uri;
}
```

### Q: 支持多少个租户域名？

**A**: 理论上无限制。Nginx 的 `default_server` 会捕获所有请求，Laravel 根据域名动态识别租户。

### Q: 域名更换会影响数据吗？

**A**: 不会。租户数据存储在独立数据库中，通过租户 ID 关联，域名只是访问入口。

## 📚 相关文档

- [README.md](README.md) - 项目总览
- [NGINX_SETUP.md](NGINX_SETUP.md) - Nginx 详细配置
- [stancl/tenancy 文档](https://tenancyforlaravel.com/docs)

---

需要帮助？请检查日志文件或联系技术支持。
