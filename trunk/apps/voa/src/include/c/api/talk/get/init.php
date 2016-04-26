<?php
/**
 * voa_c_api_talk_get_chatlist
 * 客户端聊天窗口初始化
 * 获取以下信息
 * 获取产品名称 $goods_id
 * 销售名称: 如果给了sala_id
 * 客户名称: 如果给了tv_id
 * $Author$
 * $Id$
 */

class voa_c_api_talk_get_init extends voa_c_api_talk_abstract {

	public function execute() {

		$goods_id = (int)$this->_get('goods_id');
		$sale_id = (int)$this->_get('sale_id');
		$tv_id = (int)$this->_get('tv_id');

		// 取客户信息
		$uda_viewer = new voa_uda_frontend_talk_getviewer();
		$viewers = array();
		if (!$uda_viewer->execute(array('tv_uid' => $tv_id), $viewers)) {
			$this->_set_errcode(voa_errcode_api_talk::VIEWER_IS_NOT_EXISTS);
			return true;
		}

		// 如果客户信息不存在
		if (empty($viewers)) {
			$this->_set_errcode(voa_errcode_api_talk::VIEWER_IS_NOT_EXISTS);
			return true;
		}

		$viewer = array_pop($viewers);

		// 取销售名称
		$uda_mem = new voa_uda_frontend_member_get();
		$member = array();
		if (!$uda_mem->member_by_uid($sale_id, $member)) {
			$this->_set_errcode($uda_mem->errcode.':'.$uda_mem->errmsg);
			return true;
		}

		// 取产品信息
		$uda_goods = new voa_uda_frontend_goods_data();
		$goods = array();
		if (!$uda_goods->get_subject($goods_id, $goods)) {
			$this->_set_errcode($uda_mem->errcode.':'.$uda_mem->errmsg);
			return true;
		}

		$this->_result = array(
			'goods_name' => $goods['subject'],
			'sale_name' => $member['m_username'],
			'sale_avatar' => voa_h_user::avatar($member['m_uid'], $member),
			'tv_name' => $viewer['username'],
			'tv_avatar' => voa_h_user::avatar($viewer['tv_uid'], $viewer)
		);

		return true;
	}

}
