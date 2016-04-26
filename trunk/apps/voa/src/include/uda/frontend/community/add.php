<?php

/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/20
 * Time: 14:03
 */
class voa_uda_frontend_community_add extends voa_uda_frontend_community_abstract {

	protected $_serv;

	public function __construct() {

		parent::__construct();
		if ($this->_serv == null) {
			$this->_serv = new voa_s_oa_community();
		}
	}

	public function execute($request, &$result) {

		$this->_params = $request;

		// 查询表格的条件
		$fields = array(
			array('cid', self::VAR_INT, null, null, false),
			array('uid', self::VAR_INT, null, null, false),
			array('tid', self::VAR_INT, null, null, false),
			array('draft', self::VAR_INT, null, null, false),
			array('black', self::VAR_INT, null, null, false),
			array('subject', self::VAR_STR, array($this->_serv, 'chk_subject'), null, false),
			array('message', self::VAR_STR, array($this->_serv, 'chk_message'), null, false)
		);

		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}
		//提取图片id
		$data['attach_id'] = '';
		$this->_get_img_id($data['message'], $data['attach_id']);

		if(!empty($data['cid'])) {
			$this->draft_update($data, $result);

			return true;
		}
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch($data['uid']);
		$data['username'] = $users['m_username'];
		$serv_p = &service::factory('voa_s_oa_community_theme');

		// 主题信息入库
		$newthread = array(
			'uid' => $data['uid'],
			'username' => $data['username'],
			'subject' => $data['subject'],
			'tid' => $data['tid'], //暂时使用
			'draft' => $data['draft'],
			'replies' => 0,
			'likes' => 0,
			'browses' => 0,
			'is_all' => 1,
			'attach_id' => $data['attach_id'],
			'black' => 1,
		);

		if (isset($request['outsider'])) {
			$newthread['outsider'] = 1;
		}

		$out = $this->_serv->insert($newthread);
		if($data['uid']) {
			$this->__add_dynamic($newthread, $out);
		}

		// 内容信息入库
		$newpost = array(
			'cid' => $out['cid'],
			'message' => $data['message'],
		);
		$result = $serv_p->insert($newpost);
		$this->__add_dynamic($newthread, $out['cid']);

		$result['subject'] = $data['subject'];
		return true;
	}

	/**
	 * 编辑
	 * @param $request
	 * @param $result
	 * @return bool
	 */
	public function draft_update($request, &$result) {

		$serv_p = &service::factory('voa_s_oa_community_theme');

		$request['username'] = '';
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch($request['uid']);
		$request['username'] = $users['m_username'];
		// 主题信息入库
		$newthread = array(
			'uid' => $request['uid'],
			'username' => $request['username'],
			'subject' => $request['subject'],
			'tid' => $request['tid'], //暂时使用
			'draft' => $request['draft'],
			'black' => $request['black'],
			'attach_id' => $request['attach_id']
		);
		$this->_serv->update($request['cid'], $newthread);
		if($request['uid']) {
			$this->__add_dynamic($newthread, $request['cid']);
		}
		// 内容信息入库
		$newpost = array(
			'message' => $request['message'],
		);
		$result_data = $serv_p->update_by_conds(array('cid' => $request['cid']), $newpost);
		if ( $result_data ) {
			$result = $newthread;
			$result['cid'] = $request['cid'];
			$result['message'] = $request['message'];
		}
		return true;
	}

	/**
	 * 入库操作
	 * @param $data
	 * @throws service_exception
	 */
	private function __add_dynamic($data, $cid) {

		// 只有当正式提交动作才能入库
		if ($data['draft'] == 1){

			$setting = voa_h_cache::get_instance()->get('plugin.community.setting', 'oa');
			$dynamic = new voa_d_oa_common_dynamic();
			// 入库数据
			$dynamic_data = array(
				'obj_id' => $cid,
				'cp_identifier' => 'community',
				'm_uid' => $data['uid'],
				'm_username' => $data['username'],
				'dynamic' => 4,
			);

			$detail = $dynamic->get_by_conds($dynamic_data);
			if (empty($detail)) {
				$dynamic_data['score']  = $setting['add_forum'];
				$dynamic->insert($dynamic_data);
			}
		}

	}

	/**
	 * 提取图片id
	 * @param $request
	 * @param $result
	 * @return bool
	 */
	protected function _get_img_id($request, &$result) {

		$result = '';
		$pattern = '/<img.+src=\"?.+\/attachment\/read\/(\d+?)\"?.+\/>/iU';
		preg_match_all($pattern, $request, $img_id);
		if ($img_id[1]) {
			$result = implode(',', $img_id[1]);
		}

		return true;
	}

}
