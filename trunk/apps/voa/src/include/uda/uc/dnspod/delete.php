<?php
/**
 * voa_uda_uc_dnspod_delete
 * 统一数据访问/dnspod cname 删除操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_dnspod_delete extends voa_uda_uc_dnspod_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 删除 dnspod cname 指向
	 * @param string $cname 二级域名
	 * @return boolean
	 */
	public function rm_cname($cname) {

		if (!$cname || in_array($cname, array('@', 'www', '*'))) {
			$this->errmsg(100, '内部错误：要删除的域名有错误');
			return false;
		}

		/** dnspod 数据操作方法 */
		$serv_dp = &service::factory('voa_s_uc_dnspod', array('pluginid' => 0));
		// 查出域名绑定记录
		$data = $serv_dp->fetch_by_cname($cname);
		/** 如果该域名信息不存在 */
		if (empty($data)) {
			return true;
		}

		/** 如果还未指向, 则 */
		if (empty($data['dp_zoneid']) || empty($data['dp_record_id']) || voa_d_uc_dnspod::STATUS_BANDING == $data['dp_status']) {
			$serv_dp->delete_by_ids($data['dp_id']);
			return true;
		}

		/** 调用接口 */
		$sql = array();
		try {
			/** dnspod 接口方法 */
			$dnspod = dnspod::get_instance();
			$params = array(
				'domain_id' => $data['dp_zoneid'],
				'record_id' => $data['dp_record_id'],
			);
			$result = array();
			$this->remove_record($params, $result);
			$sql['dp_status'] = voa_d_uc_dnspod::STATUS_REMOVE;
		} catch (Exception $e) {
			logger::error($e);
			$sql['dp_status'] = voa_d_uc_dnspod::STATUS_REMOING;
		}

		$serv_dp->update($sql, array('dp_id' => $data['dp_id']));

		return true;
	}

}
