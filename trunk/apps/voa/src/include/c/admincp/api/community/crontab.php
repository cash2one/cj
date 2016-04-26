<?php
/**
 * 预览话题
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/23
 * Time: 15:15
 */
class voa_c_admincp_api_community_crontab extends voa_c_admincp_api_community_base {

	public function execute() {

		$community = array();

		$uda_add = &uda::factory('voa_uda_frontend_association_powersetting');
		$post = $this->request->getx();

		if(!$uda_add->update_crontab($post, $community)) {
			return $this->_admincp_error_message(voa_errcode_oa_community::DRAFT);
		}
		if($post['crontab'] == 1) {

		}else{

		}
		// 返回结果
		$result = array(
			'code' => $community,// 附件id
		);
		return $this->_output_result($result);
	}

}
