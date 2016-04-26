<?php
/**
 * voa_backend_base
 *
 * $Author$
 * $Id$
 */

class voa_backend_base {

    /**
     * _backend_type
     * 后台程序类型
     *
     * @var mixed
     * @access private
     */
    private $_backend_type;

    /**
     * _class_name
     * 后台程序类名
     *
     * @var mixed
     * @access private
     */
    private $_class_name;

    /**
     * _log_dir
     * 日志目录
     *
     * @var string
     * @access private
     */
    private $_log_dir;

    /**
     * _data_dir
     * 数据目录（相对于应用目录路径下）
     *
     * @var string
     * @access private
     */
    private $_data_dir;

    /**
     * _fp
     * 文件锁句柄
     *
     * @var mixed
     * @access private
     */
    private $_fp;


    /**
     * __construct
     * 构造函数，初始化
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->_initialize();
    }

    /**
     * _initialize
     * 初始化日志与数据目录
     *
     * @access private
     * @return void
     */
    private function _initialize() {

        list(,, $this->_backend_type, $this->_class_name) = explode('_', get_class($this));
        $this->_backend_type = strtolower($this->_backend_type);

        $this->_log_dir = APP_PATH.'/logs/'.$this->_backend_type.'/'.$this->_class_name;
        $this->_data_dir = APP_PATH.'/data/'.$this->_backend_type.'/'.$this->_class_name;

        if (!is_dir($this->_log_dir)) {
            mkdir($this->_log_dir, 0777, true);
        }
    }

    /**
     * _log
     * 记录后台（队列、工具、计划任务）的日志
     *
     * @param mixed $message
     * @access protected
     * @return boolean
     */
    protected function _log($message) {

        $out = '['.date('Y-m-d H:i:s').'] '.$message."\n";

        $log_file = $this->_log_dir.'/'.date('Ymd').'.log';

        $fp = fopen($log_file, 'a');
        if($fp) {
            fwrite($fp, $out);
            fclose($fp);
            return true;
        } else {
            error_log("backend log: cannot open file ($log_file)");
            return false;
        }
    }

    /**
     * _get_data_file
     * 获取数据文件
     *
     * @param mixed $date
     * @access protected
     * @return void
     */
    protected function _get_data_file($date = null) {

        if (!$date) {
            $date = date('Ymd');
        }

        return $this->_data_dir.'/'.$date.'.dat';
    }

    /**
     * _lock
     * 锁定
     *
     * @access protected
     * @return void
     */
    protected function _lock() {

        $lock_file = $this->_log_dir.'/process.lock';

        /** 如果文件锁存在，退出 */
        if (is_file($lock_file)) {
            $pid = intval(file_get_contents($lock_file));
            if (is_dir('/proc/'.$pid)) {
                exit;
            }
        }

        $this->_fp = fopen($lock_file, 'w+');

        if ($this->_fp === false) {
            return false;
        }

        flock($this->_fp, LOCK_EX);
        return fwrite($this->_fp, posix_getpid());
    }

    /**
     * _unlock
     * 解锁
     *
     * @access protected
     * @return void
     */
    protected function _unlock() {

        if ($this->_fp !== false) {
            flock($this->_fp, LOCK_UN);
            fclose($this->_fp);
            return unlink($this->_log_dir.'/process.lock');
        }

        return false;
    }

    /**
     * 打印消息并写入日志
     * @param string|array $msg 消息信息，可以为数组也可以是字符串，如果是数组自动转为按行组合的字符串
     * @param boolean $success 是否是成功消息，如果是错误消息则为false，默认为：false
     * @param boolean $log 是否写入日志。true 强制写入，false 强制不写入, auto 自动（当为错误消息时写入，成功消息不写入）
     * @return boolean
     */
    protected function __output($msg, $success = false, $log = 'auto') {
    	if (is_array($msg)) {
    		$msg = implode(PHP_EOL, $msg);
    	}
    	if ($log === true || ($log == 'auto' && !$success)) {
    		$this->_log(str_replace(array("\r\n", "\n"), "\t", $msg));
    	}
    	echo PHP_EOL.$msg.PHP_EOL;
    	if ($success) {
    		return true;
    	} else {
    		return false;
    	}
    }

}
