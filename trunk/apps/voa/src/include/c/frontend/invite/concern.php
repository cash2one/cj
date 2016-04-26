<?php

/**
 * voa_c_frontend_invite_concern
 * 关注企业号，企业号的二维码
 * Created by zhoutao.
 * Created Time: 2015/7/16  17:49
 */
class voa_c_frontend_invite_concern extends voa_c_frontend_invite_base {

	//不强制登录，允许外部访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		if (!empty($this->_invite_setting['logo'])) {
			$logo = voa_h_attach::attachment_url($this->_invite_setting['logo']);
			$this->view->set('logo', $logo); // logo图片地址
		}

		// 如果没有本地qrcode
		if (!array_key_exists('local_qrcode', $this->_setting) || empty($this->_setting['local_qrcode'])) {
			// 获取微信二维码
			$uda = &uda::factory('voa_uda_frontend_attachment_insert');
			$attachment = array();
			$result = $uda->upload($attachment, $this->_setting['qrcode'], 'remote');
			if ($result) {
				$at_id = $attachment['at_id'];
				$serv_common = &uda::factory('voa_s_oa_common_setting');
				if (!array_key_exists('local_qrcode', $this->_setting)) {
					$serv_common->insert(
						array(
							'cs_key' => 'local_qrcode',
							'cs_value' => voa_h_attach::attachment_url($at_id),
							'cs_type' => 0,
							'cs_comment' => '本地微信企业二维码',
						)
					);
				} elseif (empty($this->_setting['local_qrcode'])) {
					$serv_common->update(array('cs_value' => voa_h_attach::attachment_url($at_id)), array('cs_key' => 'local_qrcode'));
				}
			}

			// 清理缓存
			$uda_base = &uda::factory('voa_uda_frontend_base');
			$uda_base->update_cache();

			$serv = &service::factory('voa_s_oa_common_setting', array('pluginid' => 0));
			$data = $serv->fetch_all();
			/** 重新整理数据 */
			$arr = array();
			foreach ($data as $v) {
				if (voa_d_oa_common_setting::TYPE_ARRAY == $v['cs_type']) {
					$arr[$v['cs_key']] = unserialize($v['cs_value']);
				} else {
					$arr[$v['cs_key']] = $v['cs_value'];
				}
			}
			$this->_setting = $arr;
		}

		$this->view->set('sitename', $this->_setting['sitename']); // 企业名称
		$this->view->set('qrcode', $this->_setting['local_qrcode']); // 企业二维码
		$this->_output('mobile/invite/concern');

		return true;
	}

}