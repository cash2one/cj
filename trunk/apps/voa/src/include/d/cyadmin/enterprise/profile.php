<?php

/**
 * voa_d_cyadmin_enterprise_profile
 * 畅移后台/企业信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_cyadmin_enterprise_profile extends dao_mysql {
	/** 表名 */
	public static $__table = 'cyadmin.enterprise_profile';
	/** 主键 */
	private static $__pk = 'ep_id';
	/** 字段前缀 */
	private static $__prefix = 'ep_';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/**********************/

	/** 待建立 */
	const APPSTATUS_WAIT_OPEN = 0;
	/** 待删除 */
	const APPSTATUS_WAIT_DELETE = 1;
	/** 已建立 */
	const APPSTATUS_OPEN = 2;
	/** 已删除 */
	const APPSTATUS_DELETE = 3;
	/** 待关闭 */
	const APPSTATUS_WAIT_CLOSE = 4;
	/** 已关闭 */
	const APPSTATUS_CLOSE = 5;

	/**********************/

	/**
	 * <p><strong style="color:blue">【D】获取带前缀的字段名</strong></p>
	 * @author Deepseath
	 *
	 * @param string $field 无前缀的字段名
	 *
	 * @return string 带前缀的字段名
	 */
	public static function fieldname( $field ) {
		return self::$__prefix . $field;
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键值获取单条数据</strong></p>
	 * @author Deepseath
	 *
	 * @param int $value 主键值
	 */
	public static function fetch( $value, $shard_key = array() ) {
		return parent::_fetch_first( self::$__table,
			"SELECT * FROM %t WHERE %i='%d' AND %i<'%d' LIMIT 1",
			array( self::$__table, self::$__pk, $value, self::fieldname( 'status' ), self::STATUS_REMOVE ), $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键更新</strong></p>
	 * @author Deepseath
	 *
	 * @param array $data 需要更新的数据数组
	 * @param string|number $value 主键值
	 */
	public static function update( $data, $value, $shard_key = array() ) {
		if( empty( $data[ self::fieldname( 'status' ) ] ) ) {
			$data[ self::fieldname( 'status' ) ] = self::STATUS_UPDATE;
		}

		if( empty( $data[ self::fieldname( 'update' ) ] ) ) {
			$data[ self::fieldname( 'updated' ) ] = startup_env::get( 'timestamp' );
		}

		return parent::_update( self::$__table, $data, array( self::$__pk => $value ), false, false, $shard_key );
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键删除</strong></p>
	 * @author Deepseath
	 *
	 * @param array|number $value 主键值
	 */
	public static function delete( $value, $shard_key = array() ) {
		return self::delete_by_conditions( array( self::$__pk => $value ), $shard_key );
	}

	/**
	 * <p><strong style="color:blue">【D】获取表字段默认数据</strong></p>
	 * @author Deepseath
	 * @return array
	 */
	public static function fetch_all_field( $shard_key = array() ) {
		return parent::_fetch_all_field( self::$__table, $shard_key );
	}

	/**
	 * <p><strong style="color:blue">【D】读取所有数据</strong></p>
	 * @author Deepseath
	 *
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function fetch_all( $start = 0, $limit = 0, $shard_key = array() ) {
		return parent::_fetch_all( self::$__table,
			"SELECT * FROM %t WHERE %i<'%d' " . db_help::limit( $start, $limit ),
			array( self::$__table, self::fieldname( 'status' ), self::STATUS_REMOVE ), self::$__pk, $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】统计所有未删除的记录总数</strong></p>
	 * @author Deepseath
	 * @return number
	 */
	public static function count_all( $shard_key = array() ) {
		return (int) parent::_result_first( self::$__table,
			"SELECT COUNT(%i) FROM %t WHERE %i<'%d'",
			array( self::$__pk, self::$__table, self::fieldname( 'status' ), self::STATUS_REMOVE ), $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】数据入库</strong></p>
	 * @author Deepseath
	 *
	 * @param array $data 入库数据数组
	 * @param boolean $return_insert_id
	 * @param boolean $replace
	 */
	public static function insert( $data, $return_insert_id = false, $replace = false, $shard_key = array() ) {
		if( empty( $data[ self::fieldname( 'status' ) ] ) ) {
			$data[ self::fieldname( 'status' ) ] = self::STATUS_NORMAL;
		}

		if( empty( $data[ self::fieldname( 'created' ) ] ) ) {
			$data[ self::fieldname( 'created' ) ] = startup_env::get( 'timestamp' );
		}

		if( empty( $data[ self::fieldname( 'updated' ) ] ) ) {
			$data[ self::fieldname( 'updated' ) ] = $data[ self::fieldname( 'created' ) ];
		}

		return parent::_insert( self::$__table, $data, $return_insert_id, $replace, false, $shard_key );
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件更新</strong></p>
	 * @author Deepseath
	 *
	 * @param array $data 需要更新的数据数组
	 * @param array|string $conditions 更新条件
	 */
	public static function update_by_conditions( $data, $conditions, $shard_key = array() ) {
		if( empty( $data[ self::fieldname( 'status' ) ] ) ) {
			$data[ self::fieldname( 'status' ) ] = self::STATUS_UPDATE;
		}

		if( empty( $data[ self::fieldname( 'update' ) ] ) ) {
			$data[ self::fieldname( 'updated' ) ] = startup_env::get( 'timestamp' );
		}

		return parent::_update( self::$__table, $data, $conditions, false, false, $shard_key );
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件删除 </strong></p>
	 * @author Deepseath
	 *
	 * @param array $conditions 删除条件
	 *
	 * @return void
	 */
	public static function delete_by_conditions( $conditions, $shard_key = array() ) {
		return self::update_by_conditions( array(
			self::fieldname( 'status' )  => self::STATUS_REMOVE,
			self::fieldname( 'deleted' ) => startup_env::get( 'timestamp' )
		), $conditions, $shard_key );
	}

	/**********************************************/

	/**
	 * 根据条件计算总数
	 *
	 * @param  array $conditions
	 *                            $conditions = array(
	 *                            'field1' => '查询条件', // 运算符为 =
	 *                            'field2' => array('查询条件', '查询运算符'),
	 *                            'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *                            ...
	 *                            );
	 *
	 * @return number
	 */
	public static function count_by_conditions( $conditions, $start_date = 0, $end_date = 0, $shard_key = array() ) {
		$date_condi = array();
		if( $start_date > 0 ) {
			$date_condi[] = " ep_created > $start_date ";
		}
		if( $end_date > 0 ) {
			$date_condi[] = " ep_created < $end_date ";
		}
		$date_condi = implode( ' AND ', $date_condi );

		if( $date_condi ) {
			$date_condi = ' AND ' . $date_condi;
		}

		return (int) parent::_result_first( self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND ep_status<%d $date_condi", array(
			self::$__table,
			self::parse_conditions( $conditions ),
			self::STATUS_REMOVE
		), $shard_key );
	}

	public static function count_by_conditionsstr( $conditionsstr, $shard_key = array() ) {
		return parent::_result_first( self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND ep_status<%d", array(
			self::$__table,
			$conditionsstr,
			self::STATUS_REMOVE
		), $shard_key );
	}

	/**
	 * 列出指定条件的数据
	 *
	 * @param  array $conditions
	 *                            $conditions = array(
	 *                            'field1' => '查询条件', // 运算符为 =
	 *                            'field2' => array('查询条件', '查询运算符'),
	 *                            'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *                            ...
	 *                            );
	 * @param  number $start
	 * @param  number $limit
	 *
	 * @return array
	 */
	public static function fetch_by_conditions( $conditions, $start = 0, $limit = 0, $start_date = 0, $end_date = 0, $shard_key = array() ) {
		$date_condi = array();
		if( $start_date > 0 ) {
			$date_condi[] = " ep_created > $start_date ";
		}
		if( $end_date > 0 ) {
			$date_condi[] = " ep_created < $end_date ";
		}
		$date_condi = implode( ' AND ', $date_condi );

		if( $date_condi ) {
			$date_condi = ' AND ' . $date_condi;
		}

		return (array) parent::_fetch_all( self::$__table, "SELECT * FROM %t
            WHERE %i AND ep_status<%d $date_condi ORDER BY %i DESC" . db_help::limit( $start, $limit ), array(
			self::$__table,
			self::parse_conditions( $conditions ),
			self::STATUS_REMOVE,
			self::$__pk
		), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据查询条件拼凑 sql 条件
	 *
	 * @param array $conditions 查询条件
	 *                          $conditions = array(
	 *                          'field1' => '查询条件', // 运算符为 =
	 *                          'field2' => array('查询条件', '查询运算符'),
	 *                          'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *                          ...
	 *                          );
	 */
	public static function parse_conditions( $conditions = array() ) {
		$wheres = array();
		/** 遍历条件 */
		foreach( $conditions as $field => $v ) {
			/** 非当前表字段 */
			/*
				if (!in_array($field, self::$__fields)) {
			continue;
			}
			*/
			$f_v  = $v;
			$gule = '=';
			/** 如果条件为数组, 则 */
			if( is_array( $v ) ) {
				$f_v  = $v[0];
				$gule = empty( $v[1] ) ? '=' : $v[1];
			}

			$wheres[] = db_help::field( $field, $f_v, $gule );
		}

		return empty( $wheres ) ? 1 : implode( ' AND ', $wheres );
	}

	/**
	 * 列出指定企业ID的企业信息列表
	 *
	 * @param array $ep_ids
	 *
	 * @return array
	 */
	public static function fetch_all_by_ids( $ep_ids, $shard_key = array() ) {
		return (array) self::fetch_by_conditions( array(
			'ep_id' => $ep_ids
		), 0, 0, $shard_key );
	}

	/**
	 * <p><strong style="color:blue">根据 domain 获取单条数据</strong></p>
	 * @author Deepseath
	 *
	 * @param string $domain 域名
	 */
	public static function fetch_by_domain( $domain ) {
		return parent::_fetch_first( self::$__table,
			"SELECT * FROM %t WHERE ep_domain=%s AND %i<'%d' LIMIT 1",
			array( self::$__table, $domain, self::fieldname( 'status' ), self::STATUS_REMOVE ), array()
		);
	}

	/**
	 * <p><strong style="color:blue">根据 corpid 获取单条数据</strong></p>
	 * @author Deepseath
	 *
	 * @param int $corpid corpid
	 */
	public static function fetch_by_corpid( $corpid ) {
		return parent::_fetch_first( self::$__table,
			"SELECT * FROM %t WHERE ep_wxcorpid=%s AND %i<'%d' LIMIT 1",
			array( self::$__table, $corpid, self::fieldname( 'status' ), self::STATUS_REMOVE ), array()
		);
	}

}
