<?php

/**
 * voa_c_admincp_office_activity_edit
 * 统一数据访问/活动报名/编辑活动
 * Created by zhoutao.
 * Created Time: 2015/5/14  21:07
 */
class voa_c_admincp_office_activity_edit extends voa_c_admincp_office_activity_base {

	public function execute() {

		$ispost = $this->_is_post();
		if ($ispost) {
			$post = $this->request->postx();
			try {
				$ac = $post['ac'];
				$uda = &uda::factory('voa_uda_frontend_activity_add');
				$true = null;
				if (!$uda->updataact($post, $true, $this->session)) {
					if ($uda->errmsg) {
						$this->message('error', $uda->errmsg);
					} else {
						$this->message('error', '编辑失败');
					}
				}
				$this->_admincp_success_message('编辑成功！', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('acid' => $ac)));
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
		}

		$ac = $this->request->get('acid');
		if (isset($ac) && $ac != '') {
			try {
				$acid = array('acid' => $ac);
				//根据acid查询活动
				$uda = &uda::factory('voa_uda_frontend_activity_get');
				$data = null;
				if (!$uda->getact($acid, $data)) {
					$this->_admincp_error_message('抱歉，没有这条数据');
				}
				//数据处理
				$handle = null;
				$data['content'] = nl2br($data['content']);
				$uda->handle($data, $handle);
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
		} else {
			$this->_admincp_error_message('抱歉，地址输入错误');
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
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id));
		$this->view->set('ueditor_output', $ueditor_output);
		$this->view->set('data', $handle);
		$this->view->set('ac', $ac);
		$this->output('office/activity/edit');
		return true;
	}


}
