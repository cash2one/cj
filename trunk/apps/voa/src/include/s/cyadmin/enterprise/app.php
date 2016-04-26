<?php

/**
 * voa_s_cyadmin_enterprise_app
 * 畅移后台/企业应用数据表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_cyadmin_enterprise_app extends service {

	/** 分库/分表的信息 */
	private $__shard_key = array();

	/**
	 * __construct
	 *
	 * @param  array $shard_key
	 *
	 * @return void
	 */
	public function __construct( $shard_key = array() ) {

		$this->__shard_key = $shard_key;
	}

	/**
	 * 【S】根据主键值读取单条数据
	 * @author Deepseath
	 *
	 * @param int $value 主键值
	 *
	 * @throws service_exception
	 */
	public function fetch( $value ) {
		try {
			return voa_d_cyadmin_enterprise_app::fetch( $value, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * 【S】通过主键更新
	 * @author Deepseath
	 *
	 * @param array $data 待更新的数据数组
	 * @param array|string $value 主键值
	 *
	 * @throws service_exception
	 */
	public function update( $data, $value ) {
		try {
			return voa_d_cyadmin_enterprise_app::update( $data, $value, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * 【S】通过主键删除记录
	 * @author Deepseath
	 *
	 * @param array|string $value 主键值
	 *
	 * @throws service_exception
	 * @return void
	 */
	public function delete( $value ) {
		try {
			return voa_d_cyadmin_enterprise_app::delete( $value, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * 【S】获取数据表默认值
	 * @author Deepseath
	 * @throws service_exception
	 */
	public function fetch_all_field() {
		try {
			return voa_d_cyadmin_enterprise_app::fetch_all_field( $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * 【S】读取所有
	 * @author Deepseath
	 *
	 * @param int $start
	 * @param int $limit
	 *
	 * @throws service_exception
	 */
	public function fetch_all( $start = 0, $limit = 0 ) {
		try {
			return voa_d_cyadmin_enterprise_app::fetch_all( $start, $limit, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * 【S】统计所有未删除数据的总数
	 * @author Deepseath
	 * @throws service_exception
	 * @return number
	 */
	public function count_all() {
		try {
			return voa_d_cyadmin_enterprise_app::count_all( $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * 【S】数据入库
	 * @author Deepseath
	 *
	 * @param array $data 入库数据
	 * @param boolean $return_insert_id 是否返回自增id
	 * @param boolean $replace 是否使用 replace into
	 *
	 * @throws service_exception
	 */
	public function insert( $data, $return_insert_id = false, $replace = false ) {
		try {
			return voa_d_cyadmin_enterprise_app::insert( $data, $return_insert_id, $replace, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * 【S】通过条件更新
	 * @author Deepseath
	 *
	 * @param array $data 更新的数据
	 * @param string|array $conditions 更新条件
	 *
	 * @throws service_exception
	 * @return Ambigous <void, boolean, unknown>
	 */
	public function update_by_conditions( $data, $conditions ) {
		try {
			return voa_d_cyadmin_enterprise_app::update_by_conditions( $data, $conditions, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * 【S】根据条件删除记录
	 * @author Deepseath
	 *
	 * @param array|string $conditions
	 *
	 * @throws service_exception
	 * @return void
	 */
	public function delete_by_conditions( $conditions ) {
		try {
			return voa_d_cyadmin_enterprise_app::delete_by_conditions( $conditions, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * 通过企业ID和应用id来找到应用申请记录
	 *
	 * @param number $ep_id
	 * @param number $cp_pluginid
	 *
	 * @throws service_exception
	 * @return array
	 */
	public function fetch_by_ep_id_and_cp_pluginid( $ep_id, $cp_pluginid ) {
		try {
			return voa_d_cyadmin_enterprise_app::fetch_by_ep_id_and_cp_pluginid( $ep_id, $cp_pluginid, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/** 获取所有列表 */
	public function fetch_all_by_id( $id = 0, $start = 0, $limit = 0 ) {
		try {
			return voa_d_cyadmin_enterprise_app::fetch_all_by_id( $id, $start, $limit, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	public function count_all_by_id( $id ) {
		try {
			return voa_d_cyadmin_enterprise_app::count_all_by_id( $id, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	public function fetch_all_notification() {
		try {
			return voa_d_cyadmin_enterprise_app::fetch_all_notification( $start = 0, $limit = 0, $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );
			throw new service_exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * [fetch_all_notification_total 待处理应用的通知]
	 * @return [type] [进行提示待处理应用]
	 */
	public function fetch_all_notification_total() {
		try {
			return voa_d_cyadmin_enterprise_app::fetch_all_notification_total( $this->__shard_key );
		} catch( Exception $e ) {
			logger::error( $e );

			throw new service_exception( $e->getMessage(), $e->getCode() );
		}

	}
}
