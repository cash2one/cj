<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/21
 * Time: 15:08
 */
class voa_uda_frontend_community_likes extends voa_uda_frontend_community_abstract {

	protected $_serv = null;

	public function __construct() {
		parent::__construct();
		if($this->_serv == null) {
			$this->_serv = new voa_s_oa_common_dynamic();
		}
	}

	public function execute($request, &$result) {

		$this->_params = $request;
		// 查询条件
		$fields = array(
			'obj_id' => array('obj_id', self::VAR_INT, null, null, true),
			'cp_identifier' => array('cp_identifier', self::VAR_STR, null, null, true),
			'dynamic' => array('dynamic', self::VAR_INT, null, null, true),
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
		$this->_likes_formt($data, $result);//格式化todo
		return true;
	}

	protected function _likes_formt($request, &$result) {

		if ($request) {
			foreach($request as &$_v) {
				$_v['_created'] = rgmdate($_v['created'], 'Y-m-d H:i:s');
			}
		}
		$result = $request;

		return true;
	}
}
