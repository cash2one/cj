<?php
/**
 * voa_uda_frontend_goods_class
 * 统一数据访问/商品应用/快递操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_goods_express extends voa_uda_frontend_goods_abstract {

	public function __construct($ptname) {

		parent::__construct($ptname);
		//$this->_classes = $ptname['classes'];
	}

	/**
	 * 获取快递列表
	 * @param array &$list 快递列表
	 * @return boolean
	 */
	public function list_all($gp, &$list, $page_option, &$total) {
		// 查询表格的条件
		$fields = array(
			array('expid', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}
		$conds['tid'] = $this->_table['tid'];
		
		// 获取快递列表
		$t = new voa_d_oa_goods_express();
		$list = $t->list_by_conds($conds, $page_option, array('expid' => 'desc'));
		// 取总数
		$t->reset();
		$total = $t->count_by_conds($conds);
		if(!empty($list)){
			foreach ($list as &$_v) {
				$_v['_created'] = rgmdate($_v['created']);
			}
		}
		return true;
	}

	/**
	 * 根据 expid 获取分类信息
	 * @param int $expid 分类id
	 * @param array $express 分类信息
	 * @return boolean
	 */
	public function get_one($expid, &$express) {
		
		$t = new voa_d_oa_goods_express();
		$express = $t->get($expid);
		return true;
	}
		
	/**
	 * 新增快递
	 * @param array $gp 数据
	 * @throws service_exception
	 * @return boolean
	 */
	public function add($gp, &$express) {
		// 提取数据
		$express['tid'] = $this->_table['tid'];
		if (!$this->__parse_gp($gp, $express)) {
			return false;
		}
		$t = new voa_d_oa_goods_express();
		// 根据快递类型读取快递
		$so_conds = array(
				'tid' => $this->_table['tid'],
				'exptype' => $express['exptype']
		);
		// 如果快递类型已存在
		if ($t->get_by_conds($so_conds)) {
			$this->set_errmsg(voa_errcode_oa_goods::EXPRESSTYPE_DUPLICATE);
			return false;
		}
		try {
			$t->reset();
			$express = $t->insert($express);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	
		return true;
	}
	
	/**
	 * 更新当个快递信息
	 * @param array $gp 数据
	 * @param int $expid 分类id
	 * @throws service_exception
	 * @return boolean
	 */
	public function update($gp, $expid) {

		// 提取数据
		$data = array();
		if (!$this->__parse_gp($gp, $data)) {
			return false;
		}

		$expid = (int)$expid;
		$t = new voa_d_oa_goods_express();
		try {
			$t->update($expid, $data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 删除快递信息
	 * @param mixed $classid 快递id
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($expid) {

		// 获取搜索条件
		$conds = array(
			'expid' => $expid,
			'tid' => $this->_table['tid']
		);

		// 初始化数据表操作类
		$t = new voa_d_oa_goods_express();
		try {
			$t->reset();
			$t->delete_by_conds($conds);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $gp 请求数据
	 * @param array $table 数据结果
	 * @return boolean
	 */
	private function __parse_gp($gp, &$data) {
		$fields = array(
			array('exptype', self::VAR_STR, 'chk_exptype', voa_errcode_oa_goods::GOODS_EXPRESSTYPE_IS_EMPTY, false),
			array('expcost',self::VAR_STR,NULL,NULL,false)
		);
		// 提取数据
		if (!$this->extract_field($data, $fields, $gp)) {
			return false;
		}

		return true;
	}

	/**
	 * 检查快递类型
	 * @param string $name 快递类型
	 * @param string $err 错误信息
	 * @return boolean
	 */
	public function chk_exptype($name, $err = null) {

		// 如果名称为空
		if (empty($name)) {
			$this->set_errmsg($err);
			return false;
		}

		return true;
	}
}

