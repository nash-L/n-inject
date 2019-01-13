<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2018/12/28
 * Time: 15:37
 */

namespace NashInject;

class Injector
{
    protected $_defineList, $_shareList, $_shareObjects, $_prepares, $_sourceMap;

    /**
     * Injector constructor.
     */
    public function __construct()
    {
        $this->_defineList = [];
        $this->_shareList = [];
        $this->_shareObjects = [];
        $this->_prepares = [];
        $this->_sourceMap = [];
        $this->share($this);
    }

    /**
     * @param string $className
     * @param $params
     * @param callable|null $prepare
     */
    public function define(string $className, $params, callable $prepare = null)
    {
        $this->prepare($className, $prepare);
        $this->_defineList[$className] = ['callable' => null, 'params' => []];
        if (is_callable($params)) {
            $this->_defineList[$className]['callable'] = $params;
        } elseif (is_array($params)) {
            $this->_defineList[$className]['params'] = $params;
        }
    }

    /**
     * @param string $className
     * @param array $params
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function make(string $className, array $params = [])
    {
        if (isset($this->_shareObjects[$className])) {
            return $this->_shareObjects[$className];
        }
        if (isset($this->_sourceMap[$className])) {
            $defineParams = [];
            $preDefineParams = [];
            if (isset($this->_shareList[$className])) {
                $preDefineParams = $this->_shareList[$className]['params'];
            } elseif (isset($this->_defineList[$className])) {
                $preDefineParams = $this->_defineList[$className]['params'];
            }
            foreach ($this->_sourceMap[$className]['params'] as $name => $defineParam) {
                if ($defineParam['type'] === 'fromInput') {
                    if (isset($params[$name])) {
                        $defineParams[] = $params[$name];
                    } elseif (isset($preDefineParams[$name])) {
                        $defineParams[] = $preDefineParams[$name];
                    } elseif (isset($defineParam['default'])) {
                        $defineParams[] = $defineParam['default'];
                    } else {
                        throw new \Exception('无法初始化参数', 0);
                    }
                } else {
                    if (isset($params[$name]) && is_object($params[$name]) && is_a($params[$name], $defineParam['className'])) {
                        $defineParams[] = $params[$name];
                    } elseif ($defineParam['type'] === 'fromClassWithInput' && isset($params[$name])) {
                        $defineParams[] = $this->make($defineParam['className'], ['data' => $params[$name]]);
                    } elseif ($defineParam['type'] === 'fromClass') {
                        $defineParams[] = $this->make($defineParam['className'], []);
                    } elseif (isset($preDefineParams[$name])) {
                        $defineParams[] = $preDefineParams[$name];
                    } elseif (isset($defineParam['default'])) {
                        $defineParams[] = $defineParam['default'];
                    } else {
                        throw new \Exception('无法初始化参数', 0);
                    }
                }
            }
            switch ($this->_sourceMap[$className]['type']) {
                case 'construct': $obj = new $className(...$defineParams);break;
                case 'callable': case 'closure': $obj = call_user_func_array($this->_sourceMap[$className]['key'], $defineParams);break;
                default: throw new \Exception('无法初始化参数', 0);
            }
            foreach ($this->_prepares as $classTempName => $prepare) {
                if (is_a($obj, $classTempName)) {
                    call_user_func($prepare, $obj);
                }
            }
            return $obj;
        }
        $obj = $this->makeObject($className, [], $params);
        foreach ($this->_prepares as $classTempName => $prepare) {
            if (is_a($obj, $classTempName)) {
                call_user_func($prepare, $obj);
            }
        }
        return $obj;
    }

    /**
     * @param string $className
     * @param array $relationList
     * @param array $params
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function makeObject(string $className, array $relationList, array $params)
    {
        if (isset($this->_shareObjects[$className])) {
            return $this->_shareObjects[$className];
        }
        if (in_array($className, $relationList)) {
            throw new \Exception('出现循环依赖', 0);
        }
        $relationList[] = $className;
        $makeFun = null;
        if (isset($this->_shareList[$className]) && !empty($this->_shareList[$className]['callable'])) {
            $makeFun = $this->_shareList[$className]['callable'];
        } elseif (isset($this->_defineList[$className]) && !empty($this->_defineList[$className]['callable'])) {
            $makeFun = $this->_defineList[$className]['callable'];
        } else {
            if (isset($this->_shareList[$className])) {
                $params = array_merge($this->_shareList[$className]['params'], $params);
            } elseif (isset($this->_defineList[$className])) {
                $params = array_merge($this->_defineList[$className]['params'], $params);
            }
            $makeFun = new \ReflectionClass($className);
        }
        $this->_sourceMap[$className] = ['type' => 'construct', 'key' => $className, 'params' => []];
        if (is_array($makeFun)) {
            $this->_sourceMap[$className] = ['type' => 'callable', 'key' => $makeFun, 'params' => []];
            $makeFun = new \ReflectionMethod($makeFun[0], $makeFun[1]);
        } elseif (is_string($makeFun) || $makeFun instanceof \Closure) {
            $this->_sourceMap[$className] = ['type' => 'closure', 'key' => $makeFun, 'params' => []];
            $makeFun = new \ReflectionFunction($makeFun);
        } else {
            $makeFun = $makeFun->getConstructor();
        }
        if (is_null($makeFun)) {
            if (isset($this->_shareList[$className])) {
                $this->_shareObjects[$className] = new $className;
                unset($this->_sourceMap[$className]);
                return $this->_shareObjects[$className];
            }
            return new $className;
        }
        $makeParams = $makeFun->getParameters();
        foreach ($makeParams as $index => $parameter) {
            $pName = $parameter->getName();
            $pHasDef = $parameter->isDefaultValueAvailable();
            $this->_sourceMap[$className]['params'][$pName] = [];
            if ($pHasDef) {
                $this->_sourceMap[$className]['params'][$pName]['default'] = $parameter->getDefaultValue();
            }
            $this->_sourceMap[$className]['params'][$pName]['type'] = 'fromInput';
            if ($pClass = $parameter->getClass()) {
                $this->_sourceMap[$className]['params'][$pName]['type'] = 'fromClass';
                $this->_sourceMap[$className]['params'][$pName]['className'] = $pClass->getName();
                if ($pClass->isSubclassOf(InjectorType::class)) {
                    $this->_sourceMap[$className]['params'][$pName]['type'] = 'fromClassWithInput';
                }
                try {
                    if (isset($params[$pName]) && is_object($params[$pName]) && is_a($params[$pName], $pClass->getName())) {
                        $makeParams[$index] = $params[$pName];
                    } elseif ($pClass->isSubclassOf(InjectorType::class) && isset($params[$pName])) {
                        $makeParams[$index] = $this->makeObject($parameter->getType()->getName(), $relationList, ['data' => $params[$pName]]);
                    } else {
                        $makeParams[$index] = $this->makeObject($parameter->getType()->getName(), $relationList, []);
                    }
                } catch (\Exception $e) {
                    if ($pHasDef) {
                        $makeParams[$index] = $parameter->getDefaultValue();
                    } else {
                        throw $e;
                    }
                }
            } else {
                if (isset($params[$pName])) {
                    $makeParams[$index] = $params[$pName];
                } elseif ($pHasDef) {
                    $makeParams[$index] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception('无法初始化参数', 0);
                }
            }
        }
        if ($makeFun instanceof \ReflectionMethod && $makeFun->isConstructor()) {
            if (isset($this->_shareList[$className])) {
                $this->_shareObjects[$className] = new $className(...$makeParams);
                unset($this->_sourceMap[$className]);
                return $this->_shareObjects[$className];
            }
            return new $className(...$makeParams);
        }
        if (isset($this->_shareList[$className])) {
            $this->_shareObjects[$className] = $makeFun->isClosure() ? call_user_func_array($makeFun->getClosure(), $makeParams) : $makeFun->invokeArgs($makeParams);
            unset($this->_sourceMap[$className]);
            return $this->_shareObjects[$className];
        }
        return $makeFun->isClosure() ? call_user_func_array($makeFun->getClosure(), $makeParams) : $makeFun->invokeArgs($makeParams);
    }

    /**
     * @param $obj
     * @param array $params
     * @param callable|null $prepare
     */
    public function share($obj, $params = [], callable $prepare = null)
    {
        $this->prepare(is_string($obj) ? $obj : get_class($obj), $prepare);
        if (is_string($obj) && class_exists($obj)) {
            $this->_shareList[$obj] = ['callable' => null, 'params' => []];
            if (is_callable($params)) {
                $this->_shareList[$obj]['callable'] = $params;
            } elseif (is_array($params)) {
                $this->_shareList[$obj]['params'] = $params;
            }
        } elseif (is_object($obj) && $objClass = get_class($obj)) {
            $this->_shareObjects[$objClass] = $obj;
        }
    }

