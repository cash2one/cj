<?php
/**
 * voa_c_api_travel_post_goodstop
 * 商品置顶
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_goodstop extends voa_c_api_travel_goodsabstract {

	public function execute() {

		$goodsid = (string)$this->_get('goodsid');
		// 读取记录
		$serv_mg = new voa_s_oa_travel_mem2goods();
		$mgdata = $serv_mg->get_by_conds(array(
			'dataid' => $goodsid,
			'uid' => array($this->_member['m_uid'], 0)
		));
		// 如果记录不存在
		if (empty($mgdata)) {
			$this->_set_errcode(voa_errcode_api_travel::MEM_GOODS_IS_NOT_EXIST);
			return true;
		}

		// 更新收藏状态
		$serv_mg->update($mgdata['mgid'], array('fav' => empty($mgdata['fav']) ? 1 : 0));

		return true;
	}

}
