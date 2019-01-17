## 依赖注入工具

> 原本使用的依赖注入工具[rdlowrey/auryn](https://packagist.org/packages/rdlowrey/auryn)在[easyswoole v3](https://packagist.org/packages/easyswoole/easyswoole)下偶尔会报循环依赖的问题，于是便自己设计了一个依赖注入工具，使用上参考了[rdlowrey/auryn](https://packagist.org/packages/rdlowrey/auryn)，并做出部分更改

### 基本使用
```php
$inject = new \NashInject\Injector;

// 构建无需初始化参数的对象
$std = $inject->make(\stdClass::class);
var_dump($std);

// 使用初始化参数构建对象
$pdo = $inject->make(\PDO::class, [
  'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
  'username' => 'dbuser',
  'passwd' => 'dbpass'
]);
var_dump($pdo);

// 预定义初始化参数
$inject->define(\PDO::class, [
  'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
  'username' => 'dbuser',
  'passwd' => 'dbpass'
]);
var_dump($inject->make('PDO'));

// 预处理
$inject->define(\PDO::class, [
  'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
  'username' => 'dbuser',
  'passwd' => 'dbpass'
], function (\PDO $pdo) {
  $pdo->query('SET NAMES utf-8');
  var_dump('数据库已连接');
});
var_dump($inject->make('PDO'));

// 全局共享
$inject->share(\PDO::class, [
  'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
  'username' => 'dbuser',
  'passwd' => 'dbpass'
], function () {
  $pdo->query('SET NAMES utf-8');
  var_dump('数据库已连接');
});
var_dump($inject->make('PDO'));
var_dump($inject->make('PDO'));

// 执行方法
$inject->share(\PDO::class, [
  'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
  'username' => 'dbuser',
  'passwd' => 'dbpass'
], function () {
  $pdo->query('SET NAMES utf-8');
  var_dump('数据库已连接');
});
$inject->execute(function (PDO $pdo) {
  var_dump($pdo);
});

// 自定义类型
class TypeInt implements \NashInject\Type\InjectorType {
  private $data;

  public function __construct($data)
  {
    if (!is_int($data)) {
      throw new Exception('请输入一个正确的端口号');
    }
    $this->data = intval($data);
  }

  public function getData()
  {
    return $this->data;
  }
}

$inject->define('PDO', function ($host, TypeInt $port, $dbName, $username, $password) {
  return new \PDO("mysql:dbname={$dbName};host={$host};port={$port->getData()}", $username, $password);
}, function (\PDO $pdo) {
  $pdo->query('SET NAMES utf-8');
});

$pdo = $inject->make('PDO', ['host' => '127.0.0.1', 'port' => 3306, 'dbName' => 'test', 'username' => 'root', 'password' => 'root']);

var_dump($pdo);
```