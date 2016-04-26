<?php

/**
 * insert.php
 *
 * Created by zhoutao.
 * Created Time: 2015/6/18  10:42
 */
class voa_uda_uc_fastlogin_insert extends voa_uda_uc_fastlogin_base {

	/**
	 * 关联信息表入库
	 * @param $userdata 微信返回的信息
	 * @param $out 入库返回的主键值
	 * @return bool
	 */
	public function insert_add($userdata, &$out) {
		if (empty($userdata['user_info']['userid'])) {
			$userdata['user_info']['userid'] = '';
		}
		if (empty($userdata['user_info']['name'])) {
			$userdata['user_info']['name'] = '';
		}
		if (empty($userdata['user_info']['mobile'])) {
			$userdata['user_info']['mobile'] = '';
		}
		$fastlogin_data = array(
			'email' => (string)$userdata['user_info']['email'],
			'userid' => (string)$userdata['user_info']['userid'],
			'name' => (string)$userdata['user_info']['name'],
			'mobile' => (string)$userdata['user_info']['mobile'],
			'corpid' => (string)$userdata['corp_info']['corpid'],
			'corp_name' => (string)$userdata['corp_info']['corp_name'],
			'corp_type' => (string)$userdata['corp_info']['corp_type'],
			'corp_round_logo_url' => (string)$userdata['corp_info']['corp_round_logo_url'],
			'corp_square_logo_url' => (string)$userdata['corp_info']['corp_square_logo_url'],
			'corp_user_max' => (string)$userdata['corp_info']['corp_user_max'],
			'corp_agent_max' => (string)$userdata['corp_info']['corp_agent_max'],
			'corp_wxqrcode' => (string)$userdata['corp_info']['corp_wxqrcode']
		);
		// 插入uc_fastinformation表
		if (empty($fastlogin_data['email'])) {
			logger::error(print_r($userdata, true) . '没有基本的email');
			return false;
		}
		try {
			//判断是否已存在记录
			$finfo = $this->_fastinformation->get_by_conds(array('email =?' => $fastlogin_data['email'] , 'corpid =?' => $fastlogin_data['corpid']));
			if (!empty($finfo) && !empty($finfo['fa_id'])) {
				$out = $finfo['fa_id'];
				return true;
			}
			$out = $this->_fastinformation->insert($fastlogin_data);
			// 返回存储的表主键fa_id
			$out = $out['fa_id'];
		} catch (Exception $e) {
			logger::error($e);
		}
		return true;
	}

	/**
	 * 关联表入库
	 * @param $in fa_id、ep_id、ca_id、corpid
	 * @return bool
	 */
	public function insert_add_fastlogin ($in) {
		// 关联信息表数据
		$fa_data = $this->_fastinformation->list_by_conds(array('fa_id' => $in['fa_id']));
		$data = array(
			'fa_id' => $in['fa_id'],
			'email' => $fa_data[$in['fa_id']]['email'],
			'corpid' => $fa_data[$in['fa_id']]['corpid'],
			'ep_id' => $in['ep_id'],
			'ca_id' => $in['ca_id'],
			'lastlogin' => startup_env::get('timestamp')
		);
		if ($this->_fastlogin->list_by_conds(array('fa_id' => $data['fa_id']))) {
			logger::error('关联信息已经存在,取消此次存储，触发所在的类：voa_uda_uc_fastlogin_insert，方法：insert_add_fastlogin');
			return true;
		};
		$this->_fastlogin->insert($data);
		return true;
	}
}
