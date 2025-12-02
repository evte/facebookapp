#!/bin/bash

# YuriApp - 添加租户域名到 hosts 文件

if [ -z "$1" ]; then
    echo "用法: ./add-tenant-host.sh <租户名>"
    echo "示例: ./add-tenant-host.sh tenant1"
    exit 1
fi

TENANT_NAME=$1
DOMAIN="${TENANT_NAME}.localhost"

# 检查是否已存在
if grep -q "$DOMAIN" /etc/hosts; then
    echo "⚠️  域名 $DOMAIN 已存在于 hosts 文件中"
    exit 0
fi

# 添加到 hosts
echo "127.0.0.1 $DOMAIN" | sudo tee -a /etc/hosts > /dev/null

if [ $? -eq 0 ]; then
    echo "✅ 已添加: $DOMAIN -> 127.0.0.1"
    echo ""
    echo "现在可以通过以下地址访问："
    echo "  http://$DOMAIN/tenant"
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
