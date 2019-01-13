<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/12
 * Time: 1:55
 */

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

class A {}

class B {

  public $a;

  public function __construct(A $a)
  {
    $this->a = $a;
  }

}

$inject = new \NashInject\Injector;

$b = $inject->make(B::class);

var_dump($b);

var_dump($b->a);
