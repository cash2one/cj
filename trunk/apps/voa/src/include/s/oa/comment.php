<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/21
 * Time: 12:01
 */

class voa_s_oa_comment extends voa_s_abstract {

	protected $_d_class = null;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_comment();
		}
	}

	/**
	 * 根据主键获取信息
	 * @param $id
	 * @return Ambigous
	 * @throws service_exception
	 */
	public function del_by_tid($tid) {

		$result = $this->_d_class->list_by_conds(array('obj_id' => $tid));
		if (!empty($result)) {
			$ids = array_column($result, 'obj_id');
			$this->_d_class->delete_by_conds(array('obj_id' => $ids, 'cp_identifier' => 'community'));
		}

		return true;
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

				$result[$k]['created'] = rgmdate($v['created'], 'Y-m-d H:i:s');
				$result[$k]['_created'] = rgmdate($v['created'], 'u');
				$result[$k]['content'] = nl2br(rhtmlspecialchars($v['content']));
				$result[$k]['m_username'] = $user['m_username'];
				$result[$k]['avatar'] = voa_h_user::avatar($v['m_uid'], $user);
			}
		}

		return $result;
	}
}
