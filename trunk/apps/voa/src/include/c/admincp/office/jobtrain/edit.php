<?php
/**
* voa_c_admincp_office_jobtrain_add
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_edit extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		$uda_cata = &uda::factory('voa_uda_frontend_jobtrain_category');
		$uda = &uda::factory('voa_uda_frontend_jobtrain_article');
		$id = $this->request->get('id');
		if ($this->_is_post()) {
			$data = $this->request->postx();
			$article = array();
			try {
				$args = array(
					'id' => $data['id']
				);
				$uda->save_article($data, $args, $article);
				// 是否发布
				$is_publish = $article['is_publish'];
				// 是否推送
				$is_push = isset($data['is_push']) ? $data['is_push'] : 0;
				if($is_publish){
					if($is_push){
						$this->_to_queue($article);
					}
					$this->message('success', '发布内容成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
				}else{
					if (!empty($article['is_preview'])) {
						$this->_to_queue($article);
						$this->message('success', '预览已发送成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
						die;
					}
					$this->message('success', '保存草稿成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
				}
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
		}
		// 读取内容
		$result = $uda->get_article($id);

		// 初始化编辑器
		$ueditor = new ueditor();
		$content_key = 'content';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = config::get(startup_env::get('app_name') . '.ueditor.ueditor_home_url');
		// 处理上传文件路径
		$ueditor->server_url = '/admincp/ueditor/';
		$ueditor->ueditor_config = array('toolbars' => '_jobtrain', 'textarea' => $content_key, 'initialFrameHeight' => 300, 'initialContent' => isset($result['content']) ? $result['content'] : '', 'elementPathEnabled' => false, 'autoHeightEnabled' => false);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}
		
		$this->view->set('secret_id', config::get('voa.jobtrain.secret_id')); // 设置视频 secret_id
		$this->view->set('app_id', config::get('voa.jobtrain.app_id'));
		$this->view->set('domain', $this->_setting['domain']); // 设置域名
		$this->view->set('result', $result);
		$this->view->set('catas', $uda_cata->list_cata(true, array('is_open'=>1)) );
		$this->view->set('ueditor_output', $ueditor_output);
		$this->view->set('types', voa_d_oa_jobtrain_article::$TYPES);
		$this->view->set('attach_upload_url', $this->cpurl($this->_module, $this->_operation, 'attachupload', $this->_module_plugin_id));
		$this->output('office/jobtrain/add');
	}

}