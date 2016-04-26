<?php
/**
 * voa_uda_uc_dnspod_insert
 * 统一数据访问/dnspod cname 入库操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_dnspod_insert extends voa_uda_uc_dnspod_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * add_cname
	 * 添加域名 cname 记录
	 * @param $cname 二级域名
	 * @param $domain 所属服务器域名
	 *
	 * @return integer
	 */
	public function add_cname($cname, $domain) {

		/** dnspod 数据操作方法 */
		$serv_dp = &service::factory('voa_s_uc_dnspod', array('pluginid' => 0));

		// 先查一下本地有没有这个数据
		$data = $serv_dp->fetch_by_cname($cname);
		if ($data && voa_d_uc_dnspod::STATUS_BANDED == $data['dp_status']) {
			return true;
		}

		$params = array(
			'sub_domain' => $cname,
			'record_type' => 'CNAME',
			'record_line' => '默认',
			'value' => $domain,
			'mx' => '',
			'ttl' => $this->_ttl
		);

		/** 记录 */
		$sql = array(
			'dp_zoneid' => $this->_zoneid,
			'dp_cname' => $cname,
			'dp_type' => 'CNAME',
			'dp_data' => $domain,
			'dp_ttl' => $this->_ttl
		);

		/** 调用接口 */
		$status = voa_d_uc_dnspod::STATUS_BANDING;
		$record_id = 0;
		try {
			/** dnspod 接口方法 */
			$dnspod = dnspod::get_instance();
			$result = array();
			$dnspod->create_record($params, $result);
			$record_id = $result['record']['id'];
			$status = voa_d_uc_dnspod::STATUS_BANDED;
		} catch (Exception $e) {
			logger::error($e);
		}

		$sql['dp_status'] = $status;
		$sql['dp_record_id'] = $record_id;
		if ($data) {
			if ($record_id) {
				foreach ($sql as $k => $v) {
					if ($data[$k] == $v) {
						unset($sql[$k]);
					}
				}
			}

			if ($sql && $data['dp_id']) {
				$serv_dp->update($sql, array('dp_id' => $data['dp_id']));
			}
		} else {
			$serv_dp->insert($sql);
		}

		return voa_d_uc_dnspod::STATUS_BANDING == $status ? false : true;
	}
}
