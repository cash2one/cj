<?php
/**
 * voa_uda_frontend_member_delete
 * 统一数据访问/用户表/删除
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_member_delete extends voa_uda_frontend_member_base {

	protected $_uda_member_get = null;

	public function __construct() {
		parent::__construct();
		if ($this->_uda_member_get === null) {
			$this->_uda_member_get = &uda::factory('voa_uda_frontend_member_get');
		}
	}

	/**
	 * 删除指定m_uid的用户信息
	 * @param number $m_uid
	 * @param boolean $delete_qywx 删除企业微信用户
	 * @return boolean
	 */
	public function delete($m_uid, $delete_qywx = false) {

		if (!$m_uid || !is_numeric($m_uid)) {
			return $this->set_errmsg(voa_errcode_oa_member::MEMBER_UID_NULL);
		}

		try {

            $member = array();
            //删除企业微信用户
            if ($delete_qywx === true) {
                $this->_uda_member_get->member_by_uid($m_uid, $member);
            }
			$this->serv_member->begin();

			/** 以下删除相关 member 数据 */
			$this->serv_member->delete($m_uid);
			$this->serv_member_department->delete_by_m_uid($m_uid);
			$this->serv_member_field->delete_by_ids($m_uid);
			$this->serv_member_search->delete_by_m_uid($m_uid);
			$this->serv_member_share->delete_by_m_uid($m_uid);
            //删除企业微信用户
            if ($delete_qywx === true) {
                $wxqyab = &voa_wxqy_addressbook::instance();
                $result = array();
                if (!$wxqyab->user_delete($member['m_openid'], $result)) {

                    if ($wxqyab->errcode == '-100') {
                        $this->serv_member->rollback();
                        $this->errcode = $wxqyab->errcode;
                        $this->errmsg = '连接微信企业号失败';
                        return false;
                    }
                }
            }
			$this->serv_member->commit();

		} catch (Exception $e) {
			$this->serv_member->rollback();
			logger::error($e);
			if ($e->getCode()) {
				$this->errcode = $e->getCode();
				$this->errmsg = $e->getMessage();
			} else {
				$this->set_errmsg(voa_errcode_oa_member::MEMBER_DELETE_FAILED);
			}
			return false;
		}

		return true;
	}

}
