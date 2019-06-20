`Fetcher` 包用于发送配置化的 api 请求.

根据配置中的 url, 请求方式, 响应字段名称, 验签类等配置, 通过 `GuzzleHttp` 发送 http 请求.

# 安装

运行 `composer require Unifreak/fetcher`

## 注册 service provider & facade

如果你不使用 `laravel/lumen`, 则可以跳过这一步.

如果你使用的 `laravel` 的版本大于 5.5, 则会自动注册 service provider 和 facade(参见 <https://laravel-news.com/package-auto-discovery>), 也可以跳过这一步.

如果你使用的 `lumen`, 则只需要把以下两行加入到 `<yourProject>/bootstrap/app.php` 中对应位置即可:

```php
<?php
// 注册 facade
if (!class_exists('Fetcher')) {
    class_alias(Unifreak\Fetcher\FetcherFacade::class, 'Fetcher');
}

// 注册 service provider
$app->register(Unifreak\Fetcher\FetcherServiceProvider::class);
```

`laravel` 类似, 但是要修改的文件可能不一样, 具体参考 `laravel` 的[官方文档](https://laravel.com/docs/5.7)

# 用法

```php
<?php
/**
 * api 的配置可以放到专门的配置文件中, 这里直接使用数组
 */
$api = [
    'url' => 'http://example.com/demo/api', // 请求地址, **注意不能带参数**
    'method' => 'POST', // 请求方法, 默认 'GET'
    'timeout' => 0.01, // 超时 (秒)
    'codeField' => 'payload.code', // 响应码字段, 可用 `.` 形式嵌套, 默认为 'code'
    'dataField' => 'payload.data', // 响应数据字段, 可用 `.` 形式嵌套, 默认为 'data'
    'messageField' => 'payload.msg', // 响应消息字段, 可用 `.` 形式嵌套, 默认为 'message'
    'successCode' => 2, // 当响应码为此值时, 认定为请求成功. 默认为 1
    'signer' => DemoSigner::class, // 签名类, 见下一段代码
];

/**
 * 假设已经配置了 facade, 则可以直接使用 Fetcher::fetch() 形式
 * 否则需要自己生成 Fetcher 实例, 如下: new Fetcher(new GuzzleHttp\Client());
 *
 * fetch() 函数的参数说明如下:
 * 1. 一个参数是 api 配置数组
 * 2. 第二个参数是请求参数数组(可选)
 * 3. 第三个参数是 cookie 数组(可选)
 * 4. 第四个参数是 header 数组(可选)
 */
$fetched = Fetcher::fetch(
    $api,
    ['parameter' => 'value'],
    ['debug_token' => 'some debug token cookie'],
    ['Header1' => ['Value1', 'Value2'], 'Header2' => 'Value3']
);

/**
 * fetcher() 函数始终返回一个 Fetched 对象 (此处即为 $fetched 变量), 可以通过此对象判断请求结果
 */
if ($fetched->ok()) { // 请求是否 ok
    var_dump([
        $fetched->code(), // 响应码
        $fetched->message(), // 响应消息
        $fetched->data() // 响应数据
    ]);
}
```

```php
<?php
namespace App\Signers;

use Unifreak\Fetcher\Signer\Signer;

class DemoSigner implements Signer
{
    /**
     * Signer 必须实现 sign() 方法. sign() 方法实现实际的签名逻辑
     */
    public function sign(array $payload) {
        return $payload + ['_sn' => 'abcdefg'];
    }
}
```

# 其他

根据各位使用中的情况, 后续新增特性可能包括:

- 解析 xml 等其他形式的响应 (目前只适用于 json 响应)
- 上传文件(multipart/form-data 以及 raw data)
- 配合环境敏感的配置包, 设定指定的配置文件
- 接口响应缓存 ?

如果你有其他的需求或点子, 可以直接联系项目作者
