<?php

namespace Majie721\Webmanobjiects;

use Majie721\Webmanobjiects\Exception\ParamException;
use Majie721\Webmanobjiects\Exception\RouteException;
use support\Container;
use support\Request;

class BaseDispatcher
{
    public static array $controllerClassStack = [];
    public static array $actionClassStack     = [];
    public static array $banAction            = [
        '__construct', '__destruct', '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep',
        '__wakeup', '__toString', '__invoke', '__set_state', '__clone', '__debugInfo'
    ];

    /**
     * @param string $controllerPath
     * @param string $action
     * @param \support\Request $request
     * @param array $params
     * @param string|null $controllerDir
     * @return mixed
     * @throws \app\library\Router\Exception\ParamException
     * @throws \app\library\Router\Exception\RouteException
     * @throws \Exception
     */
    public static function dispatch(string $controllerPath, string $action, Request $request, array $params = [], ?string $controllerDir = null):mixed
    {
        $action = self::camelize($action);
        if (in_array($action, self::$banAction)) {
            throw new RouteException(sprintf('action %s is not allowed', $action));
        }
        $controllerClass = self::getControllerClass($controllerPath, $controllerDir);
        return self::paramsDispatch($controllerClass, $action, $request, $params);
    }

    /**
     * @param string $controllerClass
     * @param string $action
     * @param \support\Request $request
     * @param array $params
     * @return mixed
     * @throws \app\library\Router\Exception\ParamException|\app\library\Router\Exception\RouteException|\Exception
     */
    private static function paramsDispatch(string $controllerClass, string $action, Request $request, array $params = []):mixed
    {
        $controller = self::getControllerObject($controllerClass);
        $className  = get_class($controller);
        if (!method_exists($controller, $action)) {
            throw new RouteException(sprintf('The method [%s] is not accessible.', $action));
        }

        $reflectionMethod = new \ReflectionMethod($controller, $action);
        if (!$reflectionMethod->isPublic()
            || $reflectionMethod->isStatic()
            || is_callable([$controller, $action]) === false) {
            throw new RouteException(sprintf('The method [%s] is not accessible.', $action));
        }
        $reflectionParams = $reflectionMethod->getParameters();
        $args             = [];
        foreach ($reflectionParams as $parameter) {
            $parameterName = $parameter->getName();
            $hasType       = $parameter->hasType();
            if (!$hasType) {//控制器方法中的变量没有定义类型
                throw new \Exception(sprintf('Undefined parameter type for [%s] in [%s::%s]. Please define the type for the parameter."', $parameterName, $className, $action));
            }
            if (!$parameter->getType()->isBuiltin()) {//内置类型是是Class类型,仅支持WebmanRequestInterface和Request注入
                $class = $parameter->getType()->getName();
                if (!class_exists($class)) {
                    throw new \Exception(sprintf('The class of parameter [%s] does not exist in [%s::%s].', $parameterName, $className, $action));
                }
                $ReflectionClass = new \ReflectionClass($class);
                if ($ReflectionClass->implementsInterface(WebmanRequestInterface::class)) {
                    $args[] = new $class($params);
                } else if ($ReflectionClass->isInstance($request)) {
                    $args[] = $request;
                } else {
                    throw new \Exception(sprintf('The class of parameter [%s] does not allow injection in [%s::%s].', $parameterName, $className, $action));
                }
            } else if (isset($params[$parameterName])) { //注入标准类型
                //类型检查
                self::checkParamType($parameter->getType()->getName(), $parameterName, $params[$parameterName]);
                $args[] = $params[$parameterName];
            } else if ($parameter->isDefaultValueAvailable()) { //注入默认值
                $args[] = $parameter->getDefaultValue();
            } else if ($parameter->allowsNull()) { //注入null
                $args[] = null;
            } else {
                throw new ParamException('Missing parameter value.');
            }
        }
        return $controller->{$action}(...$args);
    }

    /**
     * @param string $typeName
     * @param string $paramName
     * @param $value
     * @return void
     * @throws \app\library\Router\Exception\ParamException
     */
    private static function checkParamType(string $typeName, string $paramName, $value): void
    {
        $ok = match ($typeName) {
            'int' => is_int($value) || preg_match('/^-?[1-9]?\d*$/', $value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            'true' => $value === true,
            'false' => $value === false,
            'string' => is_string($value) || is_numeric($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'mixed' => true,
            default => throw new ParamException(sprintf('Parameter type error for the parameter [%s]. Please check the parameter type.', $paramName)),
        };

        if (!$ok) {
            throw new ParamException(sprintf('Parameter type error for the parameter [%s]. The expected type is [%s], but the actual type is [%s]. Please check the parameter type.', $paramName, $typeName, gettype($value)));
        }
    }

    /**
     * 获取控制器对象
     * @param string $className
     * @return object
     * @throws \Exception
     */
    private static function getControllerObject(string $className): object
    {
        if (!class_exists("\support\Container")) {
            throw new \Exception("请先打开webman里依赖自动注入功能");
        }
        $controllerReuse = config('app.controller_reuse');
        if (!!$controllerReuse) {
            return Container::get($className);
        } else {
            return Container::make($className, []);
        }
    }


    /**
     * 根据控制器路径获取控制器类
     * @param string $controllerPath
     * @param string|null $controllerDir
     * @return string
     * @throws \Exception
     */
    private static function getControllerClass(string $controllerPath, ?string $controllerDir = null): string
    {
        if (isset(self::$controllerClassStack[$controllerPath])) {
            $controllerClass = self::$controllerClassStack[$controllerPath];
        } else {
            $controllerClass                             = self::buildControllerClass($controllerPath, $controllerDir);
            self::$controllerClassStack[$controllerPath] = $controllerClass;
        }

        if (!class_exists($controllerClass)) {
            throw new \Exception(sprintf('controller class %s not exists', $controllerClass));
        }
        return $controllerClass;
    }


    /**
     * 获取控制器类
     * @param string $controllerPath
     * @param string|null $controllerDir
     * @return string
     * @throws \Exception
     */
    private static function buildControllerClass(string $controllerPath, ?string $controllerDir = null): string
    {
        $controllerArr = explode('/', $controllerPath);
        //-或者_分割的路由都转成大驼峰,如(user_login或者user-login)转成UserLogin
        $controllerArr  = array_map(function ($item) {
            return self::camelize($item);
        }, $controllerArr);
        $path           = implode('/', $controllerArr);
        $controllerDir  = $controllerDir ?: self::defaultControllerDir();
        $controllerFile = sprintf('%s%sController.php', $controllerDir, $path);
        if (!file_exists($controllerFile)) {
            throw new \Exception(sprintf('controller file [%s] not exists', substr($controllerFile, strlen(app_path()))));
        }

        //获取命名空间
        $content = file_get_contents($controllerFile, false, null, 0, 1000);
        preg_match('/namespace\s+(.*);/', $content, $matches);
        $namespace = $matches[1] ?? '';
        if (!$namespace) {
            throw new \Exception(sprintf('controller file %s namespace not exists', substr($controllerFile, strlen(app_path()))));
        }
        //返回命名空间和类名
        return $namespace . '\\' . $controllerArr[count($controllerArr) - 1] . 'Controller';
    }

    /**
     * 转成大驼峰(如user_login或者user-login转成UserLogin)
     * @param string $str
     * @return string
     */
    private static function camelize(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }

    /**
     * 默认控制器目录
     * @return string
     */
    private static function defaultControllerDir(): string
    {
        return app_path() . '/http/';
    }
}