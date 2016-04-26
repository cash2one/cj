<?php

/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/21
 * Time: 11:32
 */
class voa_uda_frontend_comment_list extends voa_uda_frontend_comment_abstract {

	protected $_serv = null;

	public function __construct() {

		parent::__construct();
		if ($this->_serv == null) {
			$this->_serv = new voa_s_oa_comment();
		}
	}

	public function execute($request, &$result) {

		$this->_params = $request;
		// 查询条件
		$fields = array(
			'obj_id' => array('obj_id', self::VAR_INT, null, null, true),
			'cp_identifier' => array('cp_identifier', self::VAR_STR, null, null, true),
			'plugin_id' => array('plugin_id', self::VAR_STR, null, null, true),
			'page' => array('page', self::VAR_INT, null, null, true),
			'perpage' => array('perpage', self::VAR_INT, null, null, true),
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}
		$option = array();
		$this->_get_page_option($option, $conds);

		$this->_total = $this->_serv->count_by_conds($conds);

		$data = $this->_serv->list_by_conds($conds, $option, array('created' => 'DESC'));
		if (empty($data)) {
			$data = array();
		}
		$this->_leaves_formt($data, $result);//格式化todo

		return true;
	}

	/**
	 *
	 * 预留格式化代码
	 * @param $request
	 * @param $result
	 * @return bool
	 */
	protected function _leaves_formt($request, &$result) {
	//todo
		if ($request) {
			foreach ($request as &$_v) {
				$_v['_created'] = rgmdate($_v['created'], 'Y-m-d H:i:s');
			}
		}
		$result = $request;

		return true;
	}
}