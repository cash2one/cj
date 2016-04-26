<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/21
 * Time: 17:39
 */

class voa_uda_frontend_community_delete extends voa_uda_frontend_community_abstract {

	protected $_serv = null;
	protected $_browses = null;
	protected $_house = null;
	protected $_likes = null;
	protected $_comment = null;

	public function __construct() {
		parent::__construct();
		if($this->_serv == null) {
			$this->_serv = new voa_s_oa_community();
			$this->_browses = new voa_s_oa_community_browses();
			$this->_dynamic = new voa_s_oa_common_dynamic();
			$this->_likes = new voa_s_oa_community_likes();
			$this->_comment = new voa_s_oa_comment();
		}
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
			array('tid', self::VAR_ARR, null, null, false)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 删除信息
		$this->_serv->delete($data['tid']);
		// 删除主题的评论信息
		$this->_comment->del_by_tid($data['tid']);
		//删除浏览
		$this->_browses->del_by_tid($data['tid']);
		//删除收藏
		$this->_dynamic->del_by_tid($data['tid']);
		//删除点赞
		$this->_likes->del_by_tid($data['tid']);


		$dynamic = new voa_d_oa_common_dynamic();
		// 删除动态数据
		foreach ($data['tid'] as $v) {
			$delet_conds[] = array(
				'obj_id' => $v,
			);
		}
		$conds = array();
		$conds['obj_id'] =$delet_conds;
		$conds['cp_identifier'] = 'community';
		$dynamic->delete_by_conds($conds);
		return true;
	}

	/**
	 * 删除指定分类的所有话题
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function del_by_tid($tid) {

		$this->_serv->del_by_tid($tid);
		return true;
	}
}
