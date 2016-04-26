<?php
/**
 * upload.php
 * 后台API/附件上传接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_admincp_api_attachment_upload extends voa_c_admincp_api_attachment_base {

	public function execute() {

		// 上传文件表单名
		$input_name = (string)$this->request->get('file');
		// 缩略图尺寸
		$thumb_size = (int)$this->request->get('thumbsize');

		// 上传类型
		$upload_type = 'upload';
		if (empty($_FILES[$input_name])) {
			return $this->_admincp_error_message(voa_errcode_api_attachment::UPLOAD_DATA_EMPTY);
		}
		if (empty($_FILES[$input_name]['tmp_name'])) {
			return $this->_admincp_error_message(voa_errcode_api_attachment::UPLOAD_DATA_NULL);
		}
		$_POST['fileName'] = $_FILES[$input_name]['name'];

		// 处理上传并写入附件
		$uda = &uda::factory('voa_uda_frontend_attachment_insert');
		$attachment = array();
		if (!$uda->upload($attachment, $input_name, $upload_type)) {
			return $this->_admincp_error_message(voa_errcode_api_attachment::UPLOAD_UDA_ERROR, $uda->error);
		}

		$attach_list = array(

			0 => array(
				'name' => $attachment['at_filename'],
				'size' => $attachment['at_filesize'],
				'type' => $_FILES[$input_name]['type'],
				'url' => voa_h_attach::attachment_url($attachment['at_id'], 0),
				'thumbnailUrl' => $thumb_size > 0 ? voa_h_attach::attachment_url($attachment['at_id'], $thumb_size) : '',
				'deleteUrl' => '',
				'deleteType' => 'POST'
			)

		);

		// 返回结果
		$result = array(
			'id' => (int)$attachment['at_id'],// 附件id
			$input_name => $attach_list,
			'list' => $attach_list
		);

		return $this->_output_result($result);
	}

}
