<?php
/**
 * voa_c_admincp_office_askoff_view
 * 企业后台/微办公管理/请假审批/详情浏览
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askoff_view extends voa_c_admincp_office_askoff_base {

	public function execute() {

		$ao_id = $this->request->get('ao_id');
		$ao_id = rintval($ao_id, false);
		if ($ao_id <= 0 || !($askoff = $this->_service_single('askoff', $this->_module_plugin_id, 'fetch_by_id', $ao_id))) {
			$this->message('error', '指定请假审批信息不存在 或 已被删除');
		}
		$askoff['_status'] = isset($this->_uda_base->askoff_status[$askoff['ao_status']]) ? $this->_uda_base->askoff_status[$askoff['ao_status']] : '';

		//格式化审批主表信息数据
		$uda_format = &uda::factory('voa_uda_frontend_askoff_format');
		$uda_format->askoff($askoff);

		//进度列表
		$proc_list = $this->_service_single('askoff_proc', $this->_module_plugin_id, 'fetch_by_ao_id', $ao_id);
		//当前请假涉及到的所有人员id
		$uids = array();
		//抄送给的用户列表
		$cc_users = array();
		//当前进度记录
		$current_proc = array();
		foreach ($proc_list as $k => &$v) {
			$uda_format->askoff_proc($v);
			$uids[$v['m_uid']] = $v['m_uid'];
			if (voa_d_oa_askoff_proc::STATUS_CARBON_COPY == $v['aopc_status']) {
				//抄送人
				$cc_users[$v['m_uid']] = $v['m_username'];
				//unset($proc_list[$k]);
				$v['_status'] = '抄送人';
				continue;
			}

			$v['_status'] = isset($this->_uda_base->askoff_proc_status[$v['aopc_status']]) ? $this->_uda_base->askoff_proc_status[$v['aopc_status']] : '';

			//当前进度记录
			if ($v['aopc_id'] == $askoff['aopc_id']) {
				$current_proc = $v;
			}
		}
		unset($k, $v);

		//读取回复信息列表
		$post_list = $this->_service_single('askoff_post', $this->_module_plugin_id, 'fetch_by_ao_id', $ao_id);

		//整理回复信息
		foreach ($post_list as $k => &$v) {
			$uda_format->askoff_post($v);
			$v['_created'] = rgmdate($v['aopt_created'], 'Y-m-d H:i');
			$uids[$v['m_uid']] = $v['m_uid'];
			if (voa_d_oa_askoff_post::FIRST_YES == $v['aopt_first']) {
				//请假主题信息

				$askoff = array_merge($askoff, $v);
				unset($post_list[$k]);
			}
		}
		voa_h_user::get_multi($uids);

		// 读取请假所有相关文件 by Deepseath@20141226#332
		$attachs = array();
		$serv_aoat = &service::factory('voa_s_oa_askoff_attachment', array('pluginid' => $this->_module_plugin_id));
		$attach_list = $serv_aoat->fetch_all_by_ao_id($ao_id);
		if ($attach_list) {
			// 请假文件所关联的公共附件ID
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
				$attachs[$v['aopt_id']][] = array(
					'at_id' => $v['at_id'],// 公共文件附件ID
					'id' => $v['ao_id'], // 请假文件ID
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

		$this->view->set('askoff', $askoff);
		$this->view->set('proc_list', $proc_list);
		$this->view->set('proc_count', count($proc_list));
		$this->view->set('cc_users', $cc_users);
		$this->view->set('current_proc', $current_proc);
		$this->view->set('post_list', $post_list);
		$this->view->set('post_count', count($post_list));
		$this->view->set('types', $this->_p_sets['types']);
		$this->view->set('timearea', $askoff['_begintime_ymdhi']. ' 至 ' . $askoff['_endtime_ymdhi']);

		// 请假图片
		$this->view->set('attach_list', array_key_exists(0, $attachs) ? $attachs[0] : array());

		$this->output('office/askoff/askoff_view');
	}

}
