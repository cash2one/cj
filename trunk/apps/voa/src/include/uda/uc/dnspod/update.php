<?php
/**
 * voa_uda_uc_dnspod_update
 * 统一数据访问/dnspod cname 更新操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_dnspod_update extends voa_uda_uc_dnspod_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 编辑 dnspod cname 指向
	 * @param string $cname 二级域名
	 * @param string $domain 所属服务器名称
	 * @return boolean
	 */
	public function mv_cname($cname, $domain) {

		/** 主/泛域名不能编辑 */
		if (!$cname || in_array($cname, array('@', 'www', '*'))) {
			$this->errmsg(100, '内部错误：要修改的域名有错误');
			return false;
		}

		/** dnspod 数据操作方法 */
		$serv_dp = &service::factory('voa_s_uc_dnspod', array('pluginid' => 0));
		// 查出域名绑定记录
		$data = $serv_dp->fetch_by_cname($cname);
		if (empty($data) || empty($data['dp_zoneid']) || empty($data['dp_record_id'])) {
			$this->errmsg(100, '内部错误：未找到该域名的绑定记录');
			return false;
		}

		/** 如果是待删除状态, 则 */
		if (voa_d_uc_dnspod::STATUS_REMOING == $data['dp_status']) {
			$this->errmsg(100, '内部错误：该域名正处于待删除状态');
			return false;
		}

		/** 如果指向未改动 */
		if (voa_d_uc_dnspod::STATUS_BANDED == $data['dp_status'] && $data['dp_data'] == $domain) {
			return true;
		}

		/** 如果是待绑定状态, 则 */
		if (voa_d_uc_dnspod::STATUS_BANDING == $data['dp_status']) {
			$serv_dp->update(array('dp_data' => $domain), array('dp_id' => $data['dp_id']));
			return true;
		}

		$params = array(
			'record_id' => $data['dp_record_id'],
			'sub_domain' => $cname,
			'record_type' => 'CNAME',
			'record_line' => '默认',
			'value' => $domain,
			'mx' => '',
			'ttl' => $this->_sets['ttl']
		);

		/** 调用接口 */
		$sql = array();
		try {
			/** dnspod 接口方法 */
			$dnspod = dnspod::get_instance();
			$result = array();
			$dnspod->modify_record($params, $result);
			// 修改本地状态
			$sql = array(
				'dp_status' => voa_d_uc_dnspod::STATUS_BANDED,
				'dp_data' => $domain
			);
		} catch (Exception $e) {
			logger::error($e);
			$sql['dp_status'] = voa_d_uc_dnspod::STATUS_EDITING;
			$sql['dp_data'] = $domain;
		}

		$serv_dp->update($sql, array('dp_id' => $data['dp_id']));

		return true;
	}

}
