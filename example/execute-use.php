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

  public function __construct($name = 'test')
  {
    $this->name = $name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

}

$inject = new \NashInject\Injector;

$inject->execute(function (A $a) {
  var_dump($a);
});

function test (A $a, $name) {
  $a->setName($name);
  var_dump($a);
}

$inject->execute('test', ['name' => 'nash']);
