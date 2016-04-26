<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/12/1
 * Time: 19:14
 */
class voa_uda_frontend_association_markhandle extends voa_uda_frontend_community_abstract {

	// 列表
	protected $_serv = null;

	public function __construct() {

		parent::__construct();
		if ($this->_serv == null) {
			$this->_serv = new voa_s_oa_association_mark();
		}
	}

	/**
	 * 标签添加
	 * @param $in
	 * @param $out
	 * @return bool
	 * @throws help_exception
	 */
	public function add($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('order', self::VAR_INT, null, null, false),
			array('title', self::VAR_STR, null, null, false)
		);

		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		$conds = array('title' => $data['title']);
		$result = $this->_serv->get_by_conds($conds);

		if ($result) {
			$this->errmsg(10002, '标签已经存在');
			return false;
		}

		$out = $this->_serv->insert($data);

		return true;
	}

	/**
	 * 标签修改
	 * @param $in
	 * @return bool
	 * @throws help_exception
	 */
	public function update($in) {

		$mdid = $in['mdid'];
		// 查询表格的条件
		$fields = array(
			array('order', self::VAR_INT, null, null, false),
			array('title', self::VAR_STR, null, null, false)
		);

		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		$this->_serv->update($mdid, $data);

		return true;
	}

}