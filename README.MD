<h1 align="center">WechatLib</h1>

## 说明

基于 [easywechat](https://github.com/w7corp/easywechat) 二次开发（easywechat PHP >= 8.0.2）

## 必要

- php >= 7.0
- composer

## 安装

```shell
$ composer require suunnn/wechatlib
```

## 用法

### Laravel

+ 配置微信账号
```
config/wechat.php
```
```php
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
```

+ 定义 provider

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use WeChatLib\MiniApp\AccessToken;
// use WeChatLib\OfficialAccount\AccessToken;
use WeChatLib\MiniApp\Application;
// use WeChatLib\OfficialAccount\Application;

class WeChatMiniAppProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('WeChatMiniApp', function () {
            $app = new Application(config('wechat.miniapp'));   // 实例化一个小程序应用对象
            // $app = new \WeChatLib\OfficialAccount\Application(config('wechat.official_account'));   // 实例化一个公众号应用对象

            //============== 可以设置已有的小程序或公众号 access token ==============/
            // $token = "ACCESS_TOKEN";
            // $accessToken = new AccessToken($app->getAccount()->getAppId(), $app->getAccount()->getSecret());    // 实例化 AccessToken 对象
            // $accessToken->setToken($token); // 传入 access token 的值
            // $app->setAccessToken($accessToken); // 给应用对象传入实例化的 AccessToken 对象
            //=================================================================/

            return $app;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function provides()
    {
        return ['WeChatMiniApp'];
    }
}
```

+ 定义 Facade

```php
<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class WeChatMiniApp extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'WeChatMiniApp';
    }
}

```
+ 框架配置

```app/config.php```

```php
'providers' => [
    /*
     * Package Service Providers...
     */
     App\Providers\WeChatMiniAppProvider::class,
],
```

```php
'aliases' => [
    ...
    'WeChatMiniApp' => App\Facades\WeChatMiniApp::class,
],
```

+ 使用

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use WeChatMiniApp;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // 上传图片
        $response = WeChatMiniApp::getClient()->request(
            'POST',                                         // method 方式
            'https://api.weixin.qq.com/shop/img/upload',    // 微信小程序 api 接口
            // 请求 options 详见 vendor/symfony/http-client-contracts/HttpClientInterface.php
            // 提交微信的数据主要为 body 或 json 等
            [
                'body' => [
                    'resp_type' => 1,
                    'upload_type' => 1,
                    'img_url' => "https://xxx",
                ]
            ]
        )->toArray();

//        // 验证场景值
//        $response = WeChatMiniApp::getClient()->request('POST', 'https://api.weixin.qq.com/shop/scene/check', [
//            'json' => [
//                'scene' => $scene
//            ]
//        ])->toArray();
    }
}
```

### 通用
```php
<?php

use WeChatLib\MiniApp\AccessToken;
// use WeChatLib\OfficialAccount\AccessToken;
use WeChatLib\MiniApp\Application;
// use WeChatLib\OfficialAccount\Application;

class UserController
{
    public function store()
    {
        $app = new Application(config('wechat.miniapp'));   // 实例化一个小程序应用对象
        // $app = new \WeChatLib\OfficialAccount\Application(config('wechat.official_account'));   // 实例化一个公众号应用对象

        //============== 可以设置已有的小程序或公众号 access token ==============/
        // $token = "ACCESS_TOKEN";
        // $accessToken = new AccessToken($app->getAccount()->getAppId(), $app->getAccount()->getSecret());    // 实例化 AccessToken 对象
        // $accessToken->setToken($token); // 传入 access token 的值
        // $app->setAccessToken($accessToken); // 给应用对象传入实例化的 AccessToken 对象
        //=================================================================/

        $response = $app->getClient()->request(
            'POST',                                         // method 方式
            'https://api.weixin.qq.com/shop/img/upload',    // 微信小程序 api 接口
            // 请求 options 详见 vendor/symfony/http-client-contracts/HttpClientInterface.php
            // 提交微信的数据主要为 body 或 json 等
            [
                'body' => [
                    'resp_type' => 1,
                    'upload_type' => 1,
                    'img_url' => "https://xxx",
                ]
            ]
        )->toArray();

//        // 验证场景值
//        $response = WeChatMiniApp::getClient()->request('POST', 'https://api.weixin.qq.com/shop/scene/check', [
//            'json' => [
//                'scene' => $scene
//            ]
//        ])->toArray();
    }
}


```

## License

MIT
