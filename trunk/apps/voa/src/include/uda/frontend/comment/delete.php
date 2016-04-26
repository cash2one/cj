<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/21
 * Time: 16:30
 */

class voa_uda_frontend_comment_delete extends voa_uda_frontend_comment_abstract {

	protected $_serv = null;

	public function __construct() {

		parent::__construct();
		if ($this->_serv == null) {
			$this->_serv = new voa_s_oa_comment();
			$this->_community = new voa_s_oa_community();
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
			'obj_id' => array('obj_id', self::VAR_ARR, null, null, false),
			'tid' => array('tid', self::VAR_INT, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 删除主题的评论信息
		$this->_serv->delete($data['obj_id']);

		return true;
	}

}