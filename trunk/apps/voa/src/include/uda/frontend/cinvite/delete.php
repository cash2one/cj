<?php

/**
 * voa_uda_frontend_cinvite_delete
 * 邀请人员/uda/删除数据
 * Created by zhoutao.
 * Created Time: 2015/7/8  17:23
 */
class voa_uda_frontend_cinvite_delete extends voa_uda_frontend_cinvite_base {
	/** service 类*/
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if( $this->__service == null ) {
			$this->__service = new voa_s_oa_cinvite_personnel();
		}
	}

	/**
	 * 删除邀请人信息
	 *
	 * @param array $per_id 请求的参数
	 *
	 * @return boolean
	 */
	public function delete_invites( $ids ) {
		try {
			$this->__service->begin();
			$this->__service->delete( $ids );
			$this->__service->commit();
		} catch( Exception $e ) {
			$this->__service->rollback();

			return $this->set_errmsg( voa_errcode_oa_cinvite::DELETE_INVITE_FAILED );
		}

		return true;
	}

}
