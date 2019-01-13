<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/12
 * Time: 1:55
 */

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

class TypeInt implements \NashInject\InjectorType {

  public $data;

  public function __construct($data)
  {
    $this->data = 0;
    if (is_numeric($data)) {
      $this->data = intval($data);
    }
  }

}

class A {

  public $name, $age;

  public function __construct($name, TypeInt $age)
  {
    $this->name = $name;
    $this->age = $age->data;
  }

}

$inject = new \NashInject\Injector;

$inject->define(A::class, ['name' => 'Nash-Liu', 'age' => '123']);

$inject->execute(function (A $a) {
  var_dump($a);
});
