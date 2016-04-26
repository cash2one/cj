<?php
/**
 * voa_c_admincp_misc_ueditor
 * 企业后台/【公用】/上传业务处理
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_misc_ueditor extends voa_c_admincp_base {

	protected $_ueditor_config = array();
	protected $_ueditor;
	protected $_action;

	public function execute() {

		// 获取动作类型
		$action = $this->request->get('action');

		// 获取附件储存根目录
		$upload_save_dir_root = voa_h_func::get_attachdir(startup_env::get('domain'));
		// 编辑器上传配置参数
		$upload_config = config::get(startup_env::get('app_name').'.ueditor.ueditor');
		// 使用公共的附件容量覆盖配置
		$keys = array(
			'imageMaxSize', 'scrawlMaxSize', 'catcherMaxSize', 'videoMaxSize', 'fileMaxSize'
		);
		foreach ($keys as $_key) {
			if ($upload_config[$_key] <= 0 || empty($upload_config[$_key])) {
				$upload_config[$_key] = config::get(startup_env::get('app_name').'.attachment.max_size');
			}
		}

		// 引入编辑器类
		$ueditor = new ueditor();

		if ($action == 'config') {
			/** 获取上传配置信息 */

			$ueditor->upload_save_dir_root = $upload_save_dir_root;
			$ueditor->upload_config = $upload_config;
			$output = '';
			if (!$ueditor->get_upload_config($output)) {
				$output = $ueditor->upload_error_to_ueditor($ueditor->upload_error);
			}

		} else {
			/** 上传动作 */

			// 映射上传类型与配置的表单字段名关系，见/config/ueditor.php
			$field_name_keys = array(
					'catchimage' => 'catcherFieldName',
					'uploadimage' => 'imageFieldName',
					'uploadscrawl' => 'scrawlFieldName',
					'uploadvideo' => 'videoFieldName',
					'uploadfile' => 'fileFieldName',
			);

			// 当前动作的表单名
			$field_name = isset($field_name_keys[$action]) ? $field_name_keys[$action] : $field_name_keys['uploadfile'];
			$field_name_key = $upload_config[$field_name];

			// uda 处理上传业务
			$uda_upload = &uda::factory('voa_uda_frontend_attachment_insert');
			$output = '';
			if (!$uda_upload->editor_upload($field_name_key, $upload_config, $upload_save_dir_root, $action, $output)) {
				$ueditor->upload_error_to_ueditor($output);
				//header("Content-Type: text/html; charset=utf-8");
				$output = $ueditor->upload_error;
			}
		}

		$this->view->set('output', $output);
		$this->output('misc/ueditor_output');
	}
}
