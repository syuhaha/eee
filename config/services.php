<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | FYJT Remote Service
    |--------------------------------------------------------------------------
    |
    | 配置说明（客户端/调用方）：
    | - `endpoint` 为远程接收提交的完整 URL，例如 https://api.example.com/fyjt/submit
    | - `secret` 为与对方约定的共享密钥，用于计算请求签名（仅在本机作为 HMAC key 使用，勿在代码库中明文存放）
    |
    | 说明：时间窗（drift）与 nonce TTL（防重放）为接收方（远端服务）需要的校验参数，
    | 仅需在对端服务配置并执行验证。客户端无需在本地保存或使用这些阈值。
    */
    'fyjt' => [
        'endpoint' => env('FYJT_REMOTE_ENDPOINT'),
        'secret' => env('FYJT_REMOTE_SECRET'),
    ],

];
