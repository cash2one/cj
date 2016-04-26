<?php
/**
 * 新闻公告/点赞
 * $Author ppker
 * $Id$
 */

class voa_s_oa_news_like extends voa_s_abstract {

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
	/*public function format_list($list) {
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
			}
		}

		return $result;
	}*/

	/**
	 * 格式化一条数据,用于后台列表显示
	 * @param array $like 点赞数据记录
	 */
	public function format_one($like) {
		$result = array();
		//取得用户信息
		$s_member = new voa_s_oa_member();
		$user = $s_member->fetch_by_uid($like['m_uid']);
		voa_h_user::push($user);
		//格式化
		$user = voa_h_user::get($like['m_uid']);
		$result['created'] = rgmdate($like['created'], 'Y-m-d H:i:s');
		// $result['_created'] = rgmdate($like['created'], 'u');
		$result['description'] = $like['description'] == 1 ? "次数+1" : $like['description'] == 2 ? "次数-1" : "";
		$result['m_username'] = rhtmlspecialchars($user['m_username']);
		$result['avatar'] = voa_h_user::avatar($like['m_uid'], $user);
		$result['ip'] = trim($like['ip']);
		$result['num_like'] = intval($like['num_like']);
		// 后面要转化成新闻标题
		$result['ne_id'] = $like['ne_id'];

		return $result;
	}

	/**
	 * 验证IP
	 */
	public function validator_ip($ip){
		if (!validator::is_ip($ip)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::ERR_LIKE_IP, $ip);
		}
		return true;
	}
	/**
	 * 验证m_uid
	 */
	public function validator_uid($m_uid){
		if ($m_uid < 0) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::ERR_LIKE_UID, $m_uid);
		}
		return true;
	}

	/**
	 * 验证ne_id
	 */
	public function validator_neid($ne_id){

		if ($ne_id == '' || $ne_id < 1 ){
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::ERR_LIKE_NEID, $ne_id);
		}
		return true;
	}


}
