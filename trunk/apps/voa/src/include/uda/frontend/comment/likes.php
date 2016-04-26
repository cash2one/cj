<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/21
 * Time: 12:00
 */

class voa_uda_frontend_comment_likes extends voa_uda_frontend_comment_abstract {

	protected $_serv = null;

	public function __construct() {
		parent::__construct();
		if($this->_serv == null) {
			$this->_serv = new voa_s_oa_comment_likes();
		}
	}

	public function execute($request, &$result) {

		$this->_params = $request;
		// 查询条件
		$fields = array(
			'cid' => array('cid', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$data = $this->_serv->list_by_conds($conds, '', array('created' => 'DESC'));
		if (empty($result)) {
			return $data = array();
		}
		//$this->_leaves_formt($data, $result);//格式化todo

		return true;
	}
}
