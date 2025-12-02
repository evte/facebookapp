# 应用凭证使用指南

每个租户都有独立的应用ID (App ID) 和应用密钥 (App Secret)，用于 API 认证和第三方集成。

## 📋 字段说明

### App ID（应用ID）
- **用途**: 公开的应用标识符
- **格式**: `app_xxxxxxxxxxxxxxxx`
- **特点**: 可以公开，用于识别租户应用
- **使用场景**:
  - API 请求头
  - OAuth 客户端ID
  - 第三方服务集成标识

### App Secret（应用密钥）
- **用途**: 私密的认证密钥
- **格式**: 32位随机字符串
- **特点**: 必须保密，不能暴露给客户端
- **使用场景**:
  - API 签名验证
  - OAuth 客户端密钥
  - 服务端认证

## 🔐 安全性

### 自动生成
创建租户时，系统会自动生成：
- App ID: `app_` + 16位随机字符
- App Secret: 32位随机字符

### 存储安全
- App Secret 在表单中使用密码字段，默认隐藏
- 列表页面默认不显示 App Secret
- 建议在生产环境加密存储（可选）

### 最佳实践
1. ✅ 不要在客户端代码中暴露 App Secret
2. ✅ 使用 HTTPS 传输凭证
3. ✅ 定期轮换密钥
4. ✅ 记录凭证使用日志
5. ✅ 限制 API 访问频率

## 💻 使用示例

### 1. API 认证

#### 基础认证（Header）

```php
// 租户调用 API
$client = new GuzzleHttp\Client();

$response = $client->request('POST', 'https://api.example.com/endpoint', [
    'headers' => [
        'X-App-ID' => 'app_xxxxxxxxxxxxxxxx',
        'X-App-Secret' => 'your-secret-key-here',
        'Content-Type' => 'application/json',
    ],
    'json' => [
        'data' => 'your-data'
    ]
]);
```

#### 服务端验证

```php
// 在你的 API 中间件中验证
namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;

class ValidateAppCredentials
{
    public function handle($request, Closure $next)
    {
        $appId = $request->header('X-App-ID');
        $appSecret = $request->header('X-App-Secret');

        // 验证凭证
        $tenant = Tenant::where('app_id', $appId)
                       ->where('app_secret', $appSecret)
                       ->first();

        if (!$tenant) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        // 将租户信息附加到请求
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}
```

### 2. 签名验证（更安全）

#### 生成签名

```php
// 租户端：生成请求签名
function generateSignature($appId, $appSecret, $timestamp, $data)
{
    $stringToSign = $appId . $timestamp . json_encode($data);
    return hash_hmac('sha256', $stringToSign, $appSecret);
}

$timestamp = time();
$data = ['user_id' => 123, 'action' => 'create'];

$signature = generateSignature(
    'app_xxxxxxxxxxxxxxxx',
    'your-secret-key-here',
    $timestamp,
    $data
);

// 发送请求
$response = $client->request('POST', 'https://api.example.com/endpoint', [
    'headers' => [
        'X-App-ID' => 'app_xxxxxxxxxxxxxxxx',
        'X-Timestamp' => $timestamp,
        'X-Signature' => $signature,
    ],
    'json' => $data
]);
```

#### 服务端验证签名

```php
// API 中间件
public function handle($request, Closure $next)
{
    $appId = $request->header('X-App-ID');
    $timestamp = $request->header('X-Timestamp');
    $signature = $request->header('X-Signature');

    // 检查时间戳（防止重放攻击）
    if (abs(time() - $timestamp) > 300) { // 5分钟有效期
        return response()->json(['error' => 'Request expired'], 401);
    }

    // 查找租户
    $tenant = Tenant::where('app_id', $appId)->first();

    if (!$tenant) {
        return response()->json(['error' => 'Invalid app ID'], 401);
    }

    // 验证签名
    $data = $request->all();
    $stringToSign = $appId . $timestamp . json_encode($data);
    $expectedSignature = hash_hmac('sha256', $stringToSign, $tenant->app_secret);

    if (!hash_equals($expectedSignature, $signature)) {
        return response()->json(['error' => 'Invalid signature'], 401);
    }

    $request->merge(['tenant' => $tenant]);

    return $next($request);
}
```

### 3. OAuth 2.0 集成

```php
// 使用 App ID 和 App Secret 作为 OAuth 凭证
'providers' => [
    'your_service' => [
        'client_id' => env('TENANT_APP_ID'),
        'client_secret' => env('TENANT_APP_SECRET'),
        'redirect' => 'https://your-app.com/callback',
    ],
],
```

### 4. Webhook 验证

