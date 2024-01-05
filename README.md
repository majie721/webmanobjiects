# webmanobjiects
webman 使用自定义路由,并将请求的参数路由参数转成自定义的对象


### 特别说明
- 用惯php数组一把梭的同学忽略这个包
- php版本必须大于等于8.1
- webman版本需要开启自动注入(使用php-di)
- 对于请求可以在support中创建一个请求的基类继承RequestBean, 可以自己选择熟悉的验证器实现参数的验证功能
- 在示例参数对象时,会对数据的类型进行严格校验


1.路由配置文件中添加自定义路由
````php
Route::group('/test', function () {
    Route::any('/{controller:.+}/{action}', function ($request, $controller, $action) {
        return BaseDispatcher::dispatch('/test/'.$controller, $action, $request, $request->all());
    });
});
````
2.创建请求数据对象Bean对象,这个对象必须继承RequestBean 
```php
<?php

namespace app\http\test\Product\Bean;

use Majie721\Webmanobjiects\RequestBean;

class CreateBean extends RequestBean
{

    public string $name = 'CC';

    public int $age;

    /**
     * @var array[] $list
     */
    public array $list;
    

```

3.创建控制器,控制器中的方法参数必须是RequestBean的子类,并且参数名必须和请求参数名一致
```php

<?php

namespace app\http\test\Product\V1;

use app\http\test\Product\Bean\CreateBean;

class IndexController
{

    public function createData(CreateBean $bean)
    {
        $age = $bean->age;
        $name = $bean->name;
        $list = $bean->list;
        return json_encode(['age'=>$age,'name'=>$name,'list'=>$list]);
    }
    
}


```
post请求示例(路由支持 `test/product/v1/index/create-data` 和 `test/product/v1/index/create_data` 和  `test/product/v1/index/createData` 方式)
```php
curl --location -g --request POST 'http://127.0.0.1:8787/test/product/v1/index/create-data' \
--header 'Content-Type: application/json' \
--header 'Accept: */*' \
--header 'Host: 127.0.0.1:8787' \
--header 'Connection: keep-alive' \
--data-raw '{
    "name": "C12C",
    "age": 13322,
    "list": [22222]
}'



