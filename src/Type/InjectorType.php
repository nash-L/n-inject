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
    protected $data, $inject;

    /**
     * InjectorType constructor.
     * @param $data
     * @param Injector $inject
     * @throws InjectorTypeException
     */
    final public function __construct($data, Injector $inject)
    {
        $this->inject = $inject;
        try {
            $this->data = is_null($data) ? null : $this->validate($data);
        } catch (Exception $e) {
            if ($e instanceof InjectorTypeException) {
                throw $e;
            }
            throw new InjectorTypeException($e->getCode(), $e->getMessage(), $e);
        }
    }

    abstract public function validate($data);

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
