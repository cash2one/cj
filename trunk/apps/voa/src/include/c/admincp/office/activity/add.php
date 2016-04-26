<?php

/**
 * voa_c_admincp_office_activity_add
 * 企业后台/企业文化/活动报名/添加
 * Created by zhoutao.
 * Created Time: 2015/5/13  16:52
 */
class voa_c_admincp_office_activity_add extends voa_c_admincp_office_activity_base {

	public function execute() {
		$ac['ispost'] = $this->_is_post();
		if (isset($ac['ispost']) && $ac['ispost']) {
			try {
				$ac = $this->request->postx();
				$data = null;
				$uda = &uda::factory('voa_uda_frontend_activity_add');
				if (empty($ac['m_uid'])) {
					$this->_admincp_error_message('10000', '新发起活动必须选择活动发起人');
					return true;
				}
				if (empty($ac['dp']) && empty($ac['users']) && $ac['all_company'] != 1) {
					$this->_admincp_error_message('10005', '不能发起没有参与人的活动');
					return true;
				}
				if (!$uda->addact($ac, $data, $this->session)) {
					if ($uda->errmsg) {
						$this->message('error', $uda->errmsg);
					} else {
						$this->message('error', '添加失败');
					}
				}
				$this->_admincp_success_message('添加成功！', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
		}

		// 初始化编辑器
		$ueditor = new ueditor();
		$content_key = 'content';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = config::get(startup_env::get('app_name') . '.ueditor.ueditor_home_url');
		// 处理上传文件路径
		$ueditor->server_url = '/admincp/ueditor/';

		$ueditor->ueditor_config = array('toolbars' => '_mobile', 'textarea' => $content_key, 'initialFrameHeight' => 300, 'initialContent' => isset($data['content']) ? $data['content'] : '', 'elementPathEnabled' => false);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}

		$this->view->set('expand_css', 'activity/activity.css');
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, 'add', $this->_module_plugin_id));
		$this->view->set('ueditor_output', $ueditor_output);
		$this->output('office/activity/add');
		return true;
	}

}
