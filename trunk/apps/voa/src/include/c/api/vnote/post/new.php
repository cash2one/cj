<?php
/**
 * 新的备忘
 * $Author$
 * $Id$
 */

class voa_c_api_vnote_post_new extends voa_c_api_vnote_base {

	public function execute()
	{
		/*需要的参数*/
		$fields = array(
			'subject' => array('type' => 'string_trim', 'required' => false),	//备忘标题
			'message' => array('type' => 'string_trim', 'required' => true),	//备忘内容
		);
		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}
		/*审批标题检查*/
		if (empty($this->_params['message'])) {
			return $this->_set_errcode('message not null');
		}
		
		$uda = &uda::factory('voa_uda_frontend_vnote_insert');
		/** 备忘信息 */
		$vnote = array();
		/** 备忘详情信息 */
		$post = array();
		/** 抄送人信息 */
		$cculist = array();
		if (!$uda->vnote_new($vnote, $post, $cculist)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 更新草稿信息 */
		$this->_update_draft(array_keys($cculist));

		/** 把消息推入队列 */
		$this->_to_queue($vnote, $cculist);

		$this->_result = array(
			'vn_id' => $vnote['vn_id']
		);
		return true;
	}
}
