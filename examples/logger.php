<?php
require_once __DIR__ . '/../src/TiarraSocket.php';
stream_filter_register('capture.tiarra.*', 'Stream_Filter_TiarraSocket');

/**
 * ありがちなロガー
 */
class Logger
{
    private $fp;

    public function __construct($logfile)
    {
        $this->fp = fopen($logfile, 'w');
        // stream_filter_append($this->fp, 'capture.tiarra.tiarra2:#piyo@piyo', STREAM_FILTER_WRITE, array('channel' => 'unk'));
    }

    public function debug($msg)
    {
        fputs($this->fp, sprintf('[DEBUG %s] %s' . PHP_EOL, date('Y-m-d H:i:s'), $msg));
    }
}

// filter かましつつ logger を初期化
$logger = new Logger('php://filter/capture.tiarra.tiarra:#hoge@fuga/resource=sample.log');
// $logger = new Logger('sample.log');


$logger->debug('hoge');
$logger->debug('fuga');

