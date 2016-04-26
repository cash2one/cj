<?php
/**
 * add.php
 * 新增红包
 * $Author$
 * $Id$
 */

class voa_c_api_redpack_post_add extends voa_c_api_redpack_base {

	public function execute() {

		// 判断用户权限
		if (empty($this->_p_sets['privilege_uids']) || !in_array($this->_member['m_uid'], $this->_p_sets['privilege_uids'])) {
			return $this->_set_errcode('400:您没有权限发红包');
		}

		$params = $this->request->getx();
		$params['uid'] = $this->_member['m_uid'];
		$params['username'] = $this->_member['m_username'];
		$result = array();
		try {
			$uda = &uda::factory('voa_uda_frontend_redpack_add');
			if (!$uda->doit($params, $result)) {
				$this->_errcode = $uda->errcode;
				$this->_errmsg = $uda->errmsg;
				return true;
			}
		} catch (help_exception $e) {
			$this->_errcode = $e->getCode();
			$this->_errmsg = $e->getMessage();
			return true;
		} catch (Exception $e) {
			$this->_errcode = '500';
			$this->_errmsg = '服务器繁忙';
			return true;
		}

		// 把微信消息推入队列
		$result['_type'] = (int)$this->request->get('type');
		// 是否发给所有人
		$sendall = $this->request->get('sendall');
		if (!empty($sendall) && 0 < $sendall) {
			$cd_ids = null;
			$m_uids = null;
		} else {
			$cd_ids = $result['_cd_ids'];
			$m_uids = $result['_m_uids'];
		}

		$uda->push_wx_msg($result, $m_uids, $cd_ids, $this->session);
		//$result['url'] = '/frontend/redpack/new';
		$this->_result = $result;
	}

}
