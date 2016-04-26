<?php

/**
 * @Author: ppker
 * @Date:   2015-07-30 14:59:47
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-07 11:50:02
 */
class voa_uda_cyadmin_enterprise_message_read extends voa_uda_cyadmin_enterprise_base {
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if( $this->__service == null ) {
			$this->__service = new voa_s_cyadmin_enterprise_message_read ();
		}
	}

	/**
	 * [insert 插入已读消息]
	 *
	 * @param  [type] $data [description]
	 *
	 * @return [type]       [description]
	 */
	public function insert( $data ) {
		return $this->__service->insert( $data );
	}
}
