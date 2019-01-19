<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/15
 * Time: 22:54
 */

namespace NashInject;

use NashInject\Exception\InjectorException;
use NashInject\Type\InjectorType;
use ReflectionFunctionAbstract;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;

class Injector
{
    protected $share, $shareDefine, $define, $making;

    /**
     * Injector constructor.
     * @throws InjectorException
     */
    public function __construct()
    {
        $this->share = [];
        $this->define = [];
        $this->shareDefine = [];
        $this->making = [];
        $this->share($this);
    }

    /**
     * @param $objectOrClassName
     * @param array $params
     * @param callable|null $prepare
     * @throws InjectorException
     */
    public function share($objectOrClassName, $params = [], ?callable $prepare = null)
    {
        if (is_string($objectOrClassName)) {
            $this->define($objectOrClassName, $params, $prepare);
            $this->shareDefine[$objectOrClassName] = $objectOrClassName;
        } elseif (is_object($objectOrClassName)) {
            $this->share[get_class($objectOrClassName)] = $objectOrClassName;
        } else {
            // 错误的数据类型
            throw new InjectorException('The first param of function "share" must be a string or object', InjectorException::ERROR_PARAM);
        }
    }

    /**
     * @param string $className
     * @param $params
     * @param callable|null $prepare
     */
    public function define(string $className, $params, ?callable $prepare = null)
    {
        $this->define[$className] = ['params' => $params, 'prepare' => $prepare];
    }

    /**
     * @param string $className
     * @param array $params
     * @return mixed
     * @throws InjectorException
     * @throws \ReflectionException
     */
    public function make(string $className, array $params = [])
    {
        if (key_exists($className, $this->share)) {
            return $this->share[$className];
        }
        if (key_exists($className, $this->making)) {
            // 依赖发生循环
            throw new InjectorException('The type of "' . $className . '" dependency loops', InjectorException::ERROR_DEPENDENT_CYCLE);
        }
        $this->making[$className] = $className;
        if (empty($this->define[$className])) {
            return $this->prepareExecute($this->makeObjectFromConstruct($className, $params), $this->define[$className] ?? null);
        }
        $definedParams = $this->define[$className]['params'];
        return $this->prepareExecute(
            is_callable($definedParams)
                ? $this->execute($definedParams, $params)
                : $this->makeObjectFromConstruct($className, array_merge($definedParams, $params))
            , $this->define[$className] ?? null
        );
    }

    /**
     * @param callable $call
     * @param array $params
     * @return mixed
     * @throws \ReflectionException
     */
    public function execute(callable $call, array $params = [])
    {
        $ref = $this->makeReflectFunction($call);
        $callParams = $this->signFunction($ref);
        $callParams = $this->makeCallParams($callParams, $params);
        return call_user_func_array($call, $callParams);
    }

    /**
     * @param $obj
     * @param $prepare
     * @return mixed
     * @throws InjectorException
     */
    protected function prepareExecute($obj, $prepare)
    {
        if (is_callable($prepare)) {
            call_user_func($prepare, $obj, $this);
        }
        if (key_exists($className = get_class($obj), $this->shareDefine)) {
            $this->share($obj);
        }
        unset($this->making[$className]);
        return $obj;
    }

    /**
     * @param string $className
     * @param array $params
     * @return mixed
     * @throws \ReflectionException
     */
    protected function makeObjectFromConstruct(string $className, array $params)
    {
        $ref = $this->makeReflectConstruct($className);
        if (is_null($ref)) {
            return new $className;
        }
        $callParams = $this->signFunction($ref);
        $callParams = $this->makeCallParams($callParams, $params);
        return new $className(...$callParams);
    }

    /**
     * @param array $callParams
     * @param array $inputParams
     * @return array
     */
    protected function makeCallParams(array $callParams, array $inputParams)
    {
        return array_map(function ($callParam) use ($inputParams) {
            if (empty($callParam['className'])) {
                return $this->makeCallParamFromInput($callParam, $inputParams);
            } elseif ($callParam['isInjectType']) {
                return $this->makeCallParamToType($callParam, $inputParams);
            }
            try {
                return $this->make($callParam['className']);
            } catch (\Exception $e) {
                if ($callParam['hasDefaultValue']) {
                    return $callParam['defaultValue'];
                } else {
                    // 无法产生参数
                    throw new InjectorException('Can\'t make param "$' . $callParam['name'] . '"', InjectorException::ERROR_CON_NOT_MAKE_PARAM, $e);
                }
            }
        }, $callParams);
    }

    /**
     * @param $callParam
     * @param $inputParams
     * @return mixed
     * @throws InjectorException
     */
    protected function makeCallParamFromInput($callParam, $inputParams)
    {
        if (key_exists($callParam['name'], $inputParams)) {
            return $inputParams[$callParam['name']];
        } elseif ($callParam['hasDefaultValue']) {
            return $callParam['defaultValue'];
        }
        // 无法产生参数
        throw new InjectorException('Can\'t make param "$' . $callParam['name'] . '"', InjectorException::ERROR_CON_NOT_MAKE_PARAM);
    }

    /**
     * @param $callParam
     * @param $inputParams
     * @return mixed
     * @throws InjectorException
     */
    protected function makeCallParamToType($callParam, $inputParams)
    {
        $paramClassName = $callParam['className'];
        if (key_exists($callParam['name'], $inputParams)) {
            return new $paramClassName($inputParams[$callParam['name']], $this);
        } elseif ($callParam['hasDefaultValue']) {
            return new $paramClassName($callParam['defaultValue'], $this);
        }
        // 无法产生参数
        throw new InjectorException('Can\'t make param "$' . $callParam['name'] . '"', InjectorException::ERROR_CON_NOT_MAKE_PARAM);
    }

    /**
     * @param callable $call
     * @return ReflectionFunctionAbstract
     * @throws \ReflectionException
     */
    protected function makeReflectFunction(callable $call): ReflectionFunctionAbstract
    {
        if (is_array($call)) {
            return new ReflectionMethod($call[0], $call[1]);
        } elseif (is_object($call)) {
            return new ReflectionMethod($call, '__invoke');
        }
        return new ReflectionFunction($call);
    }

    /**
     * @param string $className
     * @return ReflectionFunctionAbstract
     * @throws \ReflectionException
     */
    protected function makeReflectConstruct(string $className): ?ReflectionFunctionAbstract
    {
        return (new ReflectionClass($className))->getConstructor();
    }

    /**
     * @param ReflectionFunctionAbstract $ref
     * @return \ReflectionParameter[]
     */
    protected function signFunction(ReflectionFunctionAbstract $ref)
    {
        $params = $ref->getParameters();
        foreach ($params as $index => $param) {
            $params[$index] = ['name' => $param->getName()];
            if ($paramClass = $param->getClass()) {
                $params[$index]['className'] = $paramClass->getName();
                $params[$index]['isInjectType'] = $paramClass->isSubclassOf(InjectorType::class);
            }
            if ($params[$index]['hasDefaultValue'] = $param->isDefaultValueAvailable()) {
                $params[$index]['defaultValue'] = $param->getDefaultValue();
            }
        }
        return $params;
    }
}
