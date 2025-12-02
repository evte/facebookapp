#!/bin/bash

# YuriApp - 添加租户独立域名到 hosts 文件（用于本地测试）

if [ -z "$1" ]; then
    echo "用法: ./add-tenant-domain.sh <完整域名>"
    echo ""
    echo "示例:"
    echo "  ./add-tenant-domain.sh tenant1.com"
    echo "  ./add-tenant-domain.sh my-client.net"
    echo "  ./add-tenant-domain.sh awesome-site.org"
    echo ""
    echo "注意: 生产环境中，需要在 DNS 服务商处将域名指向服务器 IP"
    exit 1
fi

DOMAIN=$1

# 检查域名格式
if [[ ! $DOMAIN =~ ^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$ ]]; then
    echo "❌ 无效的域名格式: $DOMAIN"
    echo "请输入完整的域名，例如: example.com"
    exit 1
fi

# 检查是否已存在
if grep -q "$DOMAIN" /etc/hosts; then
    echo "⚠️  域名 $DOMAIN 已存在于 hosts 文件中"
    echo ""
    echo "当前配置:"
    grep "$DOMAIN" /etc/hosts
    exit 0
fi

# 添加到 hosts（用于本地测试）
echo "127.0.0.1 $DOMAIN" | sudo tee -a /etc/hosts > /dev/null

if [ $? -eq 0 ]; then
    echo "✅ 已添加本地测试域名: $DOMAIN -> 127.0.0.1"
    echo ""
    echo "📋 下一步操作:"
    echo "  1. 在中央后台创建租户"
    echo "  2. 在租户配置中添加域名: $DOMAIN"
    echo "  3. 访问: http://$DOMAIN/tenant"
    echo ""
    echo "🌐 生产环境部署:"
    echo "  - 在 DNS 服务商添加 A 记录"
    echo "  - 将 $DOMAIN 指向服务器公网 IP"
    echo "  - 配置 SSL 证书（Let's Encrypt）"
    echo ""

    # 刷新 DNS 缓存
    echo "正在刷新 DNS 缓存..."
    sudo dscacheutil -flushcache
    sudo killall -HUP mDNSResponder
    echo "✅ DNS 缓存已刷新"
else
    echo "❌ 添加失败，请检查权限"
    exit 1
fi
