<?php
/**
 * voa_uda_frontend_thread_post_add
 * 统一数据访问/社区应用/发表评论(回复)
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_post_add extends voa_uda_frontend_thread_post_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {

		$this->_params = $in;
		// 提取用户提交的数据
		$fields = array(
			array('tid', self::VAR_INT, null, null, true),
			array('message', self::VAR_STR, array($this->_serv, 'chk_message'), null, true),
			array('pid', self::VAR_INT, null, null, true),
		    array('p_uid', self::VAR_INT, null, null, true),
		    array('p_username', self::VAR_STR, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}
		// 获取评论 id
		$ppid = empty($data['pid']) ? 0 : $data['pid'];
/* 		$post = array();
		// 检查当前用户是否有评论/回复的权限
		if (!$this->_chk_post_privilege($data['tid'], $ppid, $post)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::NO_PRIVILEGE);
			return false;
		} */

		// 回复信息入库
		$data['uid'] = startup_env::get('wbs_uid');
		$data['username'] = startup_env::get('wbs_username');
		$data['subject'] = '';
		$data['message'] = $data['message'];
		// 如果是对评论的回复
		if (0 < $ppid) {
			// 回复评论
			unset($data['pid']);
			$data['ppid'] = $ppid;
			$this->_add_reply($out, $data);
		} else {
           //评论操作
			$this->_add_comment($out, $data);
		}

		return true;
	}

	/**
	 * 新增回复
	 * @param array &$post 回复信息
	 * @param array $data 提交的数据
	 * @param array $ppost 评论信息
	 * @return boolean
	 */
	protected function _add_reply(&$post, $data) {

		// 回复信息入库
		$post = $this->_serv->insert($data);

		// 更新主题回复数
		$serv_t = &service::factory('voa_s_oa_thread');
		$serv_t->update_by_conds($data['tid'], array('`replies`=`replies`+?' => 1));

		return true;
	}

	/**
	 * 新增评论信息
	 * @param array &$post 返回的数据
	 * @param array $data 提交的数据
	 * @return boolean
	 */
	protected function _add_comment(&$post, $data) {

		// 新增评论
		$post = $this->_serv->insert($data);

		// 更新主题回复数
		$serv_t = &service::factory('voa_s_oa_thread');
		$serv_t->update_by_conds($data['tid'], array('`replies`=`replies`+?' => 1));

		return true;
	}

}
