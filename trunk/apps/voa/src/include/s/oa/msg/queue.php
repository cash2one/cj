<?php
/**
 * voa_s_oa_msg_queue
 * 用户表
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_msg_queue extends service {
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

	/**
	 * 获取发送队列
	 * @param int $start
	 * @param int $limit
	 * @throws service_exception
	 */
	public function fetch_all($start = 0, $limit = 0) {
		try {
			return voa_d_oa_msg_queue::fetch_all($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 id 获取数据
	 * @param int $id
	 */
	public function fetch_by_id($id) {
		try {
			return voa_d_oa_msg_queue::fetch_by_id($id, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	public function fetch_by_ids($ids) {
		try {
			return voa_d_oa_msg_queue::fetch_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取发送队列
	 * @param int $start
	 * @param int $limit
	 */
	public function fetch_send_list($start = 0, $limit = 0) {
		try {
			return voa_d_oa_msg_queue::fetch_send_list($start, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据id获取未发送队列信息
	 * @param array $ids id数组
	 * @param array $shard_key 分库参数
	 * @return Ambigous <void, boolean>
	 */
	public function fetch_unsend_by_ids($ids) {
		try {
			return voa_d_oa_msg_queue::fetch_unsend_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 数据入库
	 * @param array $data 数据
	 * @param boolean $return_insert_id 是否返回自增id
	 * @param boolean $replace 是否启用 REPLACE INTO
	 * @param boolean $silent
	 * @throws service_exception
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		try {
			return voa_d_oa_msg_queue::insert($data, $return_insert_id, $replace, $silent, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 更新数据
	 * @param array $data 数据
	 * @param string $condition 更新条件
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 * @throws service_exception
	 */
	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		try {
			return voa_d_oa_msg_queue::update($start, $data, $condition, $unbuffered, $low_priority, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 id 更新
	 * @param array $data 数据
	 * @param array $ids
	 * @throws service_exception
	 */
	public function update_by_ids($data, $ids) {
		try {
			return voa_d_oa_msg_queue::update_by_ids($data, $ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 id 自增调用次数
	 * @param array $ids id 数组
	 * @throws service_exception
	 */
	public function increase_times_by_ids($ids) {
		try {
			return voa_d_oa_msg_queue::increase_times_by_ids($ids, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据 id 删除
	 * @param array $ids id 数组
	 * @param boolean $unbuffered
	 * @throws service_exception
	 */
	public function delete_by_ids($ids, $unbuffered = false) {
		try {
			return voa_d_oa_msg_queue::delete_by_ids($ids, $unbuffered, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取未发送消息
	 * @param int $sendtime 发送时间戳
	 * @param array $shard_key
	 */
	public function fetch_unsend_by_sendtime($sendtime, $limit = 0) {
		try {
			return voa_d_oa_msg_queue::fetch_unsend_by_sendtime($sendtime, $limit, $this->__shard_key);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
