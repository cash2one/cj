<?php
/**
 * 微信墙配置表
 * $Author$
 * $Id$
 */

class voa_s_oa_wxwall_setting extends service {

	/** 分库/分表的信息 */
	private $__shard_key = array();

	/**
	 * __construct
	 *
	 * @param  array $shard_key
	 * @return void
	*/
	public function __construct($shard_key = array()) {

		$this->__shard_key = $shard_key;
	}

	public function fetch_all($start = 0, $limit = 0) {
		try {
			$sets = voa_d_oa_wxwall_setting::fetch_all($start, $limit, $this->__shard_key);
			$data = array();
			foreach ($sets as $v) {
				$data[$v['wws_key']] = $v['wws_type'] ? unserialize($v['wws_value']) : $v['wws_value'];
			}

			return $data;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 插入新的数据 */
	public function insert($data) {
		try {
			return voa_d_oa_wxwall_setting::insert($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新数据 */
	public function update($data) {
		try {
			return voa_d_oa_wxwall_setting::update($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 删除数据 */
	public function delete($data) {
		try {
			return voa_d_oa_wxwall_setting::delete($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 以变量名为键名输出所有变量信息
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_all_setting() {
		try {
			return voa_d_oa_wxwall_setting::fetch_all_setting($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 更新多个变量的值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @throws service_exception
	 * @return boolean
	 */
	public function update_setting($data) {
		try {
			return voa_d_oa_wxwall_setting::update_setting($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
