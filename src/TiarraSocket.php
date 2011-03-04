<?php
/**
 * Stream_Filter_TiarraSocket
 *
 * stream_filter_register('capture.tiarra', 'Stream_Filter_TiarraSocket');
 * stream_filter_register('capture.tiarra.*', 'Stream_Filter_TiarraSocket');
 *
 * @author Keisuke SATO <ksato@otobank.co.jp>
 */
require_once 'Net/Socket/Tiarra.php';

class Stream_Filter_TiarraSocket extends php_user_filter
{
    const PRIVMSG = 0;
    const NOTICE = 1;

    protected $tiarra;
    protected $channel;
    protected $mode = 1;

    /**
     * stream filter
     *
     * @param resource $in
     * @param resource $out
     * @param int &$consumed
     * @param bool $closing
     * @return int
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            switch ($this->mode) {
                case self::PRIVMSG:
                    $this->tiarra->message($this->channel, $bucket->data);
                    break;

                case self::NOTICE:
                default:
                    $this->tiarra->noticeMessage($this->channel, $bucket->data);
                    break;
            }
            echo $bucket->data, PHP_EOL, PHP_EOL;

            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

    /**
     * filter initializer
     *
     * @return bool
     */
    public function onCreate()
    {
        if (isset($this->params['socket'])) {
            $socketname = $this->params['socket'];
        }

        if (isset($this->params['channel'])) {
            $this->channel = $this->params['channel'];
        }

        if (isset($this->params['mode'])) {
            $this->mode = $this->params['mode'];
        }

        if (preg_match('/^capture\.tiarra\.(.+?):(.+?)$/', $this->filtername, $matches)) {
            list(, $socketname, $this->channel) = $matches;
        }

        if (isset($socketname) && isset($this->channel)) {
            try {
                $this->tiarra = new Net_Socket_Tiarra($socketname);

                return true;

            } catch (Net_Socket_Tiarra_Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        return false;
    }

    public function onClose()
    {
        $this->tiarra = null;
    }
}