    /**
     * @param string $className
     * @param callable $prepare
     */
    public function prepare(string $className, ?callable $prepare)
    {
        if (is_callable($prepare)) {
            $this->_prepares[$className] = $prepare;
        }
    }

    /**
     * @param callable $call
     * @param array $params
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function execute(callable $call, array $params = [])
    {
        $makeFun = null;
        if (is_array($call)) {
            $makeFun = new \ReflectionMethod($call[0], $call[1]);
        } else {
            $makeFun = new \ReflectionFunction($call);
        }
        $makeParams = $makeFun->getParameters();
        foreach ($makeParams as $index => $parameter) {
            $pName = $parameter->getName();
            $pHasDef = $parameter->isDefaultValueAvailable();
            if ($pClass = $parameter->getClass()) {
                try {
                    if (isset($params[$pName]) && is_object($params[$pName]) && is_a($params[$pName], $pClass->getName())) {
                        $makeParams[$index] = $params[$pName];
                    } elseif ($pClass->isSubclassOf(InjectorType::class) && isset($params[$pName])) {
                        $makeParams[$index] = $this->make($parameter->getType()->getName(), ['data' => $params[$pName]]);
                    } else {
                        $makeParams[$index] = $this->make($parameter->getType()->getName(), []);
                    }
                } catch (\Exception $e) {
                    if ($pHasDef) {
                        $makeParams[$index] = $parameter->getDefaultValue();
                    } else {
                        throw $e;
                    }
                }
            } else {
                if (isset($params[$pName])) {
                    $makeParams[$index] = $params[$pName];
                } elseif ($pHasDef) {
                    $makeParams[$index] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception('无法初始化参数', 0);
                }
            }
        }
        return call_user_func_array($call, $makeParams);
    }
}
