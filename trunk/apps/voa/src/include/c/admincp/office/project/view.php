<?php
/**
 * voa_c_admincp_office_project_view
 * 企业后台/微办公管理/工作台/项目详情
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_project_view extends voa_c_admincp_office_project_base {

	/** 项目进度状态描述 */
	protected $_project_mem_status = array(
			voa_d_oa_project_mem::STATUS_NORMAL => '项目参与者',
			voa_d_oa_project_mem::STATUS_UPDATE => '项目参与者',
			voa_d_oa_project_mem::STATUS_OUTOF => '项目发起人（不参与）',
			voa_d_oa_project_mem::STATUS_CC => '抄送者',
			voa_d_oa_project_mem::STATUS_QUIT => '已退出项目',
			voa_d_oa_project_mem::STATUS_REMOVE => '已删除',
	);

	public function execute() {

		$p_id = $this->request->get('p_id');
		$p_id = rintval($p_id, false);
		if (!($project = $this->_get_project($this->_module_plugin_id, $p_id))) {
			$this->message('error', '指定的项目不存在或已被删除');
		}

		/** 当前项目参与的人列表 */
		$memberList = array();
		$ccMemberList = array();
		$tmp = $this->_service_single('project_mem', $this->_module_plugin_id, 'fetch_by_p_id', $p_id);
		foreach ($tmp AS $_id => $_data) {
			$_data['_updated'] = rgmdate($_data['pm_updated'] ? $_data['pm_updated'] : $_data['pm_created'], 'Y-m-d H:i');
			$_data['_status'] = isset($this->_project_mem_status[$_data['pm_status']]) ? $this->_project_mem_status[$_data['pm_status']] : '';
			if ($_data['pm_status'] == voa_d_oa_project_mem::STATUS_CC) {
				$ccMemberList[$_id] = $_data;
			} else {
				$memberList[$_id] = $_data;
			}
		}
		unset($_id, $_data);

		/** 当前项目进度列表 */
		$progressList = array();
		$tmp = $this->_service_single('project_proc', $this->_module_plugin_id, 'fetch_by_p_id', $p_id);
		foreach ($tmp AS $_id => $_data) {
			$_data['_updated'] = rgmdate($_data['pp_updated'] ? $_data['pp_updated'] : $_data['pp_created'], 'Y-m-d H:i');
			$_data['_message'] = $this->_bbcode2html($_data['pp_message']);
			$progressList[$_id] = $_data;
		}

		// 读取任务所有相关文件
		$attachs = array();
		$serv_pat = &service::factory('voa_s_oa_project_attachment', array('pluginid' => $this->_module_plugin_id));
		$attach_list = $serv_pat->fetch_all_by_p_id($p_id);
		if ($attach_list) {
			// 任务文件所关联的公共附件ID
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
				$attachs[$v['pp_id']][] = array(
					'id' => $v['pat_id'], // 任务文件ID
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

		$this->view->set('project', $project);
		$this->view->set('memberList', $memberList);
		$this->view->set('progressList', $progressList);
		$this->view->set('ccMemberList', $ccMemberList);
		$this->view->set('memberCount', count($memberList));
		$this->view->set('progressCount', count($progressList));
		$this->view->set('editUrl', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('p_id' => $p_id)));
		$this->view->set('advancedUrl', $this->cpurl($this->_module, $this->_operation, 'advanced', $this->_module_plugin_id, array('p_id' => $p_id)));
		// 任务图片
		$this->view->set('attach_list', array_key_exists(0, $attachs) ? $attachs[0] : array());

		$this->output('office/project/view');

	}

}
