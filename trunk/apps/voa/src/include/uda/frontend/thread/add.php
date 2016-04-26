<?php
/**
 * voa_uda_frontend_thread_add
 * 统一数据访问/社区应用/发表帖子
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_add extends voa_uda_frontend_thread_abstract {

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
		// 查询表格的条件
		$fields = array(
		    array('uid',self::VAR_INT,null,null,false),
		    array('username',self::VAR_STR,null,null,false),
		    array('at_ids',self::VAR_STR,null,null,false),
			array('subject', self::VAR_STR, array($this->_serv, 'chk_subject'), null, false),
			array('message', self::VAR_STR, array($this->_serv, 'chk_message'), null, false),
			array('uids', self::VAR_STR, array($this->_serv, 'chk_uids'), null, true)
		);

		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}


		// 判断是否指定了用户
		$friend = empty($data['uids']) ? voa_d_oa_thread::FRIEND_ALL : voa_d_oa_thread::FRIEND_SOME;

		$serv_p = &service::factory('voa_s_oa_thread_post');
		$serv_pu = &service::factory('voa_s_oa_thread_permit_user');

		// 主题信息入库
		$newthread = array(
		    'uid' => $data['uid'],
		    'username' =>$data['username'],
			'subject' => $data['subject'],
		    'attach_id'=>$data['at_ids'],
			'friend' => $friend,
			'remindtime' => 0,
			'replies' => 0,
		    'likes' => 0,
		    'displayorder'=>0

		);

		$out = $this->_serv->insert($newthread);

		// 内容信息入库
		$newpost = array(
			'tid' => $out['tid'],
		    'uid' => $data['uid'],
		    'username' =>$data['username'],
			'subject' => $data['subject'],
			'message' => $data['message'],
			'first' => voa_d_oa_thread_post::FIRST_YES,
		    'p_uid' => 0,
		    'p_username' => ''
		);
		$serv_p->insert($newpost);

/* 		// 权限表(允许查看用户)
		if (voa_d_oa_thread::FRIEND_ALL != $friend) {
			$uids = explode(",", $data['uids']);
			// 加入自己
			$uids[] = startup_env::get('wbs_uid');
			// 根据 uids 查出所有的用户
			$serv_m = &service::factory('voa_s_oa_member');
			$members = $serv_m->fetch_all_by_ids($uids);

			foreach ($members as $m) {
				$pu = array(
					'tid' => $out['tid'],
					'uid' => $m['m_uid'],
					'username' => $m['m_username'],
				);
				$serv_pu->insert($pu);
			}
		} */

		return true;
	}

}
