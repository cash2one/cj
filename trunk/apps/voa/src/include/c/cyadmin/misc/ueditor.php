<?php
/**
 * voa_c_cyadmin_misc_ueditor
 * 企业后台/【公用】/上传业务处理
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_misc_ueditor extends voa_c_cyadmin_base {
	protected $_ueditor_config = array();
	protected $_ueditor;
	protected $_action;
	public function execute() {

		// 获取动作类型
		$action = $this->request->get('action');

		// 获取附件储存根目录
		$upload_save_dir_root = voa_h_func::get_attachdir(startup_env::get('domain'));
		// 编辑器上传配置参数
		$upload_config = config::get(startup_env::get('app_name') . '.ueditor.ueditor');
		// 使用公共的附件容量覆盖配置
		$keys = array(
			'imageMaxSize',
			'scrawlMaxSize',
			'catcherMaxSize',
			'videoMaxSize',
			'fileMaxSize'
		);
		foreach ( $keys as $_key ) {
			if ($upload_config[$_key] <= 0 || empty($upload_config[$_key])) {
				$upload_config[$_key] = config::get(startup_env::get('app_name') . '.attachment.max_size');
			}
		}

		// 引入编辑器类
		$ueditor = new ueditor();

		if ($action == 'config') {
			/**
			 * 获取上传配置信息
			 */

			$ueditor->upload_save_dir_root = $upload_save_dir_root;
			$ueditor->upload_config = $upload_config;
			$output = '';
			if (! $ueditor->get_upload_config($output)) {
				$output = $ueditor->upload_error_to_ueditor($ueditor->upload_error);
			}
		} else {
			/**
			 * 上传动作
			 */

			// 映射上传类型与配置的表单字段名关系，见/config/ueditor.php
			$field_name_keys = array(
				'catchimage' => 'catcherFieldName',
				'uploadimage' => 'imageFieldName',
				'uploadscrawl' => 'scrawlFieldName',
				'uploadvideo' => 'videoFieldName',
				'uploadfile' => 'fileFieldName'
			);

			// 当前动作的表单名
			$field_name = isset($field_name_keys[$action]) ? $field_name_keys[$action] : $field_name_keys['uploadfile'];
			$field_name_key = $upload_config[$field_name];

			// uda 处理上传业务
			/*
			 * $uda_upload = &uda::factory('voa_uda_frontend_attachment_insert');
			 * $output = '';
			 * if (!$uda_upload->editor_upload($field_name_key, $upload_config, $upload_save_dir_root, $action, $output)) {
			 * $ueditor->upload_error_to_ueditor($output);
			 * //header("Content-Type: text/html; charset=utf-8");
			 * $output = $ueditor->upload_error;
			 * }
			 */
			// 实例化附件处理类
			$config = array(
				'save_dir_path' => APP_PATH . config::get(startup_env::get('app_name') . '.cyadmin.dir'),

				// 'save_dir_path' =>voa_h_func::get_attachdir(startup_env::get('domain')),
				'allow_files' => array(
					'png',
					'jpg',
					'jpeg',
					'gif',
					'bmp'
				)
			);

			if (! file_exists($config['save_dir_path'])) {
				mkdir($config['save_dir_path'], 0777);
			}
			$upload = new upload($field_name_key, $config);
			$imginfo = $upload->get_file_info();
			$img = array();
			$img['atattachment'] = $imginfo['save_path'];
			$img['atname'] = $imginfo['file_name'];

			if ($imginfo['error_code'] != 0) {
				$output = array(
					"state" => $imginfo['error']
				);
			} else {
				// 把图片路径存到附件表中
				$serv_at = &service::factory('voa_s_cyadmin_attachment');
				$tmp = $serv_at->insert($img);
				$output = array(
					"state" => $imginfo['error'],
					"url" => $this->_get_img_url($tmp['atid']),
					"title" => $imginfo['source_name'],
					"original" => $imginfo['source_name'],
					"type" => $imginfo['file_type'],
					"size" => $imginfo['file_size'],
					"attach_id" => $tmp['atid']
				);
			}
			$output = rjson_encode($output);
		}

		echo $output;
		return $this->response->stop();
	}
}
