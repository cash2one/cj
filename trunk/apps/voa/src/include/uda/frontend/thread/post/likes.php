<?php
/**
 * voa_uda_frontend_thread_post_likes
 * 统一数据访问/社区应用/点赞操作
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_post_likes extends voa_uda_frontend_thread_post_abstract {

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
			array('tid', self::VAR_INT, null, null, false),
			'pid' => array('ppid', self::VAR_INT, null, null, false)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 获取评论 id
		$ppid = empty($data['ppid']) ? 0 : $data['ppid'];
		$post = array();
		// 检查当前用户是否有评论/回复的权限
		if (!$this->_chk_post_privilege($data['tid'], $ppid, $post)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::NO_PRIVILEGE);
			return false;
		}

		// 赞 +1
		$this->_serv->update_by_conds($conds['ppid'], array('`likes`=`likes`+?' => 1));

		return true;
	}

}
