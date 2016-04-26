<?php
/**
 * voa_c_api_goods_post_goods
 * 更新商品信息
 * $Author$
 * $Id$
 */

class voa_c_api_goods_post_goods extends voa_c_api_goods_abstract {

	public function execute() {

		// 获取表格 dataid
		$dataid = 0;
		if (isset($this->_params['dataid'])) {
			$dataid = (int)$this->_params['dataid'];
		}

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		if (0 < $dataid) {
			if (!$uda->update($this->_member, $this->_params, $dataid)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}
		} else {
			$goods = array();
			if (!$uda->add($this->_member, $this->_params, $goods)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}

			$this->_result = $goods;
		}

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _set_ptname() {

		parent::_set_ptname();

		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.goods.class', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.goods.tablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.goods.tablecolopt', 'oa');
	}

}
