<?php
/**
 * 新闻公告/评论
 * $Author$
 * $Id$
 */

class voa_s_oa_news_comment extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化数据列表
	 * @param array $list 列表（引用）
	 */
	public function format_list($list) {
		$result = array();
		if ($list) {
			//取得用户信息
			$m_uids = array_column($list, 'm_uid');
			$s_member = new voa_s_oa_member();
			$users = $s_member->fetch_all_by_conditions(array('m_uid IN' => $m_uids));
			voa_h_user::push($users);
			//格式化
			foreach ($list as $k => &$v) {
				$user = voa_h_user::get($v['m_uid']);
				$result[$k]['ncomm_id'] = $v['ncomm_id'];
				$result[$k]['created'] = rgmdate($v['created'], 'Y-m-d H:i:s');
				$result[$k]['_created'] = rgmdate($v['created'], 'u');
				$result[$k]['content'] = nl2br(rhtmlspecialchars($v['content']));
				$result[$k]['m_username'] = $user['m_username'];
				$result[$k]['avatar'] = voa_h_user::avatar($v['m_uid'], $user);
                $result[$k]['p_username'] = $v['p_username'];
			}
		}

		return $result;
	}

	/**
	 * 格式化一条数据
	 * @param array $comment 评论数据
	 */
	public function format_one($comment) {
		$result = array();
		//取得用户信息
		$s_member = new voa_s_oa_member();
		$user = $s_member->fetch_by_uid($comment['m_uid']);
		voa_h_user::push($user);
		//格式化
		$user = voa_h_user::get($comment['m_uid']);
		$result['created'] = rgmdate($comment['created'], 'Y-m-d H:i:s');
		$result['_created'] = rgmdate($comment['created'], 'u');
		$result['content'] = nl2br(rhtmlspecialchars($comment['content']));
		$result['m_username'] = rhtmlspecialchars($user['m_username']);
		$result['avatar'] = voa_h_user::avatar($comment['m_uid'], $user);
        $result['p_username'] = $comment['p_username'];



		return $result;
	}

	/**
	 * 验证公告ID
	 * @param int $ne_id
	 * @return boolean
	 */
	public function validator_ne_id($ne_id){
		if ($ne_id < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NE_ID_ERROR, $ne_id);
		}
		return true;
	}

	/**
	 * 验证公告ID
	 * @param int $ne_id
	 * @return boolean
	 */
	public function validator_content($ne_id){
		if ($ne_id == '') {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NE_ID_ERROR, $ne_id);
		}
		return true;
	}

	/**
	 * 验证公告ID
	 * @param int $ne_id
	 * @return boolean
	 */
	public function validator_uid($ne_id){
		if ($ne_id < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NE_ID_ERROR, $ne_id);
		}
		return true;
	}

}
