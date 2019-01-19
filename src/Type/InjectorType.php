<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/15
 * Time: 22:55
 */

namespace NashInject\Type;


use NashInject\Exception\InjectorTypeException;
use NashInject\Injector;
use Exception;

abstract class InjectorType
{
    protected $data, $injector;

    /**
     * InjectorType constructor.
     * @param $data
     * @param Injector|null $injector
     * @throws InjectorTypeException
     */
    final public function __construct($data, ?Injector $injector = null)
    {
        $this->setInject($injector);
        $this->data = null;
        if ($data) {
            $this->setData($data);
        }
    }

    /**
     * @param Injector|null $injector
     */
    final public function setInject(?Injector $injector)
    {
        $this->injector = $injector;
    }

    abstract public function validate($data);

    /**
     * @param $data
     * @throws InjectorTypeException
     */
    final public function setData($data)
    {
        try {
            $this->data = $this->validate($data);
        } catch (Exception $e) {
            if ($e instanceof InjectorTypeException) {
                throw $e;
            }
            throw new InjectorTypeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return mixed
     */
    final public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->data);
    }
}
