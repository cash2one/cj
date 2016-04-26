<?php
/**
 * voa_c_admincp_office_notice_base
 * 企业后台/微办公/通知公告/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_notice_base extends voa_c_admincp_office_base {

	/**
	 * 重复提醒间隔时间，允许自定义，但不能小于1800秒！！
	 * @var number
	 */
	public $repeattimestamp = 1800;

	/**
	 * 附件浏览基本地址
	 * @var string
	 */
	public $attach_view_base_url = null;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->attach_view_base_url = config::get(startup_env::get('app_name').'.attachment.attach_url');
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 返回一个空的公告数据
	 * @return array
	 */
	protected function _notice_empty() {
		$serv = &service::factory('voa_s_oa_notice', array('pluginid' => $this->_module_plugin_id));
		return $serv->fetch_all_field();
	}

	/**
	 * 返回指定id的公告信息并格式化
	 * @param number $nt_id
	 * @return boolean | array
	 */
	protected function _notice($nt_id = 0) {
		$serv = &service::factory('voa_s_oa_notice', array('pluginid' => $this->_module_plugin_id));
		$notice = $serv->fetch_by_id($nt_id);
		if (empty($notice)) {
			return false;
		}
		$uda = &uda::factory('voa_uda_frontend_notice_format');
		$uda->format($notice, $this->attach_view_base_url);

		return $notice;
	}

	/**
	 * 显示添加/编辑控制
	 * @param number $nt_id 公告id
	 * @param string $is_new 新增=true，编辑=false
	 */
	protected function _notice_edit($nt_id = 0, $is_new = false) {

		$nt_id = rintval($nt_id);
		if (!$is_new && $nt_id <= 0) {
			// 如果是编辑，但nt_id为空
			$this->message('error', '指定公告不存在');
		}

		if ($is_new) {
			// 新增
			$notice = $this->_notice_empty();
			$notice['_message'] = '';
			$nt_id = 0;
		} else {
			// 编辑
			if ($nt_id <= 0) {
				$this->message('error', '指定公告不存在1');
			}
			$notice = $this->_notice($nt_id);
			if (empty($notice)) {
				// 如果是编辑，但公告内容不存在
				$this->message('error', '指定公告不存在 或 已被删除');
			}
		}

		// 初始化编辑器
		$ueditor = new ueditor();

		$content_key = 'nt_message';

		if ($this->_is_post()) {
			$new_notice = array();
			$uda = &uda::factory('voa_uda_frontend_notice_update');
			if (!$uda->notice_update($this->_module_plugin_id, $notice, $new_notice, $this->attach_view_base_url)) {
				$this->message('error', $uda->error);
			}
			if ($is_new) {
				$this->message('success', '新增通知公告信息操作完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
			} else {
				$this->message('success', '编辑通知公告信息操作完毕', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('nt_id' => $nt_id)), false);
			}
		}

		// 编辑器资源路径
		$ueditor->ueditor_home_url = config::get(startup_env::get('app_name').'.ueditor.ueditor_home_url');
		// 处理上传文件路径
		$ueditor->server_url = '/admincp/ueditor/';

		$ueditor->ueditor_config = array('toolbars' => '_all', 'textarea' => $content_key, 'initialFrameHeight' => 300, 'initialContent' => $notice['_message'], 'elementPathEnabled' => false);
		if (!$ueditor->create_editor('nt_message', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}

		// 选择的部门
		if ($notice['nt_receiver']) {
			$department_selected = @unserialize($notice['nt_receiver']);
			$dpeartment_selected = (array)$department_selected;
		} else {
			$department_selected = array();
		}

		$this->view->set('notice', $notice);
		$this->view->set('repeattimestamp', $this->repeattimestamp);
		$this->view->set('nt_id', $nt_id);
		$this->view->set('ueditor_output', $ueditor_output);
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, $is_new ? 'add' : 'edit', $this->_module_plugin_id, array('nt_id' => $nt_id)));
		$this->view->set('department_list', $this->_department_list());
		$this->view->set('department_selected', $department_selected);

		$this->output('office/notice/notice_form');

		return;
	}

}
