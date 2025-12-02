# Nginx 配置指南

Nginx 配置文件已生成，请按以下步骤完成设置。

## 📋 配置文件位置

- **Nginx 配置**: `/opt/homebrew/etc/nginx/servers/yuriapp.conf`
- **项目目录**: `/Users/zhoujie/project/yuriapp`

## 🚀 快速设置（推荐）

### 1. 运行自动配置脚本

```bash
cd /Users/zhoujie/project/yuriapp
./setup-nginx.sh
```

这个脚本会自动：
- ✅ 测试 Nginx 配置
- ✅ 启动 PHP-FPM
- ✅ 设置项目权限
- ✅ 重启 Nginx

### 2. 添加租户域名

创建租户后，使用以下命令添加域名到 hosts：

```bash
./add-tenant-host.sh tenant1
```

这会自动添加 `tenant1.localhost` 到你的 hosts 文件。

---

## 🔧 手动设置（如果自动脚本失败）

### 步骤 1: 测试 Nginx 配置

```bash
sudo nginx -t
```

### 步骤 2: 启动 PHP-FPM

```bash
# 检查 PHP-FPM 状态
brew services list | grep php

# 如果未运行，启动它
brew services start php
```

### 步骤 3: 设置项目权限

```bash
cd /Users/zhoujie/project/yuriapp
chmod -R 775 storage bootstrap/cache
```

### 步骤 4: 重启 Nginx

```bash
# 方法 1: 重新加载配置
sudo nginx -s reload

# 方法 2: 完全重启
brew services restart nginx
```

### 步骤 5: 添加 hosts 条目

编辑 hosts 文件：

```bash
sudo nano /etc/hosts
```

添加以下行：

```
127.0.0.1 tenant1.localhost
127.0.0.1 tenant2.localhost
```

保存后刷新 DNS 缓存：

```bash
sudo dscacheutil -flushcache
sudo killall -HUP mDNSResponder
```

---

## ✅ 验证配置

### 1. 检查 Nginx 状态

```bash
brew services list | grep nginx
```

应该显示 `started`。

### 2. 检查 PHP-FPM 状态

```bash
brew services list | grep php
```

应该显示 `started`。

### 3. 测试域名解析

```bash
ping tenant1.localhost
```

应该显示 `127.0.0.1` 响应。

### 4. 访问网站

打开浏览器访问：

- **中央管理后台**: http://localhost/admin
- **租户后台**: http://tenant1.localhost/tenant

---

## 🐛 常见问题

### 问题 1: 访问显示 502 Bad Gateway

**原因**: PHP-FPM 未运行

**解决**:
```bash
brew services start php
```

### 问题 2: 访问显示 404 Not Found

**原因**: Nginx 配置的 root 路径不正确

**解决**: 检查配置文件中的路径是否正确：
```bash
cat /opt/homebrew/etc/nginx/servers/yuriapp.conf | grep root
```

### 问题 3: 租户域名无法访问

**原因**: hosts 文件未配置或 DNS 缓存未刷新

**解决**:
```bash
# 检查 hosts
cat /etc/hosts | grep localhost

# 添加域名
./add-tenant-host.sh tenant1

# 刷新 DNS
sudo dscacheutil -flushcache
sudo killall -HUP mDNSResponder
```

### 问题 4: 权限错误

**原因**: Laravel 存储目录权限不足

**解决**:
```bash
cd /Users/zhoujie/project/yuriapp
chmod -R 775 storage bootstrap/cache
chown -R $USER:staff storage bootstrap/cache
```

---

## 📊 查看日志

### Nginx 访问日志
```bash
tail -f /opt/homebrew/var/log/nginx/yuriapp-central.access.log
tail -f /opt/homebrew/var/log/nginx/yuriapp-tenant.access.log
```

### Nginx 错误日志
```bash
tail -f /opt/homebrew/var/log/nginx/yuriapp-central.error.log
tail -f /opt/homebrew/var/log/nginx/yuriapp-tenant.error.log
```

### Laravel 日志
```bash
tail -f storage/logs/laravel.log
```

---

## 🔄 重启服务

### 重启 Nginx
```bash
brew services restart nginx
```

### 重启 PHP-FPM
```bash
brew services restart php
```

### 重启所有服务
```bash
brew services restart nginx
brew services restart php
```

---

## 📝 配置文件说明

配置文件包含两个 server 块：

1. **中央后台** (`localhost`)
   - 监听端口: 80
   - 域名: localhost, 127.0.0.1
   - 用于访问中央管理后台

2. **租户后台** (`*.localhost`)
   - 监听端口: 80
   - 域名: 泛域名匹配 `*.localhost`
   - 用于所有租户访问

---

## 🎯 下一步

1. ✅ 运行 `./setup-nginx.sh` 完成初始设置
2. ✅ 访问 http://localhost/admin 登录中央后台
3. ✅ 创建第一个租户
4. ✅ 使用 `./add-tenant-host.sh tenant1` 添加租户域名
5. ✅ 访问 http://tenant1.localhost/tenant 查看租户后台

---

需要帮助？请查看主 README.md 文件或检查日志文件。
