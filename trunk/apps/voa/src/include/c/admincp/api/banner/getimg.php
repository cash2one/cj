<?php
/**
 * 内容详情
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/23
 * Time: 15:15
 */
class voa_c_admincp_api_banner_getimg extends voa_c_admincp_api_banner_base {

	public function execute() {

		$converid = (int)$this->request->post('thumb');

		if (empty($converid) || !is_int($converid)) {
			return $this->_admincp_error_message(voa_errcode_oa_community::DRAFT);
		}

		$converurl = voa_h_attach::attachment_url($converid, 0);

		 //返回结果
		$result = array(
			'converurl' => $converurl,// 附件urls
		);
		return $this->_output_result($result);
	}
}
