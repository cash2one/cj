<?php
/**
 * voa_uda_frontend_travel_goods
 * 统一数据访问/旅游产品应用/订单信息
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_order extends voa_uda_frontend_travel_abstract {

	/**
	 * 返回状态中文
	 *
	 * @param mixed $status	整数或空
	 * @return string	字符串或数组
	 */
	function status($status = null)
	{
		$map = array(
			1	=>	'待支付',
			2	=>	'支付中',
			3	=>	'已支付',
			4	=>	'已发货',
			9	=>	'已完成',
			20	=>	'已取消',
			30	=>	'已关闭',
			40	=>	'支付失败'
		);
		if($status) {
			return $map[$status] ? $map[$status] : '状态未知';
		}
		return $map;
	}
	/**
	 * 立即购买:插入订单
	 * @param $data array 订单数据
	 * @return array $order	订单信息
	 * @return boolean 返回值
	 */
	public function insert($data, &$order) {

		//添加到客户表
		$this->insertCustomer($data);

		if(!$data['goods_num']) {
			$this->errmsg = '产品数量错误';
			logger::error("产品数量错误");
			return false;
		}

		//获取产品信息
		$goods_id = intval($data['goods_id']);
		if(!$goods_id) {
			$this->errmsg = 'goods_id不能为空';
			logger::error("goods_id不能为空");
			return false;
		}

		$d = new voa_d_oa_goods_data();
		$goods = $d->get($goods_id);
		if(!$goods) {
			$this->errmsg = $goods_id.'产品不存在';
			logger::error($goods_id.'产品不存在');
			return false;
		}


		if($data['sale_id']) {
			$m = new voa_d_oa_member();
			$member = $m->fetch($data['sale_id']);
			$data['sale_name'] = $member['m_username'];
			$data['sale_phone'] = $member['m_mobilephone'];
		}


		//读规格
		$data['price'] = $goods['proto_2'] * 100;
		if($data['style_id']) {
			$style = new voa_d_oa_travel_styles();
			$sty = $style->get_by_conds(array('styleid' => $data['style_id'], 'goodsid' => $data['goods_id']));
			if(!$sty) {
				$this->errmsg = $sty.'无法获取此规格';
				logger::error($sty.'无法获取此规格');
				return false;
			}
			if($data['goods_num'] > $sty['amount']) {
				$this->errmsg = '数量超过库存';
				logger::error("数量超过库存");
				return false;
			}
			$data['price'] = $sty['price'];
		}


		$data['amount'] = $data['price']  * $data['goods_num'];
		if($data['amount'] == 0) {
			$this->errmsg = '价格异常';
			logger::error("价格异常");
			return false;
		}
		$data['goods_name'] = $goods['subject'];
		$scale = 20;	//假设提成为20%
		$data['profit'] = intval($data['amount'] * $scale / 100);
		$data['order_status'] = voa_d_oa_travel_order::$PAY_ING;


		//添加订单表和订单产品表
		$goods = array(
			'goods_id'	=>	$data['goods_id'],
			'style_id'	=>	$data['style_id'],
			'num'	=>	$data['goods_num'],
			'price'	=>	$data['price'],
			'goods_name'	=>	$data['goods_name'],
			'scale'	=>	$scale,		//debug:假设提成为20%
			'profit'	=>	$data['profit'],
		);
		unset($data['goods_id']);
		unset($data['style_id']);
		unset($data['goods_num']);
		unset($data['price']);
		unset($data['goods_name']);
		$d = new voa_d_oa_travel_order();
		$order = $d->insert($data);
		if(!$order) {
			$this->errmsg = '添加订单失败';
			logger::error("添加订单失败");
			return false;
		}
		$goods['order_id'] = $order['orderid'];
		$g = new voa_d_oa_travel_ordergoods();
		$rs = $g->insert($goods);
		if(!$rs) {
			$this->errmsg = '添加订单产品失败';
			logger::error("添加订单产品失败");
			return false;
		}
		$order['goods_name'] = $goods['goods_name'];
		return true;
	}

	/**
	 * 购物车购买:插入订单
	 *
	 * @param $data array 订单数据
	 * @param $cartids 需要处理的定单商品id
	 * @return array $order 订单信息
	 * @return boolean 返回值
	 */
	public function cart_insert($data, &$order, $cartids = '') {

		// 添加到客户表
		$this->insertCustomer($data);
		// 读购物车里的产品
		$cart = new voa_uda_frontend_travel_cart();
		$list = array();
		$rs = $cart->get_list($data['customer_openid'], $list);
		if (! $rs || ! $list) {
			$this->errmsg = '购物车中无产品';
			logger::error("购物车中无产品");
			return false;
		}

		$cart_list = array();
		$cartids_list = array();
		// 判断如果是有客户传过来的购物车商品id则处理
		if (! empty($cartids)) {
			foreach ($list as $k => $item) {
				if (in_array($item['cartid'], $cartids)) {
					$cart_list[$item['cartid']] = $item;
					$cartids_list[] = $item['cartid'];
				}
			}
		}

		if (empty($cart_list)) {
			$this->errmsg = '购物车中无产品';
			logger::error("购物车中无产品");
			return false;
		}

		// 读快递分类缓存
		$p_sets=voa_h_cache::get_instance()->get('plugin.goods.goodsexpress', 'oa');
		$express_price = 0;//设置快递价格
		foreach ($p_sets as $k => $v) {
			if ((string)$v['expid'] ==  (string)$data['expid']) {
				$express_price =  $v['expcost'];
			}
		}

		// 计算总价格,总提成
		$amount = $profit = 0;
		foreach ($cart_list as $l) {
			$amount += $l['price'] * $l['num'];
			$profit += $l['profit'];
			$goods_name[] = $l['goods_name'];
		}

		$goods_name = $goods_name[0];
		$data['amount'] = $amount + $express_price*100;
		$data['profit'] = $profit;
		/**if ($data['sale_id']) {
			$m = new voa_d_oa_member();
			$member = $m->fetch($data['sale_id']);
			$data['sale_name'] = $member['m_username'];
			$data['sale_phone'] = $member['m_mobilephone'];
		}*/

		$data['order_status'] = voa_d_oa_travel_order::$PAY_ING;
		$d = new voa_d_oa_travel_order();
		$order = $d->insert($data);
		if (! $order) {
			$this->errmsg = '添加订单失败';
			logger::error("添加订单失败");
			return false;
		}

		$order['goods_name'] = $goods_name;
		// 购物车中产品转移到订单产品表中
		$g = new voa_d_oa_travel_ordergoods();
		foreach ($cart_list as $l) {
			$goods = array(
				'order_id' => $order['orderid'],
				'goods_id' => $l['goods_id'],
				'goods_name' => $l['goods_name'],
				'cd_id' => $l['cd_id'],
				'saleuid' => $l['saleuid'],
				'salename' => $l['salename'],
				'style_id' => $l['style_id'],
				'style_name' => $l['style_name'],
				'num' => $l['num'],
				'price' => $l['price'],
				'scale' => $l['scale'],
				'profit' => $l['profit']
			);
			$rs = $g->insert($goods);
			if (! $rs) {
				$this->errmsg = '添加订单产品失败';
				logger::error("添加订单产品失败");
				return false;
			}
		}

		// 清空购物车
		$cart = new voa_d_oa_travel_ordercart();
		// $rs = $cart->delete_by_conds(array('openid' => $data['customer_openid']));
		// 只删除需要处理的订单产品
		$rs = $cart->delete_by_conds(array('cartid' => $cartids_list));
		if (! $rs) {
			$this->errmsg = '清空购物车失败';
			logger::error("清空购物车失败");
			return false;
		}

		return true;
	}

	//添加客户数据
	public function insertCustomer($data)
	{
		if(!$data['sale_id']) return true;
		$t = new voa_d_oa_customer_table();
		$t = $t->get_by_conds(array('tunique' => 'customer'));
		$tid = intval($t['tid']);
		$c = new voa_d_oa_customer_data();
		$update = array(
			'tid'	=>	$tid,
			'mobile' => $data['mobile']
		);
		$cus = $c->get_by_conds($update);
		if(!$cus) {
			$update['uid'] = intval($data['sale_id']);
			$update['truename'] = $data['customer_name'];
			$update['diys'] = 'a:1:{s:2:"_6";s:2:"12";}';
			$update['message'] = '';
			$rs = $c->insert($update);
			if(!$rs) {
				$this->errmsg = '添加客户信息错误';
				logger::error("添加客户信息错误");
				return false;
			}
		}
	}

	//设置订单表的微信订单号字段
	function set_wx_order($ordersn, $wx_orderid)
	{
		$d = new voa_d_oa_travel_order();
		$rs = $d->update_by_conds(array('ordersn' => $ordersn), array('wx_orderid' => $wx_orderid));
		return $rs;
	}

	/**
	 * 根据条件获取订单列表(企业号)
	 * @param array $conditions	条件
	 * @param int $page 页码
	 * @param int $size 每页数量
	 * @param array $list 订单数组
	 * @param int $total 总数量
	 * @return boolean 返回值
	 */
	public function get_list($conditions, $page, $size, &$list, &$total)
	{
		$start = ($page - 1) * $size;
		if($start < 0) $start = 0;

		$d = new voa_d_oa_travel_order();
		$g = new voa_d_oa_travel_ordergoods();

		$conds = $conditions;
		//查询订单产品信息
        $order_list = $g->list_order_goods($conditions,array($start, $size), array('order_id' => 'DESC'));
        if (!empty($order_list)) {

        	$orderids = array();
        	foreach ($order_list as $v) {
        		$orderids[]=$v['order_id'];
        	}
        	$conditions['orderid'] = $orderids;
        	unset($conditions['saleuid']);

        	if (!empty($conditions['sale_name'])) {
 			     unset($conditions['sale_name']);
		    }
        	//查询订单信息
        	$list = $d->list_by_conds($conditions,array(),array('orderid' => 'DESC'));
        	
        	//查询产品信息
        	$conditions['order_id'] = $orderids;
        	unset($conditions['orderid']);

        	$goods = $g->list_by_conds($conditions);
        	
        	$goods_new = array();
        	foreach ($goods as $k=>$v) {
        		$goods_new [$v['order_id']][] = $v;
        	}

        	//重组数据
        	foreach ($list as &$v) {
        		$v['_created'] = rgmdate($v['created']);
        		$v['_order_status'] = $this->status($v['order_status']);
        		$v['goods_list'] = $goods_new[$v['orderid']];
        	}
        	unset($v);
        }
        $list = $list ? array_values($list) : array();
        $total = $g->count_order_goods($conds);
        return true;
	}

	/**
	 * 根据条件获取订单列表(后台查询)
	 * @param array $conditions	条件
	 * @param int $page 页码
	 * @param int $size 每页数量
	 * @param array $list 订单数组
	 * @param int $total 总数量
	 * @return boolean 返回值
	 */
	public function get_order_list($conditions, $page, $size, &$list, &$total)
	{
		$start = ($page - 1) * $size;
		if($start < 0) $start = 0;

		$d = new voa_d_oa_travel_order();
		$g = new voa_d_oa_travel_ordergoods();

		$conds = $conditions;
		//查询订单产品信息
		$list = $d->list_mem_join_order($conditions,array($start, $size), array('a.created' => 'DESC'));
		$list = $list ? array_values($list) : array();
		$total = $d->count_by_conds_left_join($conds);
		return true;
	}

	/**
	 * 根据条件获取订单列表
	 * @param array $conditions	条件
	 * @param int $page 页码
	 * @param int $size 每页数量
	 * @param array $list 订单数组
	 * @param int $total 总数量
	 * @return boolean 返回值
	 */
	public function get_customer_list($conditions, $page, $size, &$list, &$total)
	{
		$start = ($page - 1) * $size;
		if($start < 0) $start = 0;
		$d = new voa_d_oa_travel_order();

		$g = new voa_d_oa_travel_ordergoods();
		$conditions['status<?'] = 3;

		if ($list = $d->list_by_conds($conditions, array($start, $size), array('orderid' => 'DESC'))) {

			$orderids = array();
			foreach ($list as $v) {
				$orderids[]=$v['orderid'];
			}

		    //根据订单查询产品
			$goods = $g->list_by_conds(array('order_id'=>$orderids));

		    $goods_new = array();
        	foreach ($goods as $k=>$v) {
        		$goods_new [$v['order_id']] = $v;
        	}

			foreach ($list as &$value) {
				// 增加一个格式化时间
				$value['_created'] = rgmdate($value['created']);
				$value['_order_status'] = $this->status($value['order_status']);
				$value['goods_list'][] = $goods_new[$value['orderid']];
			}
		}
		$list = $list ? array_values($list) : array();
		$total = $d->count_by_conds($conditions);
		return true;
	}

	/**
	 * 获取订单详情
	 *
	 * @param array $openid
	 * @param int $order
	 * @return boolean 返回值
	 */
	public function get_order($orderid, &$order)
	{
		$d = new voa_d_oa_travel_order();
		$g = new voa_d_oa_travel_ordergoods();
		$order = $d->get($orderid);
		$this->_ptname = array(
				'plugin' => 'travel',
				'table' => 'goods',
				'classes' => voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa'),
				'columns' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa'),
				'options' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa')
		);
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		$goods = $g->list_by_conds(array('order_id' => $order['orderid']));
		$goods = $goods ? array_values($goods) : array();
		$ts = startup_env::get('timestamp');
		foreach($goods as $k => &$item) {
			$uda->get_one($item['goods_id'], $item['goods']);
			$item['sig'] = voa_h_func::sig_create(array($item['goods_id']), $ts);
			$item['goods']['cover'] = empty($item['goods']['cover']) ? '' : $item['goods']['cover'][0]['url'];
		}
		$order['goods_list'] = $goods;
		if ($order['created']) {
			$order['_created'] = rgmdate($order['created']);
			$order['_order_status'] = $this->status($order['order_status']);
		}
		return true;
	}

	/**
	 * 条件查询订单
	 */
	public function get_by_conds($data){
		$d = new voa_d_oa_travel_order();
		return $d->get_by_conds($data);
	}

    /**
     * 更新订单
     */
	public function update($val,$submit){
		$d = new voa_d_oa_travel_order();
		return $d->update($val,$submit);
	}

	/**
	 * 获取客户的消费数量
	 *
	 * @param int $cid 客户id
	 * @param int $total 统计
	 * @return boolean 返回值
	 */
	public function get_total_by_customerid($cid, &$total) {

		// 先取
		$order = new voa_d_oa_travel_order();
		$total = $order->count_by_conds(array('customer_id'=>$cid));

		return true;
	}

	/**
	 * 获取客户的消费的商品列表
	 *
	 * @param int $id 客户id
	 * @param mixed $list 返回的列表
	 * @param int $total 统计
	 * @param string $type 类型， 客户或销售
	 * @return boolean 返回值
	 */
	public function get_goods_by_id($id, &$list, &$total, $type="customer_id", $starttime = 0) {

		// 先取
		$where = array($type=>$id);
		if ($starttime) {
			$where['pay_time > ? '] = $starttime;
		}

		$order = new voa_d_oa_travel_order();
		$order_list = $order->list_by_conds($where);
		$order_ids = array();
		if (!empty($order_list)) {
			foreach ($order_list as $item) {
				$order_ids[] = $item['orderid'];
			}
		} else {
			return false;
		}


		$order_goods = new voa_d_oa_travel_ordergoods();
		$list = $order_goods->list_by_conds(array('order_id'=>$order_ids));
		$this->_ptname = array(
				'plugin' => 'travel',
				'table' => 'goods',
				'classes' => voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa'),
				'columns' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa'),
				'options' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa')
		);
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		foreach ($list as &$item) {
			$uda->get_one($item['goods_id'], $item['goods']);
			$item['sig'] = voa_h_func::sig_create(array($item['goods_id']), startup_env::get('timestamp'));
			$item['goods']['cover'] = empty($item['goods']['cover']) ? '' : $item['goods']['cover'][0]['url'];
		}
		$total = count($list);

		return true;

	}

	/**
	 * 删除订单
	 *
	 * @param int $orderid	订单id
	* @return boolean 返回值
	 */
	public function delete($orderid)
	{
		$d = new voa_d_oa_travel_order();
		$rs = $d->delete_by_conds(array(
			'orderid' => $orderid,
			'order_status not in (?)' => array(
				voa_d_oa_travel_order::$PAY_SECCESS,
				voa_d_oa_travel_order::$PAY_SEND,
				voa_d_oa_travel_order::$PAY_SIGN
			)
		));
		return $rs;
	}
	/**
	 * 返回默认地址
	 *
	 * @param string $openid		微信开放id
	 * @param array $dft_address	返回默认地址
	 * 	name	姓名
	 *  phone	电话
	 *  adr		完整地址
	 * @return boolean 返回值
	 */
	public function address($openid, & $dft_address)
	{
		$d = new voa_d_oa_travel_order();
		$order = $d->list_by_conds(array('customer_openid' => $openid), 1, array('orderid' => 'DESC'));
		if(!$order) {
			$dft_address = array();
		}else{
			$order = array_values($order);
			$dft_address = array(
				'name' => $order[0]['customer_name'],
			 	'phone' => $order[0]['mobile'],
			 	'adr' => $order[0]['address']
			);
		}
		return true;
	}

	/**
	 * 修改订单状态,并添加操作日志
	 *
	 * @param int $orderid		订单号
	 * @param int $new_status	状态
	 */
	public function change_status($order, $new_status, $old_status, $memo, $userid, $username)
	{
		//修改订单状态
		$d = new voa_d_oa_travel_order();
		$data['order_status'] = $new_status;
		if($data['order_status'] == voa_d_oa_travel_order::$PAY_SECCESS) {
			$data['pay_time'] = time();
		}
		$orderid = $order['orderid'];
		$data['express'] = $order['express'];
		$data['expressn'] = $order['expressn'];
		$rs = $d->update($orderid, $data);
		if(!$rs) {
			$this->errmsg = "修改订单状态失败";
			return false;
		}

		$log = new voa_d_oa_travel_orderlog();


		//添加操作日志
		$data = array(
			'order_id'		=>	$orderid,
			'new_status'	=>	$new_status,
			'old_status'	=>	$old_status,
			'oper_id'		=>	$userid,
			'oper_name'		=>	$username,
			'memo'			=>	$memo
		);
		$rs = $log->insert($data);
		return $rs;
	}

	/**
	 * 加载操作日志
	 *
	 * @param int $orderid	订单id
	 * @param array $list	返回订单列表
	 */
	public function loadlog($orderid, &$list)
	{
		$log = new voa_d_oa_travel_orderlog();
		$list = $log->list_by_conds(array('order_id' => $orderid), null, array('logid' => 'desc'));
		if(!$list) $list = array();
		$list = array_values($list);
		foreach ($list as & $l)
		{
			$l['_old_status'] = $this->status($l['old_status']);
			$l['_new_status'] = $this->status($l['new_status']);
			$l['_created'] = rgmdate($l['created']);
		}
		return true;
	}
	/**
	 * 计算提成
	 *
	 * @param array $where
	 * @param int $total	提成(分)
	 */
	public function profit($where, &$profit)
	{
		$o = new voa_d_oa_travel_order();
		$list = $o->list_by_conds($where);
		$profit = 0;
		foreach ($list as $l)
		{
			$profit += $l['profit'];
		}
		return true;
	}

	/**
	 * 微信返回支付成功结果
	 * 修改订单状态和支付时间
	 *
	 * @param int $ordersn		订单编号
	 */
	public function notifySuccess($ordersn) {

		$d = new voa_d_oa_travel_order();
		$order = $d->get_by_conds(array('ordersn' => $ordersn));
		if (!$order) {
			logger::error("{$ordersn} 订单不存在");
			return false;
		}

		// 已修改的不再重复修改状态
		if ($order['order_status'] == voa_d_oa_travel_order::$PAY_SECCESS) {
			return true;
		}

		$update = array(
			'order_status' => voa_d_oa_travel_order::$PAY_SECCESS,
			'pay_time' => startup_env::get('timestamp'),
			'status' => voa_d_oa_travel_order::STATUS_UPDATE
		);
		$rs = $d->update_by_conds(array('ordersn' => $ordersn), $update, true);
		if (!$rs) {
			logger::error("{$ordersn} 修改订单状态错误");
			return false;
		}

		// 取规格信息
		$d_order_goods = new voa_d_oa_travel_ordergoods();
		if (!$order_goods = $d_order_goods->list_by_conds(array('order_id' => $order['orderid']))) {
			logger::error("不存在的订单(orderid:{$order['orderid']})");
			return false;
		}

		//扣掉库存
		$style = new voa_d_oa_travel_styles();
		$g = new voa_d_oa_goods_data();
		foreach ($order_goods as $_goods) {
			//$sty = $style->get($_goods['style_id']);
			//$kucun = $sty['amount'] - $_goods['num'];
			$rs = $style->update($_goods['style_id'], array('`amount`=`amount`-?' => $_goods['num']));
			if (!$rs) {
				logger::error("{$ordersn}:{$_goods['style_id']} 扣库存错误");
				//return false;
			}

			//增加销售量
			//$_goods = $g->get($order_goods['goods_id']);
			$rs = $g->update($_goods['goods_id'], array('`proto_5`=`proto_5`+?' => $_goods['num']));
			if (!$rs) {
				logger::error("{$ordersn}:{$_goods['goods_id']} 修改产品销量错误");
			}
		}

		return true;
	}
}
