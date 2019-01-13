<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/12
 * Time: 1:55
 */

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

class A {

  public $name;

  public function __construct($name)
  {
    $this->name = $name;
  }

}

$inject = new \NashInject\Injector;

$inject->define(A::class, ['name' => 'nash']);

$b = $inject->make(A::class);

var_dump($b);

var_dump($inject->make(A::class));
