<?php

/**
 * enterpriseadminer.php
 * 官网普通登录手机号码获取公司信息数据层
 * Created by zhoutao.
 * Created Time: 2015/6/26  18:18
 */
class voa_uda_uc_login_enterpriseadminer extends voa_uda_uc_login_base {

	/**
	 * 根据手机号码查询公司信息
	 * @param $mobile 手机号
	 * @param $list 公司信息
	 * @return bool
	 */
	public function mobile($mobile, &$list) {

		// 获取根据手机号获取的公司信息关联列表
		$list_information = $this->_enterprise_adminer->list_by_conds(
			array(
				'mobilephone' => $mobile['mobilephone'],
				'status' => array(1, 2)
			));

		// 处理返回的数据
		if (!empty($list_information)) {
			// 整理关联表ep_id
			$list_data = null;
			$this->_handle($list_information, $list_data);
			// 获取ep_id对应的信息
			$list = null;
			$this->_gainInformation($list_data, $list);
		} else {
			$list = null;
		}
		return true;
	}

	/**
	 * 处理关联表数据
	 * @param $list 关联表信息
	 * @param $data 处理过后的数据
	 */
	protected function _handle($list, &$data) {
//		$list数据结构
//		Array(
//			'1' => Array(
//				'ad_id' => 1,
//				'ep_id' => 1,
//				'ca_id' => 1,
//				'mobilephone' => 12312312312,
//				'status' => 0,
//				'created' => 0,
//				'updated' => 0,
//				'deleted' => 0,
//			),
//			'2' => ...
//		);
		$data = null;
		foreach ($list as $key => $val) {
			$data[] = $val['ep_id'];
		}
		return true;
	}

	/**
	 * 根据ep_id 获取公司信息
	 * @param $ep_ids ep_id数组
	 * @param $data 整理后的数据
	 */
	protected function _gainInformation($ep_ids, &$data) {
//		$ep_ids数据结构
//		Array
//		(
//			'0' => ep_id
//			'1' => 5
//		)

		// 到uc_enterprise表查询数据
		$list_data = null;
		$list_data = $this->_enterprise->list_by_conds(
			array(
				'ep_id IN (?)' => $ep_ids
			)
		);
		// 处理$list_data 返回数据
		$data = null;
		foreach ($list_data as $key => $val) {
			$data[] = array(
				'ep_domain' => $val['ep_domain'],
				'ep_name' => $val['ep_name']
			);
		}
		return true;

//		$data处理后的数据结构
//		Array(
//			'0' => Array(
//				'ep_domain' => 'local.vchangyi.net',
//				'ep_name' => '按时打'
//			),
//			'1' => Array(
//				'ep_domain' => 'asd.vchangyi.net',
//				'ep_name' => '爱上阿萨'
//			)
//		);
	}

	/**
	 * 从网站后台编辑管理员信息，更新关联表
	 *
	 * @param $data ep_id ，当前手机号 ， 更改的手机号
	 * @param $out
	 * @return bool
	 */
	public function update_mobilephone($data, &$out) {

		// 查询所要更改的ad_id
		$search_data = array('ep_id' => $data['ep_id'], 'mobilephone' => $data['now_mobilephone']);
		$ad_id = $this->_enterprise_adminer->list_by_conds($search_data);
		if (empty($ad_id)) {
			return false;
		}

		$ad_id = array_column($ad_id, 'ad_id');
		$ad_id = (int)$ad_id[0];
		// 更新手机号
		$update_date = array('mobilephone' => $data['change_mobilephone']);
		$out = $this->_enterprise_adminer->update_by_conds(array('ad_id' => $ad_id), $update_date);
		return true;
	}

	/**
	 * 新增关联表入库
	 * @param $insert_data ep_id， ca_id， 手机号
	 * @param $ad_id
	 */
	public function insert_mobilephone($insert_data, &$ad_id) {

		$ad_id = $this->_enterprise_adminer->insert($insert_data);

		return true;
	}

	/**
	 * 删除关联表
	 * @param $delete_data ca_id, 手机号
	 * @param $out
	 * @return bool
	 */
	public function delete_mobilephone($delete_data, &$out) {

		$out = $this->_enterprise_adminer->delete_by_conds($delete_data);

		return true;
	}
}
