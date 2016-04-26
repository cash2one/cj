<?php
/**
 * voa_c_admincp_office_minutes_view
 * 企业后台/微办公管理/会议记录/浏览详情
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_minutes_view extends voa_c_admincp_office_minutes_base {

	public function execute() {

		$mi_id = $this->request->get('mi_id');
		$mi_id = rintval($mi_id, false);

		// 当前查看的会议记录主信息
		$minutes = array();

		if ($mi_id <= 0 || !($minutes = $this->_service_single('minutes', $this->_module_plugin_id, 'fetch_by_id', $mi_id))) {
			$this->message('error', '指定 '.$this->_module_plugin['cp_name'].' 数据不存在');
		}

		// 格式化会议记录主表信息
		$uda_minutes_format = &uda::factory('voa_uda_frontend_minutes_format');
		$uda_minutes_format->minutes($minutes);

		// 提取会议记录涉及到的人信息
		$mem_list = $this->_service_single('minutes_mem', $this->_module_plugin_id, 'fetch_by_conditions', array('mi_id' => $mi_id));

		// 所有参与人信息
		$member_list = array();
		$uda_member = &uda::factory('voa_uda_frontend_member_format');
		$uda_member->data_list($mem_list, 'm_uid', $member_list);

		// 格式化参与人信息
		$uda_minutes_format->minutes_mem_list($mem_list);

		// 需要获取的用户信息字段
		$member_fields = array('_department', '_job', 'm_username', 'm_uid', '_gender', '_realname');
		foreach ($mem_list as &$row) {
			foreach ($member_fields as $_k) {
				$row[$_k] = isset($member_list[$row['m_uid']][$_k]) ? $member_list[$row['m_uid']][$_k] : '';
			}
		}
		unset($row);

		// 提取会议记录回复信息
		$post_list = $this->_service_single('minutes_post', $this->_module_plugin_id, 'fetch_by_conditions', array('mi_id' => $mi_id));

		// 获取所有回复人信息
		$member_list = array();
		$uda_member->data_list($post_list, 'm_uid', $member_list);

		// 格式化回复信息
		$uda_minutes_format->minutes_post_list($post_list);

		// 需要获取的用户信息字段
		$member_fields = array('_department', '_job', 'm_username', 'm_uid', '_gender', '_realname');
		foreach ($post_list as &$row) {
			foreach ($member_fields as $_k) {
				$row[$_k] = isset($member_list[$row['m_uid']][$_k]) ? $member_list[$row['m_uid']][$_k] : '';
			}
		}

		foreach ($post_list as $mip_id => $mip) {
			if (voa_d_oa_minutes_post::FIRST_YES == $mip['mip_first']) {
				// 主题信息
				$minutes = array_merge($minutes, $mip);
				unset($post_list[$mip_id]);
				break;
			}
		}

		// 读取会议记录所有相关文件 by Deepseath@20141230#391
		$attachs = array();
		$serv_miat = &service::factory('voa_s_oa_minutes_attachment', array('pluginid' => startup_env::get('pluginid')));
		$attach_list = $serv_miat->fetch_all_by_mi_id($mi_id);
		if ($attach_list) {
			// 会议记录文件所关联的公共附件ID
			$at_ids = array();
			foreach ($attach_list as $v) {
				$at_ids[] = $v['at_id'];
			}

			$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
			$common_attach_list = $serv_at->fetch_by_ids($at_ids);

			foreach ($attach_list as $v) {
				if (!isset($common_attach_list[$v['at_id']])) {
					continue;
				}
				$at = $common_attach_list[$v['at_id']];
				$attachs[$v['mip_id']][] = array(
					'at_id' => $v['at_id'],// 公共文件附件ID
					'id' => $v['miat_id'], // 会议记录文件会议记录
					'filename' => $at['at_filename'],// 附件名称
					'filesize' => $at['at_filesize'],// 附件容量
					'mediatype' => $at['at_mediatype'],// 媒体文件类型
					'description' => $at['at_description'],// 附件描述
					'isimage' => $at['at_isimage'] ? 1 : 0,// 是否是图片
					'url' => voa_h_attach::attachment_url($v['at_id'], 0),// 附件文件url
					'thumb' => $at['at_isimage'] ? voa_h_attach::attachment_url($v['at_id'], 45) : '',// 缩略图URL
				);
			}
		}

		$this->view->set('mi_id', $mi_id);
		$this->view->set('minutes', $minutes);
		$this->view->set('mem_list', $mem_list);
		$this->view->set('post_list', $post_list);
		$this->view->set('mem_count', count($mem_list));
		$this->view->set('post_count', count($post_list));
		$this->view->set('attach_list', array_key_exists(0, $attachs) ? $attachs[0] : array());

		$this->output('office/minutes/minutes_view');
	}

}
