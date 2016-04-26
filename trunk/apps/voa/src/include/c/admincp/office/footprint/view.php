<?php
/**
 * voa_c_admincp_office_footprint_view
 * 企业后台/微办公/销售轨迹/详情浏览
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_footprint_view extends voa_c_admincp_office_footprint_base {

	public function execute() {

		$fp_id = $this->request->get('fp_id');
		$fp_id = rintval($fp_id, false);
		// @ 当前轨迹主要信息
		$footprint = array();
		if ($fp_id <= 0 || !($footprint = $this->_service_single('footprint', $this->_module_plugin_id, 'fetch_by_id', $fp_id))) {
			$this->message('error', '指定的销售轨迹信息不存在 或 已删除');
		}

		// 格式化轨迹主要信息
		$uda_format = &uda::factory('voa_uda_frontend_footprint_format');
		$uda_format->format($footprint);

		// @ 找到相关附件
		$attach_list = array();
		$fp_attach_list = $this->_service_single('footprint_attachment', $this->_module_plugin_id, 'fetch_by_fp_id', array($fp_id));
		if ($fp_attach_list) {
			$at_ids = array();
			foreach ($fp_attach_list as $attach) {
				$at_ids[] = $attach['at_id'];
			}
			unset($fp_attach_list, $attach);
			$attach_list = $this->_service_single('common_attachment', 'fetch_by_ids', $at_ids);
			$uda_attachment_format = &uda::factory('voa_uda_frontend_attachment_format');
			$uda_attachment_format->format_list($attach_list);
		}

		// @ 找到相关分享人
		$mem_list = $this->_service_single('footprint_mem', $this->_module_plugin_id, 'fetch_by_fp_id', $fp_id);
		$m_uids = array();
		$m_uids[] = $footprint['m_uid'];
		if ($mem_list) {
			foreach ($mem_list as $mem) {
				$m_uids[] = $mem['m_uid'];
			}
			unset($mem);
		}
		// 获取用户信息
		$member_list = voa_h_user::get_multi($m_uids, array('_department', '_job', '_realname'));
		$uda_member_format = &uda::factory('voa_uda_frontend_member_format');
		$uda_member_format->format_list($member_list);
		foreach ($mem_list as &$mem) {
			if (isset($member_list[$mem['m_uid']])) {
				$mem['_department'] = $member_list[$mem['m_uid']]['_department'];
				$mem['_job'] = $member_list[$mem['m_uid']]['_job'];
				$mem['_realname'] = $member_list[$mem['m_uid']]['_realname'];
			} else {
				$mem['_department'] = $mem['_job'] = $mem['_realname'] = '';
			}
		}

		if (isset($member_list[$footprint['m_uid']])) {
			$footprint['_department'] = $member_list[$footprint['m_uid']]['_department'];
			$footprint['_job'] = $member_list[$footprint['m_uid']]['_job'];
			$footprint['_realname'] = $member_list[$footprint['m_uid']]['_realname'];
		} else {
			$footprint['_department'] = $footprint['_job'] = $footprint['_realname'] = '';
		}

		$this->view->set('fp_id', $fp_id);
		$this->view->set('footprint', $footprint);
		$this->view->set('attach_list', $attach_list);
		$this->view->set('mem_list', $mem_list);
		$this->view->set('attach_count', count($attach_list));

		$this->output('office/footprint/footprint_view');
	}

}
