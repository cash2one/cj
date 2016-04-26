<?php

/**
 * CustomerService.class.php
 * $author$ zhubeihai
 */

namespace Sales\Service;

class SalesPartnerService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Sales/SalesPartner");
	}

	/**
	 * 新增联合跟进人
	 *
	 * @param array &$partner 联合跟进人
	 * @param array $params 传入的参数
	 * @param mixed $extend 扩展参数
	 *
	 * $author$ zhubeihai
	 */
	public function add_partner(&$partner, $params, $extend = array()) {

		// 获取入库参数
		$uid = (string)$extend['uid'];
		$username = (string)$extend['username'];

		//客户id
		$sc_id = (string)$params['sc_id'];
		//联合跟进人id
		$m_uids = (array)$params['m_uids'];

		// 用户信息不能为空
		if (empty($uid) || empty($username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 客户id不能为空
		if (empty($sc_id)) {
			$this->_set_error('_ERR_CUSTOMER_SCIDS_MESSAGE');
			return false;
		}

		// 联合跟进人不能为空
		if (count($m_uids) <= 0) {
			$this->_set_error('_ERR_PARTNER_MESSAGE');
			return false;
		}

		$partner_array = array();
		//遍历联合人id
		foreach ($m_uids as $m_uid){
			$partner_array[] = array(
				'sc_id' => $sc_id,
				'm_uid' => $m_uid,
				'cgm_status' => $this->_d->get_st_create(),
				'cgm_created' => NOW_TIME
			);
		}

		// 执行入库操作
		if (!$sp_ids = $this->_d->insert_all($partner_array)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		$partner['sp_ids'] = $sp_ids;
		return true;
	}
}