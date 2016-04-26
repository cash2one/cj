<?php
/**
 * 附件上传
 * $Author$
 * $Id$
 */

class voa_c_frontend_attachment_upload extends voa_c_frontend_attachment_base {

	public function execute() {
		$file_field = 'Filedata';
		$type = 'upload';
		// 压缩模式（安卓+非wifi）
		if ($this->request->post('base64Data')) {
			$file_field = 'base64Data';
			$type = 'base64';
			// 图片新名称
			$_POST['fileName'] = empty($_POST['fileName']) ? random(16) : $_POST['fileName'];
			$_POST['base64Data'] = substr($this->request->post('base64Data'), strpos($this->request->post('base64Data'), ",") + 1);
		}

		$uda = &uda::factory('voa_uda_frontend_attachment_insert');
		$attachment = array();
		if (!$uda->upload($attachment, $file_field, $type)) {
			$this->_json_message(array('resultCode' => 1, 'describe' => $uda->error));
			return false;
		}

		$result = array(
			"resultCode"=> 0,
			"describe"=> '',
			"id"=> $attachment['at_id'],
			"data"=> array(
				"photo"=> voa_h_attach::attachment_url($attachment['at_id'], 640),
				"thumb"=> voa_h_attach::attachment_url($attachment['at_id'], 45)
			)
		);

		$this->_json_message($result);
	}
}
