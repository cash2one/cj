<?php

/**
 * PHP memcache 队列类
 * @author LKK/lianq.net
 * @create  on 19:22 2014/3/24
 * @version 0.3
 * @修改说明:
 * 1.放弃了之前的AB面轮值思路,使用类似数组的构造,重写了此类.
 * 2.队列默认先进先出,但增加了反向读取功能.
 * 3.感谢网友FoxHunter提出的宝贵意见.
 * @example:
 * $obj = new voa_memcache_queue('duilie');
 * $obj->add('1asdf');
 * $obj->get_queue_length();
 * $obj->read(10);
 * $obj->get(8);
 */

class voa_memcache_queue {
	public static $s_client; // memcache客户端连接
	public $access; // 队列是否可更新
	private $__expire; // 过期时间,秒,1~2592000,即30天内
	private $__sleep_time; // 等待解锁时间,微秒
	private $__queue_name; // 队列名称,唯一值
	private $__retry_num; // 重试次数,= 10 * 理论并发数
	public $current_head; // 当前队首值
	public $current_tail; // 当前队尾值
	const HEAD_KEY = '_queue_head_'; // 队列首kye
	const TAIL_KEY = '_queue_tail_'; // 队列尾key
	const VALU_KEY = '_queue_valu_'; // 队列值key
	const LOCK_KEY = '_queue_lock_'; // 队列锁key
	// 实际键值
	public $head_key = '';
	public $tail_key = '';
	public $lock_key = '';
	// 最大队列数,建议上限10K
	public $maxnum = 1500;

	/**
	 * 构造函数
	 *
	 * @param string $queueName 队列名称
	 * @param int $expire 过期时间
	 * @param array $config memcache配置
	 *
	 * @return <type>
	 */
	public function __construct($queue_name = '', $expire = 0, $config = '') {

		if (empty($config)) {
			self::$s_client = memcache_pconnect('127.0.0.1', 11211);
		} elseif (is_array($config)) { // array('host'=>'127.0.0.1','port'=>'11211')
			self::$s_client = memcache_pconnect($config['host'], $config['port']);
		} elseif (is_string($config)) { // "127.0.0.1:11211"
			$tmp = explode(':', $config);
			$conf['host'] = isset($tmp[0]) ? $tmp[0] : '127.0.0.1';
			$conf['port'] = isset($tmp[1]) ? $tmp[1] : '11211';
			self::$s_client = memcache_pconnect($conf['host'], $conf['port']);
		}

		if (! self::$s_client) return false;
		ignore_user_abort(true); // 当客户断开连接,允许继续执行
		set_time_limit(0); // 取消脚本执行延时上限
		$this->access = false;
		$this->__sleep_time = 1000;
		$expire = empty($expire) ? 2592000 : intval($expire);
		$this->__expire = $expire;
		$this->__queue_name = $queue_name;
		$this->__retry_num = 1000;
		$this->head_key = $this->__queue_name . self::HEAD_KEY;
		$this->tail_key = $this->__queue_name . self::TAIL_KEY;
		$this->lock_key = $this->__queue_name . self::LOCK_KEY;
		$this->__init_set_head_n_tail();
	}

	/**
	 * 初始化设置队列首尾值
	 */
	private function __init_set_head_n_tail() {

		// 当前队列首的数值
		$this->current_head = memcache_get(self::$s_client, $this->head_key);
		if ($this->current_head === false) $this->current_head = 0;
		// 当前队列尾的数值
		$this->current_tail = memcache_get(self::$s_client, $this->tail_key);
		if ($this->current_tail === false) $this->current_tail = 0;
	}

	/**
	 * 当取出元素时,改变队列首的数值
	 *
	 * @param int $step 步长值
	 */
	private function __change_head($step = 1) {

		$this->current_head += $step;
		memcache_set(self::$s_client, $this->head_key, $this->current_head, false, $this->__expire);
	}

	/**
	 * 当添加元素时,改变队列尾的数值
	 *
	 * @param int $step 步长值
	 * @param bool $reverse 是否反向
	 * @return null
	 */
	private function __change_tail($step = 1, $reverse = false) {

		if (! $reverse) {
			$this->current_tail += $step;
		} else {
			$this->current_tail -= $step;
		}

		memcache_set(self::$s_client, $this->tail_key, $this->current_tail, false, $this->__expire);
	}

	/**
	 * 队列是否为空
	 *
	 * @return bool
	 */
	private function __is_empty() {

		return (bool)($this->current_head === $this->current_tail);
	}

	/**
	 * 队列是否已满
	 *
	 * @return bool
	 */
	private function __is_full() {

		$len = $this->current_tail - $this->current_head;
		return (bool)($len === $this->maxnum);
	}

