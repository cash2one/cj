<?php

/**
 * @Author: ppker
 * @Date:   2015-07-30 16:21:08
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-19 15:06:36
 */
class voa_d_cyadmin_enterprise_message_log extends voa_d_abstruct {

	/** 初始化 */
	public function __construct( $cfg = null ) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.enterprise_message_log';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'logid';
		/** 字段前缀 */
		parent::__construct( null );
	}

	public function count_by_complex( $data ) {
		// 真正的总数 等于 要减去的已读的条数
		$total = $this->_count_by_complex( "(epid IN (?)) and status < 3", $data, '*' );

		return $total;
	}

	public function list_by_complex( $data, $limit ) {

		$count_logid = count( $data[1] );
		$logids      = array();
		if( $count_logid ) {
			while( count( $logids ) < $count_logid ) {
				$logids[] = '?';
			}
		}
		//$data[2] = array($data[2]); // 先转成数组

		$ext_data = array_merge( $data[0], $data[1] );
		array_push( $ext_data, self::STATUS_DELETE );
		if( ! empty( $data[2] ) ) {
			array_push( $ext_data, $data[2] );
		}
		if( $logids ) { // AND title LIKE ?
			if( ! empty( $data[2] ) ) {

				$re_data = $this->_list_by_complex( "(epid IN (?,?) AND logid NOT IN (" . implode( ',', $logids ) . ") ) AND " . $this->_prefield . "status < ? AND title LIKE ?", $ext_data, $limit, array( 'created' => 'DESC' ) );
			} else {
				$re_data = $this->_list_by_complex( "(epid IN (?,?) AND logid NOT IN (" . implode( ',', $logids ) . ") ) AND " . $this->_prefield . "status < ?", $ext_data, $limit, array( 'created' => 'DESC' ) );
			}

		} else {
			if( ! empty( $data[2] ) ) {
				$re_data = $this->_list_by_complex( "(epid IN (?,?)) AND " . $this->_prefield . "status < ? AND title LIKE ?", $ext_data, $limit, array( 'created' => 'DESC' ) );
			} else {
				$re_data = $this->_list_by_complex( "(epid IN (?,?)) AND " . $this->_prefield . "status < ?", $ext_data, $limit, array( 'created' => 'DESC' ) );
			}
		}

		return $re_data;
	}

}
