<?php
/**
 * voa_c_api_travel_post_goodstpl
 * 更新商品模板
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_goodstpl extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 如果不是管理员
		if (0 == $this->_is_admin) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_PRIVILEGES);
			return true;
		}

		$tpls = array('travel', 'crm');
		$tpl = (string)$this->_get('tpl');
		$tpl = in_array($tpl, $tpls) ? $tpl : 'crm';
		$serv_set = new voa_s_oa_travel_setting();
		$serv_set->update_by_conds(array('skey' => 'goods_tpl_style'), array('value' => $tpl));

		// 根据模板修改字段
		$serv_tc = new voa_s_oa_goods_tablecol();
		if ('travel' == $tpl) {
			$serv_tc->update(array('tc_id' => array(7, 8)), array('isuse' => voa_d_oa_goods_tablecol::ISUSE_DISABLED));
		} else {
			$serv_tc->update(array('tc_id' => array(7, 8)), array('isuse' => voa_d_oa_goods_tablecol::ISUSE_NORMAL));
		}

		// 更新缓存
		voa_h_cache::get_instance()->get('plugin.travel.setting', 'oa', true);
		voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa', true);

		return true;
	}

}