	/**
	 * 队列加锁
	 */
	private function __get_lock() {

		if ($this->access === false) {
			while (! memcache_add(self::$s_client, $this->lock_key, 1, false, $this->__expire)) {
				usleep($this->__sleep_time);
				@$i ++;
				if ($i > $this->__retry_num) { // 尝试等待N次
					return false;
					break;
				}
			}

			$this->__init_set_head_n_tail();
			return $this->access = true;
		}

		return $this->access;
	}

	/**
	 * 队列解锁
	 */
	private function __un_lock() {

		memcache_delete(self::$s_client, $this->lock_key, 0);
		$this->access = false;
	}

	/**
	 * 获取当前队列的长度
	 * 该长度为理论长度,某些元素由于过期失效而丢失,真实长度<=该长度
	 *
	 * @return int
	 */
	public function get_queue_length() {

		$this->__init_set_head_n_tail();
		return intval($this->current_tail - $this->current_head);
	}

	/**
	 * 添加队列数据
	 *
	 * @param void $data 要添加的数据
	 * @return bool
	 */
	public function add($data) {

		if (! $this->__get_lock()) return false;
		if ($this->__is_full()) {
			$this->__un_lock();
			return false;
		}

		$value_key = $this->__queue_name . self::VALU_KEY . strval($this->current_tail + 1);
		$result = memcache_set(self::$s_client, $value_key, $data, MEMCACHE_COMPRESSED, $this->__expire);
		if ($result) {
			$this->__change_tail();
		}

		$this->__un_lock();
		return $result;
	}

	/**
	 * 读取队列数据
	 *
	 * @param int $length 要读取的长度(反向读取使用负数)
	 * @return array
	 */
	public function read($length = 0) {

		if (! is_numeric($length)) return false;
		$this->__init_set_head_n_tail();
		if ($this->__is_empty()) {
			return false;
		}

		if (empty($length)) $length = $this->maxnum; // 默认所有
		$keyArr = array();
		if ($length > 0) { // 正向读取(从队列首向队列尾)
			$tmpMin = $this->current_head;
			$tmpMax = $tmpMin + $length;
			for($i = $tmpMin; $i <= $tmpMax; $i ++) {
				$keyArr[] = $this->__queue_name . self::VALU_KEY . $i;
			}
		} else { // 反向读取(从队列尾向队列首)
			$tmpMax = $this->current_tail;
			$tmpMin = $tmpMax + $length;
			for($i = $tmpMax; $i > $tmpMin; $i --) {
				$keyArr[] = $this->__queue_name . self::VALU_KEY . $i;
			}
		}

		$result = @memcache_get(self::$s_client, $keyArr);
		return $result;
	}

	/**
	 * 取出队列数据
	 *
	 * @param int $length 要取出的长度(反向读取使用负数)
	 * @return array
	 */
	public function get($length = 0) {

		if (! is_numeric($length)) return false;
		if (! $this->__get_lock()) return false;
		if ($this->__is_empty()) {
			$this->__un_lock();
			return false;
		}

		if (empty($length)) $length = $this->maxnum; // 默认所有
		$length = intval($length);
		$keyArr = array();
		if ($length > 0) { // 正向读取(从队列首向队列尾)
			$tmpMin = $this->current_head;
			$tmpMax = $tmpMin + $length;
			for($i = $tmpMin; $i <= $tmpMax; $i ++) {
				$keyArr[] = $this->__queue_name . self::VALU_KEY . $i;
			}

			$this->__change_head($length);
		} else { // 反向读取(从队列尾向队列首)
			$tmpMax = $this->current_tail;
			$tmpMin = $tmpMax + $length;
			for($i = $tmpMax; $i > $tmpMin; $i --) {
				$keyArr[] = $this->__queue_name . self::VALU_KEY . $i;
			}

			$this->__change_tail(abs($length), true);
		}

		$result = @memcache_get(self::$s_client, $keyArr);
		foreach ($keyArr as $v) { // 取出之后删除
			@memcache_delete(self::$s_client, $v, 0);
		}

		$this->__un_lock();
		return $result;
	}

	/**
	 * 清空队列
	 */
	public function clear() {

		if (! $this->__get_lock()) return false;
		if ($this->__is_empty()) {
			$this->__un_lock();
			return false;
		}

		$tmpMin = $this->current_head --;
		$tmpMax = $this->current_tail ++;
		for($i = $tmpMin; $i <= $tmpMax; $i ++) {
			$tmpKey = $this->__queue_name . self::VALU_KEY . $i;
			@memcache_delete(self::$s_client, $tmpKey, 0);
		}

		$this->current_tail = $this->current_head = 0;
		memcache_set(self::$s_client, $this->head_key, $this->current_head, false, $this->__expire);
		memcache_set(self::$s_client, $this->tail_key, $this->current_tail, false, $this->__expire);
		$this->__un_lock();
	}

	/**
	 * 清除所有memcache缓存数据
	 */
	public function mem_flush() {

		memcache_flush(self::$s_client);
	}

}
