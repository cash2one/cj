<?php
/**
 * Comment.class.php
 * @create-time: 2015-07-01
 */
namespace File\Controller\Api;

class CommentController extends AbstractController {

	/**
	 * 文件评论列表
	 * @return array
	 */
	public function Comment_list_get($plugin_id = 0) {

		if ($plugin_id == 0) {
			$plugin_id = $this->_plugin->get_pluginid();
		}
		// 传入参数
		$params = array(
			'plugin_id' => $plugin_id,
			'obj_id'=>I('get.file_id')
		);

		// 调用公共组件方法
		$result = R('PubApi/Api/Comment/List_get', array($params));

		// 取评论用户id集合
		$m_uids = array();
		foreach ($result['data'] as &$_t) {
			array_push($m_uids, $_t['m_uid']);
		}

		// 用户头像列表
		$serv_m = D('Common/Member', 'Service');
		$list_face = array();
		if (!empty($m_uids)) {
			// 取用户头像列表
			$list_face = $serv_m->list_by_pks($m_uids);
		}

		// 获取评论@用户m_uid集合
		$str_ids = "";
		foreach ($result['data'] as &$_tt) {
			$str_ids .= "," . $_tt['reply_m_uid'];
		}
		$info_ids = array_unique(array_filter(explode(',', $str_ids)));

		// 获取所有@用户信息
		$list_info = array();
		if (!empty($info_ids)) {
			// 获取@用户信息列表
			$list_info = $serv_m->list_by_pks($info_ids);
			$this->__format_comment_user($list_info);
		}

		// 拼接用户信息
		$serv_fmt = D('File/Format', 'Service');
		foreach ($result['data'] as &$_v) {

			$reply_m_infos = array();
			// 根据键值取相应数据
			$face_info = $this->_seekarr($list_face, 'm_uid', $_v['m_uid']);
			// 拼接评论用户头像信息
			$_v['m_face'] = $face_info['m_face'];

			// 回复评论Id为空时，移除回复相关字段
			if ($_v['reply_id'] == 0) {
				unset($_v['reply_id'], $_v['reply_m_uid']);
			} else {
				// 拼接@用户信息
				foreach (explode(',', $_v['reply_m_uid']) as $uid) {
					array_push($reply_m_infos, $this->_seekarr($list_info, 'reply_member_uid', $uid));
				}
			}

			// 数据格式化
			$serv_fmt->comment($_v);
			if (!empty($reply_m_infos)) {
				$_v['reply_users'] = $reply_m_infos;
			}
		}

		// 返回数据
		$this->_result = $result;

		return $result;
	}

	/**
	 * 文件评论
	 * @return bool
	 */
	public function Comment_create_post() {

		// 传入参数
		$params = I('request.');
		$params['plugin_id'] = $this->_plugin->get_pluginid();
		$params['obj_id'] = (int)$params['file_id'];

		$params['reply_id'] = (int)$params['reply_c_id'];
		$params['reply_m_uid'] = $params['reply_member_uid'];
		unset($params['file_id'],$params['reply_c_id'],$params['reply_member_uid']);

		// 扩展参数
		$extend = array (
			'm_uid'      => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username']
		);

		// 调用公共组件方法
		$result = R('PubApi/Api/Comment/Create_post', array($params, $extend));

		// 数据格式化
		$serv_fmt = D('File/Format', 'Service');

		// 取评论用户id集合
		$m_uids = array ();
		if(!empty($result['reply_m_uid'])){
			$m_uids = explode(",", $result['reply_m_uid']);
		}
		array_push($m_uids, $result['m_uid']);

		$serv_m = D('Common/Member', 'Service');
		// 用户头像
		$list_face = array ();
		if (!empty($m_uids)) {
			// 取用户头像列表
			$list_face = $serv_m->list_by_pks($m_uids);
		}
		// 拼接评论用户头像
		$att_info = $this->_seekarr($list_face, 'm_uid', $result['m_uid']);
		$result['m_face'] = $att_info['m_face'];

		// 回复评论Id为空时，移除回复相关字段
		if ($result['reply_id'] == 0) {
			unset($result['reply_id'], $result['reply_m_uid']);
		} else {
			$reply_m_infos = $serv_m->list_by_pks(explode(",", $result['reply_m_uid']));
			$this->__format_comment_user($reply_m_infos);
		}

		// 数据格式化
		$serv_fmt->comment($result);
		if (!empty($reply_m_infos)) {
			$result['reply_users'] = $reply_m_infos;
		}

		// 返回数据
		$this->_result = $result;
		return true;
	}

	/**
	 * 用户数据格式化
	 * @param array $members 待格式化数据
	 * @return bool
	 */
	private function __format_comment_user(&$members) {

		$serv_fmt = D('File/Format', 'Service');
		$format_members = array ();
		foreach ($members as &$_v) {
			// 数据格式化
			$serv_fmt->member($_v);
			array_push($format_members, $_v);
		}

		$members = $format_members;
		return true;
	}
}
