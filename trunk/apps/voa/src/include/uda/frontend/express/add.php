<?php
/**
 * voa_uda_frontend_express_new
 * 统一数据访问/快递助手/快递登记
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_express_add extends voa_uda_frontend_express_abstract {

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
			array('at_ids',self::VAR_STR,null,null,false),
			array('uid', self::VAR_INT,null, null, false),
			array('username', self::VAR_STR,null, null, false),
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		$serv_m = &service::factory('voa_s_oa_express_mem');
		// 快递基本信息入库
		$new = array(
			'at_id' => $data['at_ids'],
			'uid' => $data['uid'],
			'username'=>$data['username'],//收件人
			'flag' => voa_d_oa_express::GET_NO//快递状态
		);
		$new = $this->_serv->insert($new);

		//收件人信息入库
		$new_get_mem = array(
			'eid' => $new['eid'],
			'uid' => $data['uid'],
			'flag'=> voa_d_oa_express_mem::GET,
			'username'=>$data['username'],
			'updated' => 0
		);
		$out=$serv_m->insert($new_get_mem);


		//接件人信息入库
		$new_mem = array(
			'eid' => $new['eid'],
			'uid' => startup_env::get('wbs_uid'),
			'username' => startup_env::get('wbs_username'),
			'flag'=> voa_d_oa_express_mem::RECEIVE,
			'updated' => 0
		);
		$serv_m->insert($new_mem);

		return true;
	}

}
