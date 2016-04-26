<?php
/**
 * 会议配置表
 * $Author$
 * $Id$
 */

class voa_s_oa_meeting_setting extends service {

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

	public function fetch_all() {
		try {
			return voa_d_oa_meeting_setting::fetch_all($this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 插入新的数据 */
	public function insert($data) {
		try {
			return voa_d_oa_meeting_setting::insert($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 更新数据 */
	public function update($data) {
		try {
			return voa_d_oa_meeting_setting::update($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/** 删除数据 */
	public function delete($cachenames) {
		try {
			return voa_d_oa_meeting_setting::delete($cachenames, $this->__shard_key);
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
			return voa_d_oa_meeting_setting::fetch_all_setting($this->__shard_key);
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
			return voa_d_oa_meeting_setting::update_setting($data, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
