#!/bin/bash

echo "🚀 YuriApp Nginx 配置脚本"
echo "================================"
echo ""

# 1. 检查 Nginx 配置
echo "📝 步骤 1: 测试 Nginx 配置..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx 配置测试通过"
else
    echo "❌ Nginx 配置有误，请检查配置文件"
    exit 1
fi

echo ""

# 2. 检查 PHP-FPM 状态
echo "🔍 步骤 2: 检查 PHP-FPM 状态..."
PHP_FPM_STATUS=$(brew services list | grep php)
echo "$PHP_FPM_STATUS"

if echo "$PHP_FPM_STATUS" | grep -q "started"; then
    echo "✅ PHP-FPM 已运行"
else
    echo "⚠️  PHP-FPM 未运行，正在启动..."
    brew services start php
    sleep 2
    echo "✅ PHP-FPM 已启动"
fi

echo ""

# 3. 设置项目权限
echo "🔐 步骤 3: 设置项目权限..."
cd /Users/zhoujie/project/yuriapp
chmod -R 775 storage bootstrap/cache
echo "✅ 权限设置完成"

echo ""

# 4. 重启 Nginx
echo "🔄 步骤 4: 重启 Nginx..."
sudo nginx -s reload

if [ $? -eq 0 ]; then
    echo "✅ Nginx 重启成功"
else
    echo "⚠️  Nginx 重启失败，尝试完全重启..."
    brew services restart nginx
    echo "✅ Nginx 已重启"
fi

echo ""
echo "================================"
echo "🎉 配置完成！"
echo ""
echo "📍 访问地址："
echo "  - 中央管理后台: http://localhost/admin"
echo "  - 租户后台示例: http://tenant1.localhost/tenant"
echo ""
echo "⚠️  提醒："
echo "  1. 请在 /etc/hosts 中添加租户域名"
echo "  2. 默认登录: admin@example.com / password"
echo "  3. 请先在中央后台创建租户"
echo ""
