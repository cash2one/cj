<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/26
 * Time: 19:50
 */

namespace OaRpc\Service;

class EnterMessageReadService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("EnterpriseMessageRead");
	}

	/**
	 * @description 获取已读记录
	 * @param array $ep_id
	 * @return bool
	 */
	public function list_by_conds($ep_id, $uid) {

		try {
			return $this->_d->list_by_conds($ep_id, $uid);
		} catch (Exception $e) {
			E(L($e->getCode().":".$e->getMessage()));
			return false;
		}
	}


	public function mark_insert($logid, $uid) {

		try {
			// 生产数据
			if (is_array($logid)) {
				$data = array();
				foreach($logid as $k => $v) {
					$data['logid'] = $v;
					$data['uid'] = $uid;
					$this->_d->insert($data);
				}
			}

			return true;
		} catch (Exception $e) {
			E(L($e->getCode().":".$e->getMessage()));
			return false;
		}
	}


}