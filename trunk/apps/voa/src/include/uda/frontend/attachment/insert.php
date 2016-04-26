<?php
/**
 * 附件信息的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_attachment_insert extends voa_uda_frontend_attachment_base {

	/**
	 * 编辑器对象
	 * @var object
	 */
	public $ueditor;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 上传附件
	 * @param array $attachment 附件信息
	 * @param string $file 表单名称
	 * @param string $type 类型
	 *  + upload 正常文件上传
	 *  + base64 图片以base64的方式上传
	 *  + remote 远程图片
	 * @return boolean
	 */
	public function upload(&$attachment, $file, $type='upload') {

		$current_config = array();
		// 允许上传的附件类型
		$current_config['allow_files'] = config::get(startup_env::get('app_name').'.attachment.file_type');
		// 储存附件的文件名格式
		$current_config['file_name_format'] = 'auto';
		// 允许上传的文件最大尺寸
		$current_config['max_size'] = config::get(startup_env::get('app_name').'.attachment.max_size');
		/** 源文件名 */
		$current_config['source_name'] = isset($_POST['fileName']) ? $_POST['fileName'] : '';
		// 储存根目录
		if (preg_match('/\.pem$/i', $current_config['source_name'])) {
			$current_config['save_dir_path'] = voa_h_func::get_pemdir(startup_env::get('domain'));
		} else {
			$current_config['save_dir_path'] = voa_h_func::get_attachdir(startup_env::get('domain'));
		}

		$up = new upload($file, $current_config, $type);
		$att = $up->get_file_info();
		//文件类型（附件）
		$att['at_isattach'] = $this->_request->get('is_attach');

		if (empty($att['error']) || $att['error'] != 'SUCCESS') {
			$this->errmsg(100, empty($att['error']) ? '上传文件发生未知错误' : $att['error']);
			return false;
		}

		$serv = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
		try {
			$serv->begin();

			$attachment = array();
			$this->_attachment_data($att, $attachment);

			$at_id = $serv->insert($attachment, true);
			$attachment['at_id'] = $at_id;

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			//如果 $id 值为空, 则说明入库操作失败
			$this->errmsg(150, 'vnote_new_failed');
			return false;
		}

		return true;
	}

	/**
	 * 编辑器文件上传
	 * @param string $field_name 表单控件名
	 * @param array $config 配置信息
	 * @param string $upload_action_type 上传动作名
	 * @param string $attach_view_url_base 附件访问根路径url
	 * @param string &$editor_upload_result 上传结果
	 * @return boolean
	 */
	public function editor_upload($field_name, $config, $upload_save_dir_root, $upload_action_type, &$editor_upload_result) {

		$this->ueditor = new ueditor();
		$this->ueditor->upload_config = $config;
		$this->ueditor->upload_save_dir_root = $upload_save_dir_root;
		if (!$this->ueditor->uploader($field_name, $upload_action_type)) {
			$this->errmsg(9404, $this->ueditor->upload_error);
			$editor_upload_result = $this->ueditor->upload_error;
			return false;
		}

		$upload_result = $this->ueditor->upload_result;

		if ($upload_action_type == 'catchimage') {
			return $this->_editor_upload_catchimage($upload_action_type, $upload_result, $editor_upload_result);
		} else {
			return $this->_editor_upload_file($upload_action_type, $upload_result, $editor_upload_result);
		}
	}

	/**
	 * 编辑器上传文件入库并输出结果
	 * @param string $upload_type
	 * @param array $upload_file_info
	 * @param string $attach_view_url_base
	 * @param string $editor_upload_result
	 * @return boolean
	 */
	private function _editor_upload_file($upload_type, $upload_file_info, &$editor_upload_result) {

		$serv = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));

		try {
			$serv->begin();

			$attachment = array();
			$this->_attachment_data($upload_file_info, $attachment);
			$at_id = $serv->insert($attachment, true);
			$attach_view_url = voa_h_attach::attachment_url($at_id);

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(9403, '上传文件入库发生错误');
			$editor_upload_result = '上传文件入库发生错误';

			// 自文件系统移除上传的文件
			if (!empty($upload_file_info['file_path'])) {
				@unlink($upload_file_info['file_path']);
			}
			return false;
		}

		$editor_upload_result = $this->ueditor->upload_result_to_ueditor($upload_type, $upload_file_info, $attach_view_url, $at_id);

		return true;
	}

	/**
	 * 远程获取的图片信息入库并输出上传结果给编辑器前端
	 * @param string $upload_type
	 * @param array $attachments
	 * @param string $attach_view_url_base
	 * @param string &$editor_upload_result
	 * @return boolean
	 */
	private function _editor_upload_catchimage($upload_type, $attachments, &$editor_upload_result) {

		$serv = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));

		$attach_view_urls = array();
		$attach_ids = array();

		try {
			$serv->begin();

			foreach ($attachments as $k => $fileinfo) {
				$attachment = array();
				$this->_attachment_data($fileinfo, $attachment);
				$at_id = $serv->insert($attachment, true);
				$attach_view_urls[$k] = voa_h_attach::attachment_url($at_id);
				$attach_ids[$k] = $at_id;
			}

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(9403, '远程图片入库发生错误');
			$editor_upload_result = '远程图片入库发生错误';

			// 自文件系统移除上传的文件
			foreach ($attachments as $k => $fileinfo) {
				@unlink($fileinfo['file_path']);
			}
			return false;
		}

		$editor_upload_result = $this->ueditor->upload_result_to_ueditor($upload_type, $attachments, $attach_view_urls, $attach_ids);

		return true;
	}

	/**
	 * 格式化录入附件表需要的数据
	 * @param array $fileinfo
	 * @return array
	 */
	private function _attachment_data($fileinfo, &$attachment) {
		$attachment = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'at_filename' => $fileinfo['source_name'],
				'at_filesize' => $fileinfo['file_size'],
				'at_attachment' => $fileinfo['save_path'],
				'at_remote' => 0,
				'at_description' => '',
				'at_isimage' => $fileinfo['is_image'] ? 1 : 0,
				'at_isattach' => $fileinfo['at_isattach'] ? 1 : 0,
				'at_width' => $fileinfo['width'],
				'at_thumb' => empty($fileinfo['thumb']) ? 0 : 1
		);

		return true;
	}
}
