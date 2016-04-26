<?php
/**
 * voa_uda_frontend_goods_attach
 * 统一数据访问/商品应用/附件操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_goods_attach extends voa_uda_frontend_goods_abstract {

	public function __construct($ptname) {

		parent::__construct($ptname);
	}

	/**
	 * 获取分类列表
	 * @param array &$list 分类列表
	 * @return boolean
	 */
	public function list_all($gp, &$list) {

		// 查询表格的条件
		$fields = array(
			array('gaid', self::VAR_INT, null, null, true),
			array('at_id', self::VAR_INT, null, null, true),
			array('dataid', self::VAR_INT, null, null, true),
			array('uid', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}

		$conds['tid'] = $this->_table['tid'];
		// 读取附件信息
		$t = new voa_d_oa_goods_attach();
		$list = $t->list_by_conds($conds);

		return true;
	}

	/**
	 * 根据 gaid 获取分类信息
	 * @param int $gaid 分类id
	 * @param array $attach 分类信息
	 * @return boolean
	 */
	public function get_one($gaid, &$attach) {

		$t = new voa_d_oa_goods_attach();
		$attach = $t->get($gaid);

		return true;
	}

	/**
	 * 新增附件对应关系
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @throws service_exception
	 * @return boolean
	 */
	public function add($member, $gp, &$attach) {

		// 提取数据
		$attach = array(
			'uid' => $member['m_uid'],
			'tid' => $this->_table['tid']
		);
		if (!$this->__parse_gp($gp, $attach)) {
			return false;
		}

		$t = new voa_d_oa_goods_attach();

		try {
			$attach = $t->insert($attach);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 删除分类信息
	 * @param int $gaid 附件gaid
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($gaid, $att = null) {

		// 初始化数据操作类
		$t_att = new voa_d_oa_goods_attach();
		$serv_att = &service::factory('voa_s_oa_common_attachment');

		// 获取附件id
		$at_ids = array();
		if (empty($att)) {
			$gaid = (array)$gaid;
			$atts = $t_att->list_by_pks($gaid);
			foreach ($t_att as $_at) {
				$at_ids[] = $_at['at_id'];
			}
		}

		try {
			//$t_att->beginTransaction();

			// 先取附件信息
			$g_atts = $t_att->get($gaid);
			$at_ids = array();
			foreach ($t_att as $_at) {
				$at_ids[] = $_at['at_id'];
			}

			// 删除附件
			$serv_att->delete_by_ids($at_ids);
			// 删除附件对应关系
			$t_att->delete($gaid);

			//$t_att->commit();
		} catch (Exception $e) {
			//$t_att->rollBack();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $gp 请求数据
	 * @param array $attach 数据结果
	 * @return boolean
	 */
	private function __parse_gp($gp, &$attach) {

		$fields = array();
		// 如果 at_id 不存在
		if (!isset($attach['at_id'])) {
			$fields[] = array('at_id', self::VAR_INT, '_chk_at_id', null);
		}

		// 如果 dataid 不存在
		if (!isset($attach['dataid'])) {
			$fields[] = array('dataid', self::VAR_INT, '_chk_dataid', null);
		}

		// 如果需要获取的字段为空, 则直接返回
		if (empty($fields)) {
			return true;
		}

		// 提取数据
		if (!$this->extract_field($attach, $fields, $gp)) {
			return false;
		}

		return true;
	}

	/**
	 * 检查 at_id 是否存在
	 * @param int $at_id 附件id
	 * @param string $err 错误信息
	 * @return boolean
	 */
	protected function _chk_at_id($at_id, $err = null) {

		// 如果 at_id 不存在
		if (0 >= $at_id) {
			return false;
		}

		$t_att = new voa_d_oa_goods_attach();
		// 如果附件不存在
		if (!$att = $t_att->get($at_id)) {
			return false;
		}

		return true;
	}

	/**
	 * 检查 dataid 是否存在
	 * @param int $dataid 数据id
	 * @param string $err 错误信息
	 * @return boolean
	 */
	protected function _chk_dataid($dataid, $err = null) {

		// 如果 dataid
		if (0 >= $dataid) {
			return false;
		}

		$t_data = new voa_d_oa_goods_data();
		// 如果记录不存在
		if (!$data = $t_data->get($dataid)) {
			return false;
		}

		return true;
	}

}

