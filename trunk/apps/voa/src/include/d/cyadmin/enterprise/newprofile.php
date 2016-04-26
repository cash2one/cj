<?php

/**
 * voa_d_cyadmin_enterprise_profile
 *
 * Created by zhoutao.
 * Created Time: 2015/7/29  18:22
 */
class voa_d_cyadmin_enterprise_newprofile extends voa_d_abstruct {

	/** 初始化 */
	public function __construct( $cfg = null ) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.enterprise_profile';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'ep_id';

		$this->_prefield = 'ep_';
		parent::__construct( null );
	}

}
