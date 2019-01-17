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

$a = new A();

 $inject->share($a);
//$inject->share(A::class);

$b = $inject->make(B::class);
$c = $inject->make(B::class);

var_dump($b, $c);

var_dump($a);

var_dump($b->a, $c->a);
