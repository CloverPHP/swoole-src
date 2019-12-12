--TEST--
swoole_http2_server: static handler
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../include/bootstrap.php';

use Swoole\Http\Server;
use Swoole\Http\Response;
use Swoole\Http\Request;
use Swoole\Event;

define('DOC_ROOT', realpath(__DIR__ . '/../..'));

$pm = new SwooleTest\ProcessManager;
$pm->parentFunc = function ($pid) use ($pm) {
    go(function () use ($pm) {
        $domain = '127.0.0.1';
        $cli = new Swoole\Coroutine\Http2\Client($domain, $pm->getFreePort(), false);
        $cli->set([
            'timeout' => -1,
        ]);
        $cli->connect();

        $req = new Swoole\Http2\Request;
        $req->path = '/README.md';
        $req->headers = [
            'Host' => $domain,
            "User-Agent" => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-encoding' => 'gzip'
        ];
        //request
        for ($n = MAX_REQUESTS; $n--;) {
            Assert::assert($cli->send($req));
        }
        //response
        for ($n = MAX_REQUESTS; $n--;) {
            $response = $cli->recv();
            Assert::same($response->statusCode, 200);
            Assert::same(md5_file(DOC_ROOT . '/README.md'), md5($response->data));
        }
        $pm->kill();
    });
    Event::wait();
};
$pm->childFunc = function () use ($pm) {
    $http = new Server('127.0.0.1', $pm->getFreePort(), SWOOLE_BASE, SWOOLE_SOCK_TCP);
    $http->set([
        'log_file' => '/dev/null',
        'open_http2_protocol' => true,
        'enable_static_handler' => true,
        'document_root' => DOC_ROOT,
    ]);
    $http->on("WorkerStart", function ($serv, $wid) {
        global $pm;
        $pm->wakeup();
    });
    $http->on("request", function (Request $request, Response $response) {
        $response->end("hello world");
    });
    $http->start();
};
$pm->childFirst();
$pm->run();
?>
--EXPECT--
