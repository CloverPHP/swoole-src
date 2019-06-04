--TEST--
swoole_table: Github Issue#2231
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../include/bootstrap.php';
$table = new \Swoole\Table(IS_IN_TRAVIS ? 1024 : 2048);
$table->column('string', \Swoole\Table::TYPE_STRING, 256 * 1024);
$table->create();

$n = IS_IN_TRAVIS ? 100 : 1000;
// $n = 100;

$map = [];

while($n--) {
    $key = "key-".rand(1000000, 9999999);
    $value = RandStr::getBytes(rand(100*1024, 250*1024));
    $data[$key] = $value;
    $table->set($key, ['string' => $value]);   
}

foreach($map as $k => $v) {
    Assert::eq($table->get($k)['string'], $v);
}
?>
--EXPECT--