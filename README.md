# TOTP-PHP

![PHP 版本](https://img.shields.io/badge/php-%3E%3D8.2-brightgreen) ![许可证](https://img.shields.io/badge/license-MIT-blue) [![Packagist 下载量](https://img.shields.io/packagist/dm/kayon-ariel/totp-php.svg?label=%E4%B8%8B%E8%BD%BD%E9%87%8F)](https://packagist.org/packages/kayon-ariel/totp-php)

TOTP-PHP 是一个用于生成和校验基于时间的一次性密码（TOTP）的 PHP 库，兼容 RFC 6238，可与 Google Authenticator 等 TOTP 应用配合使用。

## 功能

- 生成符合 RFC 6238 的六位 TOTP 验证码。
- 生成随机 Base32 密钥。
- 校验验证码，并允许配置时间窗口偏差。
- 生成供验证器应用导入的 `otpauth://` URI。

## 安装

通过 Composer 安装：

```bash
composer require kayon-ariel/totp-php
```

需要 PHP 8.2 或更高版本。

## 快速开始

```php
<?php

require 'vendor/autoload.php';

use TotpPhp\Totp;

$totp = new Totp();
$secret = $totp->createSecret();
$code = $totp->getCode($secret);

echo "密钥：{$secret}\n";
echo "验证码：{$code}\n";

if ($totp->verifyCode($secret, $code)) {
    echo '验证成功';
} else {
    echo '验证失败';
}
```

## 生成验证器 URI

将 URI 转换为二维码后，可供 TOTP 验证器应用扫描导入。此方法生成 URI，不负责绘制二维码图像。

```php
<?php

require 'vendor/autoload.php';

use TotpPhp\Totp;

$totp = new Totp();
$secret = $totp->createSecret();
$payload = $totp->getQrCodePayload($secret, 'user@example.com', '我的应用');

echo $payload;
```

标签、密钥和发行方会按 URI 规则进行百分号编码。

## API

- `createSecret(int $secretLength = 16): string`：生成随机 Base32 密钥，长度须为 16 至 128 个字符；超出范围时抛出 `InvalidArgumentException`。
- `getCode(string $secret, ?int $timeSlice = null): string`：生成六位验证码。省略 `$timeSlice` 时使用当前 30 秒时间步；密钥无效或为空时抛出 `InvalidArgumentException`。
- `verifyCode(string $secret, string $code, int $discrepancy = 1): bool`：校验六位验证码。`$discrepancy` 表示允许的前后时间步数，默认允许当前时间步及其相邻时间步。
- `getQrCodePayload(string $secret, string $label, ?string $issuer = null): string`：生成验证器 URI；发行方参数可省略。

## 安全说明

请安全保存每位用户的密钥。生产环境还应限制验证码尝试次数，并记录已接受的时间步，避免验证码被重放。具体策略应结合应用的认证流程实现。

## 许可证

本库采用 MIT 许可证，详见 [LICENSE.md](LICENSE.md)。其中包含源自 `PHPGangsta/GoogleAuthenticator` 的代码，其原始 BSD 许可证及版权信息保留在许可证文件中。

## 贡献与联系

欢迎提交问题和改进建议。联系邮箱：[kayonariel@gmail.com](mailto:kayonariel@gmail.com)。