```php
// 接收 Webhook 时验证来源
public function handleWebhook(Request $request)
{
    $appId = $request->input('app_id');
    $signature = $request->header('X-Webhook-Signature');

    $tenant = Tenant::where('app_id', $appId)->first();

    if (!$tenant) {
        abort(401);
    }

    // 验证签名
    $payload = $request->getContent();
    $expectedSignature = hash_hmac('sha256', $payload, $tenant->app_secret);

    if (!hash_equals($expectedSignature, $signature)) {
        abort(401);
    }

    // 处理 webhook
    // ...
}
```

## 🔄 密钥轮换

### 实现密钥轮换机制

```php
// 在 Tenant 模型中添加
public function rotateAppSecret(): string
{
    $newSecret = \Illuminate\Support\Str::random(32);

    // 可选：保留旧密钥一段时间（宽限期）
    $this->old_app_secret = $this->app_secret;
    $this->old_secret_expires_at = now()->addDays(7);

    $this->app_secret = $newSecret;
    $this->save();

    return $newSecret;
}

// 验证时同时检查新旧密钥
public function validateSecret(string $secret): bool
{
    // 检查当前密钥
    if (hash_equals($this->app_secret, $secret)) {
        return true;
    }

    // 检查旧密钥（宽限期内）
    if ($this->old_app_secret &&
        $this->old_secret_expires_at &&
        $this->old_secret_expires_at->isFuture() &&
        hash_equals($this->old_app_secret, $secret)) {
        return true;
    }

    return false;
}
```

### 在 Filament 中添加轮换按钮

```php
// 在 TenantResource 的 Actions 中添加
use Filament\Actions\Action;

public static function getActions(): array
{
    return [
        Action::make('rotate_secret')
            ->label('轮换密钥')
            ->icon('heroicon-o-arrow-path')
            ->requiresConfirmation()
            ->action(function (Tenant $record) {
                $newSecret = $record->rotateAppSecret();

                Notification::make()
                    ->title('密钥已轮换')
                    ->body("新密钥: {$newSecret}")
                    ->success()
                    ->send();
            }),
    ];
}
```

## 📊 监控和日志

### 记录 API 使用

```php
// 创建 API 日志表
Schema::create('api_logs', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id');
    $table->string('app_id');
    $table->string('endpoint');
    $table->string('method');
    $table->integer('status_code');
    $table->ipAddress('ip_address');
    $table->text('user_agent')->nullable();
    $table->timestamps();
});

// 中间件中记录
ApiLog::create([
    'tenant_id' => $tenant->id,
    'app_id' => $tenant->app_id,
    'endpoint' => $request->path(),
    'method' => $request->method(),
    'status_code' => $response->status(),
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

## 🚨 安全建议

### 1. 加密存储（可选）

```php
// 在 Tenant 模型中使用加密
protected $casts = [
    'app_secret' => 'encrypted',
];
```

### 2. 限流保护

```php
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/api/endpoint', [ApiController::class, 'handle']);
});
```

### 3. IP 白名单（可选）

```php
// 在 tenants 表添加 allowed_ips 字段
Schema::table('tenants', function (Blueprint $table) {
    $table->json('allowed_ips')->nullable();
});

// 验证中间件
public function handle($request, Closure $next)
{
    $tenant = $request->tenant;

    if ($tenant->allowed_ips && !in_array($request->ip(), $tenant->allowed_ips)) {
        return response()->json(['error' => 'IP not allowed'], 403);
    }

    return $next($request);
}
```

## 📱 前端使用（谨慎）

**警告**: 不要在客户端暴露 App Secret！

### 仅使用 App ID（公开 API）

```javascript
// ✅ 安全：仅使用 App ID
fetch('https://api.example.com/public/data', {
    headers: {
        'X-App-ID': 'app_xxxxxxxxxxxxxxxx'
    }
})
```

### 使用临时令牌（推荐）

```javascript
// ✅ 安全：先用 App ID + Secret 换取临时令牌（后端操作）
// 然后前端使用临时令牌

// 1. 后端生成临时令牌
$token = $tenant->createToken('api-access')->plainTextToken;

// 2. 前端使用令牌
fetch('https://api.example.com/data', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
})
```

## 🔍 故障排查

### 常见错误

1. **401 Unauthorized**
   - 检查 App ID 是否正确
   - 检查 App Secret 是否匹配
   - 确认租户是否存在

2. **签名验证失败**
   - 检查时间戳是否在有效期内
   - 确认数据序列化方式一致
   - 验证哈希算法是否匹配

3. **请求被限流**
   - 检查请求频率
   - 增加限流配置
   - 使用缓存优化

## 📚 相关文档

- [README.md](README.md) - 项目总览
- [CUSTOM_DOMAINS.md](CUSTOM_DOMAINS.md) - 域名配置
- [Laravel Sanctum 文档](https://laravel.com/docs/sanctum) - API 令牌

---

需要帮助？请查看日志文件或联系技术支持。
