<?php
/**
 * voa_uda_frontend_express_mem_new
 * 统一数据访问/快递助手/设置代领人
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_express_mem_add extends voa_uda_frontend_express_abstract {

	public function __construct() {

		parent::__construct();
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
			array('eid',self::VAR_INT,null,null,false),
			array('uid', self::VAR_INT,null, null, false),
			array('username', self::VAR_STR,null, null, false),
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		$serv_m = &service::factory('voa_s_oa_express_mem');

		//代领人信息入库
		$new_get_mem = array(
			'eid' => $data['eid'],
			'uid' => $data['uid'],
			'flag'=> voa_d_oa_express_mem::COLLECTION,
			'username'=>$data['username'],
			'updated' => 0
		);
		$out=$serv_m->insert($new_get_mem);


		return true;
	}

}
