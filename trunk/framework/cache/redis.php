<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 15/11/19
 * Time: 13:43
 * 操作Redis
 */


class cache_redis extends cache_abstract {

	public static $instance = null;
	// 连接Redis配置
	private $conf;

	public function __construct($cache, $options) {

		$this->conf = $options;
	}

	/**
	 * 连接redis（长连接）
	 */
	private function getRedis() {

		try {
			$obj = new Redis();
			$obj->connect($this->conf['host'], $this->conf['port']);
			if (!empty($this->conf['pwd'])) {
				$obj->auth($this->conf['pwd']);
			}
		} catch (RedisException $redis) {
			logger::error('连接redis异常');
			logger::error($redis->getMessage());
			return false;
		}

		return $obj;
	}

	/**
	 * 关闭连接
	 * pconnect 连接是无法关闭的
	 *
	 * @return boolean
	 */
	public function close() {

		$this->close();
		return true;
	}

	/**
	 * 返回指定key的hash中所有的键和值
	 *
	 * @param $key
	 * @return 返回值
	 */
	public function hGetAll($key) {

		return $this->getRedis()->hGetAll($key);
	}

	/**
	 * 写入key和value值，如果key已经存在则覆盖
	 *
	 * @param $key
	 * @param $value
	 * @param int $exp 过期时间(单位秒)
	 * @return bool 写入成功返回true
	 */
	public function set($key, $value, $exp = 0) {

		if (is_array($value) || is_object($value)) {
			$value = json_encode($value);
		}

		// 永不过期
		if ($exp == 0) {
			$ret = $this->getRedis()->set($key, $value);
		} else {
			$ret = $this->getRedis()->setex($key, $exp, $value);
		}

		return $ret;
	}

	public function put($key, $value, $exp = 0) {

		return $this->set($key, $value, $exp);
	}

	/**
	 * 得到某个key的值（string值）
	 *
	 * @param $key
	 * @return bool|string
	 */
	public function get($key) {

		$result = $this->getRedis()->get($key);
		$jsonData = json_decode($result, true);
		return ($jsonData === NULL) ? $result : $jsonData;
	}

	/**
	 * 返回满足给定pattern的所有key
	 *
	 * @param $is_key 默认是一个非正则表达试，使用模糊查询
	 * @param $key
	 * @return array
	 */
	public function keys($key, $is_key = true) {

		if ($is_key) {
			return $this->getRedis()->keys("*$key*");
		}

		return $this->getRedis()->keys("$key");
	}

	/**
	 * 删除一个或多个key
	 *
	 * @param $keys
	 */
	public function delKey($keys) {

		if (is_array($keys)) {
			foreach ($keys as $key) {
				$this->getRedis()->del($key);
			}
		} else {
			$this->getRedis()->del($keys);
		}
	}

	public function remove($keys) {

		$this->delKey($keys);
	}

	/**
	 * 在名称为key的list右边（尾）添加一个值为value的 元素
	 *
	 * @param $key
	 * @param $val
	 */
	public function rPush($key, $val) {

		$this->getRedis()->rPush($key, $val);
	}

	/**
	 * 取出名称为key的list左边(头)起的第一个元素，并删除该元素
	 *
	 * @param $key
	 */
	public function lPop($key) {

		return $this->getRedis()->lPop($key);
	}

	/**
	 * 返回名称为key的list中start至end之间的元素（end为 -1 ，返回所有）
	 *
	 * @param $key
	 * @param $start 默认0 从第一个开始
	 * @param $end 默认-1
	 */
	public function lRange($key, $start = 0, $end = -1) {

		return $this->getRedis()->lRange($key, $start, $end);
	}

	/**
	 * 返回列表的长度
	 *
	 * @param $key
	 * @return int
	 */
	public function lLen($key) {

		return $this->getRedis()->lLen($key);
	}

	/**
	 * 当$key不存在的时候才设置
	 *
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public function setnx($key, $value) {

		return $this->getRedis()->setnx($key, $value);
	}

	/**
	 * 设定$key有效期
	 *
	 * @param $key
	 * @param $ttl (单位秒)
	 */
	public function expire($key, $ttl) {

		return $this->getRedis()->expire($key, $ttl);
	}

	/**
	 * 获取$key的生存时间
	 *
	 * @param $key
	 */
	public function ttl($key) {

		return $this->getRedis()->ttl($key);
	}

	/**
	 * 批量操作
	 * Redis::MULTI：将多个操作当成一个事务执行
	 * Redis::PIPELINE:让（多条）执行命令简单的，更加快速的发送给服务器，但是没有任何原子性的保证
	 * discard:删除一个事务
	 *
	 * @return \Redis
	 */
	public function multi($type) {

		return $this->getRedis()->multi($type);
	}

}
