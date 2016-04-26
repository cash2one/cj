<?php
/**
 * 预览话题
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/23
 * Time: 15:15
 */
class voa_c_admincp_api_community_draft extends voa_c_admincp_api_community_base {

	public function execute() {

		$community = array();

		$uda_add = &uda::factory('voa_uda_frontend_community_add');
		$post = $this->request->getx();

		if(!$uda_add->execute($post, $community)) {
			return $this->_admincp_error_message(voa_errcode_oa_community::DRAFT);
		}

		$uids = explode(',', $post['users']);
		$uids = array_filter($uids);

		// 获取agentid
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		if(isset($plugins[$this->_p_sets['pluginid']]['cp_agentid'])){
			startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);
		}
		$uda_add->send_msg($community, 'draft', $uids, $this->session);
		// 返回结果
		$result = array(
			'id' => (int)$community['cid'],// 附件id
		);
		return $this->_output_result($result);
	}

}
