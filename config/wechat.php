<?php

return [
    // .env 文件中定义变量
    'miniapp' => [
        'app_id' => env('WECHAT_MINIAPP_ID'),
        'secret' => env('WECHAT_MINIAPP_SECRET'),
        'token' => env('WECHAT_MINIAPP_TOKEN'),
        'aes_key' => env('WECHAT_MINIAPP_AESKEY')
    ],
    'official_account' => [

    ]
];
