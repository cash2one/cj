<?php
/**
 * cache_memcached
 *
 * $Author$
 * $Id$
 *
 */

class cache_memcached extends cache_abstract {

	/**
	 * 数据源
	 */
	public $clusters = array();

	/**
	 * 配制选项
	 */
	public $options;

	/**
	 *  已连接上的服务器组
	 */
	static protected $_connections = array();

	/**
	 * __construct
	 *
	 * @param  mixed $cache
	 * @param  mixed $options
	 * @return void
	 */
	public function __construct($cache, $options) {
		parent::__construct($cache, $options);
		$this->options = $options;
		shuffle($options['servers']);	/** 打乱数组，避免新增加一组服务器之后分配不到相应的配置 */
		foreach ($options['servers'] as $servers) {
			$cluster_id = $this->get_cluster_id($servers);
			$this->clusters[$cluster_id] = $servers;
		}
	}

	/**
	 * 计算一组数据源的Id
	 *
	 * @param array $servers 服务器组
	 * @return string
	 */
	public function get_cluster_id($servers) {
		$str = '';
		foreach ($servers as $server) {
			$str .= join(':', $server).'|';
		}

		return md5($str);
	}

	/**
	 * 获取一组到数据源的连接
	 *
	 * @param string $cluster_id 数组源的Id
	 * @param boolean $do_conn   是否强制添加到连接池
	 * @param array   $servers  数组服务器组
	 * @param object
	 */
	public function get_connection($cluster_id, $do_conn = false) {

		/** 是否在连接池 */
		if (array_key_exists($cluster_id, self::$_connections)) {
			$conn = self::$_connections[$cluster_id];
		} elseif ($do_conn) {
			$conn = new Memcache();
			foreach ($this->clusters[$cluster_id] as $server) {
				$conn->add_server($server['host'], $server['port']);
			}
			/** 添加到连接池 */
			self::$_connections[$cluster_id] = $conn;
		} else {
			$conn = null;
		}

		return $conn;
	}

	/**
	 * 获取所有连接
	 *
	 * @return array
	 */
	public function get_all_connections() {
		foreach ($this->clusters as $cluster_id => $server) {
			$conns[$cluster_id] = $this->get_connection($cluster_id, true);
		}

		return $conns;
	}

	/**
	 * 获取下一个到数据源的连接
	 *
	 * @param integer $idx 到数组源索引
	 * @param object
	 */
	public function get_next_connection($idx) {
		$i = 0;
		foreach ($this->clusters as $cluster_id => $server) {
			if ($idx == $i) {
				$conn = $this->get_connection($cluster_id, true);
				return $conn;
			}
			$i++;
		}

		return false;
	}

	/**
	 *  缓存多组数据
	 */
	private function _set_multi($conns, $datas, $ttl) {
		if (is_array($conns)) {
			foreach ($conns as $conn) {
				$this->_set_multi($conn, $datas, $ttl);
			}
		} else {
			foreach ($datas as $key => $value) {
				$res = $conns->set($key, $value, $this->options['flag'], $ttl);
			}
		}

		return true;
	}

	/**
	 * get 获取一条缓存结果
	 *
	 * @param  string $key 缓存的键值
	 * @return mixed 根据参数不同，有以下可能性:
	 * 			- mixed, 有缓存时
	 * 			- null, 连不上时或者没有对应的缓存
	 */
	public function get($key) {

		$idx = 0;
		$conns = array();
		$ttl = $this->frontend->get_ttl();

		while(($conn = $this->get_next_connection($idx))) {
			$result = $conn->get($key);
			if ($result === false || (is_array($key) && $result === array())) {
				/** 不自动复制 */
				if (!$this->options['auto_replication']) {
					return null;
				}

				$idx++;
				$conns[] = $conn;
				continue;
			}

			if ($conns) {
				if (!is_array($key)) {
					$data = array($key => $result);
				} else {
					$data = $result;
				}
				$this->_set_multi($conns, $data, $ttl);
			}

			return $result;
		}

		return null;
	}

	/**
	 * mget 获取多条缓存记录
	 *
	 * @param array $keys 缓存的键值数组
	 * @return array
	 */
	public function mget($keys) {
		$result = $this->get($keys);
		if ($result === null) {
			return array();
		}

		return $result;
	}

	/**
	 * put 缓存一条记录
	 *
	 * @param  mixed $key 缓存的键值
	 * @param  mixed $value 缓存值
	 * @return boolean
	 */
	public function put($key, $value) {
		$conns = $this->get_all_connections();
		$ttl = $this->frontend->get_ttl();

		foreach ($conns as $conn) {
			$conn->set($key, $value, $this->options['flag'], $ttl);
		}
		return true;
	}


	/**
	 * remove 消除一条记录
	 *
	 * @param  string $key  缓存的键值
	 * @return boolean
	 */
	public function remove($key) {
		$conns = $this->get_all_connections();
		foreach ($conns as $conn) {
			$conn->delete($key);
		}
		return true;
	}

	/**
	 * clear_all 清空缓存
	 *
	 * @return boolean
	 */
	public function clear_all() {
		$conns = $this->get_all_connections();
		foreach ($conns as $conn) {
			$conn->flush();
		}

		return true;
	}

	public function get_connected() {
		return self::$_connections;
	}

}
