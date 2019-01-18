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

  public $name, $age;

  public function __construct($name, \NashInject\Type\HashType $age = null)
  {
    $this->name = $name;
    $this->age = $age->getData();
  }

}

$inject = new \NashInject\Injector;

$inject->define(A::class, ['name' => 'Nash-Liu', 'age' => '{"name":"test","age":12}']);

$inject->execute(function (A $a) {
  var_dump($a);
});
