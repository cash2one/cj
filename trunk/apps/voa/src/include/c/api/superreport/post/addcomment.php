<?php
/**
 * voa_c_api_superreport_post_addcomment
 * 增加超级报表评论
 * $Author$
 * $Id$
 */

class voa_c_api_superreport_post_addcomment extends voa_c_api_superreport_abstract {

	public function execute() {

		// 请求的参数
		$fields = array(
			// 日报ID
			'dr_id' => array('type' => 'int', 'required' => true),
			// 评论内容
			'comment' => array('type' => 'string', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 获取数据
		$result = array();
		$uda = &uda::factory('voa_uda_frontend_superreport_addcomment', $this->_ptname);
		$uda->member = $this->_member;
		if (!$uda->add_comment($this->_params,  $result)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = array(
			'sc_id' => $result['sc_id'],
			'dr_id' => $result['dr_id']
		);

		return true;
	}

}

