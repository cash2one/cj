<?php
/**
 * voa_uda_frontend_travel_cart
 * 统一数据访问/旅游产品应用/购物车
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_cart extends voa_uda_frontend_travel_abstract {

	/**
	 * 插入购物车产品
	 *
	 * @param $data array
	 *        	订单数据
	 * @return array $cartid 商品购物车id
	 * @return boolean 返回值
	 */
	public function insert($data, &$cartid) {

		$where = array(
			'openid' => $data['openid'],
			'goods_id' => $data['goods_id']
		);
		// 如果有规格则加上查询条件
		if ($data['style_id']) {
			$where['style_id'] = $data['style_id'];
		}

		$d = new voa_d_oa_travel_ordercart();
		$goods = $d->get_by_conds($where);
		// 如果已存在是添加数量,不存在则添加记录
		if (! $goods) {
			$data['saleuid'] = empty($data['saleuid']) ? 0 : $data['saleuid'];
			$data['salename'] = empty($data['salename']) ? '' : $data['salename'];
			$data['cd_id'] = empty($data['cd_id']) ? 0 : $data['cd_id'];
			$this->_calc_income($data);
			$rs = $d->insert($data);
			$cartid = $rs['cartid'];
		} else {
			// $scale = 20; //debug:假设提成百分比为20%
			$goods['num'] = $data['num'];
			$this->_calc_income($goods);
			/**$profit = 0;

			if ($goods['percentage']) {
				$profit = $goods['price'] * ($goods['num'] + $data['num']) * $goods['percentage'] / 100;
			}*/

			$rs = $d->update($goods['cartid'], array('num' => $data['num'], 'profit' => $goods['profit']));
			$cartid = $goods['cartid'];
		}

		return $rs;
	}

	protected function _calc_income(&$goods) {

		$goods['profit'] = 0;
		if (!empty($goods['scale'])) {
			$goods['profit'] = $goods['price'] * $goods['num'] * $goods['scale'] / 100;
		}

		return true;
	}

	/**
	 * 修改购物车产品数量
	 * @param char	$openid	微信id
	 * @param int $cartid	购物车id
	 * @param int $num		数量
	 */
	public function updatenum($openid, $cartid, $num) {

		$d = new voa_d_oa_travel_ordercart();
		$goods = $d->get_by_conds(array(
			'cartid' => $cartid,
			'openid' => $openid
		));
		if ($goods['style_id']) {
			$style = array();
			$rs = $this->get_style($goods['style_id'], $num, $style);
			if (! $rs) {
				return false;
			}
		}

		if (! $goods) {
			$this->errmsg = "购物车中无此产品";
			return false;
		} else {
			$this->_calc_income($goods);
			$rs = $d->update($cartid, array(
				'num' => $num,
				'profit' => $goods['profit']
			));
		}

		return $rs;
	}

	/**
	 * 删除购物车产品
	 *
	 * @param char $openid
	 * @param int $cartid
	 */
	public function delete($openid, $cartid) {

		$d = new voa_d_oa_travel_ordercart();
		$rs = $d->delete_by_conds(array(
			'cartid' => $cartid,
			'openid' => $openid
		));
		return $rs;
	}

	/**
	 * 购物车-产品列表
	 *
	 * @param char $openid
	 * @param array $list
	 *        	产品列表
	 * @return boolean
	 */
	public function get_list($openid, &$list, $conditions = array(), $page_option = array()) {

		$d = new voa_d_oa_travel_ordercart();
		$conditions['openid'] = $openid;
		$list = $d->list_by_conds($conditions, $page_option);
		$this->_ptname = array(
			'plugin' => 'travel',
			'table' => 'goods',
			'classes' => voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa'),
			'columns' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa'),
			'options' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa')
		);
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		$list = empty($list) ? array() : $list;
		foreach ($list as &$value) {
			unset($value['openid']);
			$value['_created'] = rgmdate($value['created']);
			$uda->get_one($value['goods_id'], $value['goods']);
			$value['style_amount'] = 0;
			$value['style_price'] = 0;
			if (! empty($value['goods']['styles'])) {
				if (! empty($value['goods']['styles'][$value['style_id']])) {
					$value['style_amount'] = $value['goods']['styles'][$value['style_id']]['amount'];
					$value['style_price'] = $value['goods']['styles'][$value['style_id']]['price'];
				}
			}

			$value['goods']['cover'] = empty($value['goods']['cover']) ? '' : $value['goods']['cover'][0]['url'];
		}

		$list = $list ? array_values($list) : array();
		return true;
	}

	/**
	 * 购物车商品统计
	 *
	 * @param char $openid	微信id
	 * @param int $total 	总数
	 * @return boolean		结果
	 */
	public function get_cart_total($openid, &$total) {
		$d = new voa_d_oa_travel_ordercart();
		$conditions['openid'] = $openid;
		$total = $d->count_by_conds($conditions);

		return true;
	}

	/**
	 * 获取产品信息
	 *
	 * @param int $goods_id	产品id
	 * @param array $goods	产品列表
	 * @return boolean 返回值
	 */
	public function get_goods($goods_id, &$goods)
	{
		$d = new voa_d_oa_goods_data();
		$goods = $d->get($goods_id);
		if($goods) {
			unset($goods['message']);
			unset($goods['diys']);
			$goods = array(
				'dataid'	=>	$goods['dataid'],
				'subject'	=>	$goods['subject'],
				'uid'		=>	$goods['uid'],
				'proto_2'	=>	$goods['proto_2'],
				'percentage' => $goods['percentage']
			);
		}
		return true;
	}

	/**
	 * 读规格
	 *
	 * @param int $styleid
	 * @param int $num
	 * @param array $style
	 * @return boolean
	 */
	public function get_style($styleid, $num, &$style) {

		$s = new voa_d_oa_travel_styles();
		$style = $s->get($styleid);
		if(!$style) {
			$this->errmsg = $sty.'无法获取此规格';
			return false;
		}

		if($num > $style['amount']) {
			$this->errmsg = '数量超过库存';
			return false;
		}

		return true;
	}
}
