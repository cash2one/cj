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
	 * 根据 corpid 列出所有企业信息
	 * @param string $corpid 企业 corpid
	 * @param array $orders 排序数组
	 * @return boolean|Ambigous <multitype:, unknown>
	 */
	public function list_by_wxcorpid($corpid, $orders = array()) {

		try {
			return $this->_d->list_by_wxcorpid($corpid, $orders);
		} catch (\Exception $e) {
			E(L($e->getCode().":".$e->getMessage()));
			return false;
		}
	}

	/**
	 * 根据 corpid 获取企业信息
	 * @param string $corpid 企业 corpid
	 * @return boolean|Ambigous <multitype:, unknown>
	 */
	public function get_by_wxcorpid($corpid) {

		try {
			return $this->_d->get_by_wxcorpid($corpid);
		} catch (\Exception $e) {
			E(L($e->getCode().":".$e->getMessage()));
			return false;
		}
	}

	/**
	 * 根据域名更新企业信息
	 * @param string $domain 域名信息
	 * @param array $enterprise 企业信息
	 */
	public function update_by_domain($domain, $enterprise) {

		try {
			// 如果需要更新 corpid
			if (!empty($enterprise['ep_wxcorpid'])) {
				$corpid = $enterprise['ep_wxcorpid'];
				$new_corpid = $corpid . '_bak';
				$this->_d->update_corpid_not_in_domain($domain, $corpid, $new_corpid);
			}

			return $this->_d->update_by_domain($domain, $enterprise);
		} catch (\Exception $e) {
			E(L($e->getCode() . ':' . $e->getMessage()));
			return false;
		}
	}

	/**
	 * 根据 ep_id 和 domain 更新企业信息
	 * @param int $ep_id 企业ID
	 * @param string $domain 域名
	 * @param array $enterprise 企业信息
	 */
	public function update_by_ep_id_notin_domain($ep_id, $domain, $enterprise) {

		try {
			return $this->_d->update_by_ep_id_notin_domain($ep_id, $domain, $enterprise);
		} catch (\Exception $e) {
			E(L($e->getCode() . ':' . $e->getMessage()));
			return false;
		}
	}

	/**
	 * 通过 ep_id 更新企业信息(初期只让改企业名称)
	 * @param int $ep_id 企业ID
	 * @param array $enterprise 企业信息
	 */
	public function update_by_ep_id($ep_id, $enterprise = array()) {

		// 更新的企业信息不能为空(初期只让改企业名称)
		if (empty($enterprise) || empty($enterprise['ep_name'])) {
			E('_ERR_ENTERPRISE_PROFILE_EMPTY');
			return false;
		}

		$ep_id = (int)$ep_id;
		// 企业ID不能为空
		if (empty($ep_id)) {
			E('_ERR_EP_ID_INVALID');
			return false;
		}

		return $this->_d->update($ep_id, array('ep_name' => $enterprise['ep_name']));
	}

	/**
	 * 获取指定日期的新增公司
	 * @param $date string 日期
	 *  + s_time string 开始日期
	 *  + e_time string 结束日期
	 */
	public function list_by_date($date, $page_option) {

		return $this->_d->list_by_date($date, $page_option);
	}

	/**
	 * 获取指定日期的新增公司数量
	 * @param $date string 日期
	 *  + s_time string 开始日期
	 *  + e_time string 结束日期
	 */
	public function count_by_date($date) {

		return $this->_d->count_by_date($date);
	}

	/**
	 * 获取付费公司信息
	 * @param $ep_list array 公司id
	 * @param $date array 时间范围
	 */
	public function list_pay_company_info($ep_list, $date, $page_option) {

		return $this->_d->list_pay_company_info($ep_list, $date, $page_option);
	}
}
