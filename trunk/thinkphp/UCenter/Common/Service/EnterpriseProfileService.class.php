<?php
/**
 * EnterpriseProfileService.class.php
 * $author$
 */

namespace Common\Service;

class EnterpriseProfileService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/EnterpriseProfile");
	}

	/**
	 * 通过 ep_id 更新企业corpid
	 * @param int $ep_id 企业ID
	 * @param array $corpid 微信企业号corpid
	 */
	public function update_enterprise_corpid($corpid, $ep_id) {

		$corpid = (string)$corpid;
		// 更新的企业信息不能为空
		if (empty($corpid)) {
			E('_ERR_ENTERPRISE_CORPID_EMPTY');
			return false;
		}

		$ep_id = (int)$ep_id;
		// 企业ID不能为空
		if (empty($ep_id)) {
			E('_ERR_EP_ID_INVALID');
			return false;
		}

		return $this->_d->update($ep_id, array('epp_wxcorpid' => $corpid));
	}

	/**
	 * 根据corpid读取企业信息
	 * @param string $corpid 微信企业号corpid
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_corpid($corpid) {

		return $this->_d->get_by_corpid($corpid);
	}
}
