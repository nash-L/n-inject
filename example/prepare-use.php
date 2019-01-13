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

  public function setName($name)
  {
    $this->name = $name;
  }

  public function say()
  {
    var_dump('I\'m ' . $this->name);
  }

}

$inject = new \NashInject\Injector;

$inject->define(A::class, ['name' => 'Nash-Liu'], function ($a) {
  $a->say();
});

$inject->execute(function (A $a) {
  var_dump($a);
});
