<?php
/**
 * voa_uda_frontend_travel_goods
 * 统一数据访问/旅游产品应用/销售管理
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_sale extends voa_uda_frontend_travel_abstract {


	/**
	 * 根据条件销售列表
	 * @param array $conditions	条件
	 * @param int $page 页码
	 * @param int $size 每页数量
	 * @param array $list 返回的销售数组
	 * @param int $total 返回的总数量
	 * @return boolean 返回值
	 */
	public function get_list($conditions, $page, $size, &$list, &$total)
	{
		$start = ($page - 1) * $size;
		if($start < 0) $start = 0;
		$d = new voa_d_oa_travel_sale();
		$conditions['status<?'] = 3;
		
		$list = $d->list_by_conds($conditions, array($start, $size), array('saleid' => 'DESC'));
		$list = $list ? array_values($list) : array();
		
		$total = $d->count_by_conds($conditions);
		return true;
	}


	/**
	 * 获取订单详情
	 *
	 * @param int $saleid	销售id
	 * @param array $list 	返回数据
	 * @return boolean 返回值
	 */
	public function get($saleid, &$sale)
	{
		$d = new voa_d_oa_travel_order();
		$sale = $d->get($saleid);
		return true;
	}
	
	/**
	 * 移除,拒绝销售
	 *
	 * @param int $sale	直销员id
	* @return boolean 返回值
	 */
	public function delete($saleid)
	{
		$d = new voa_d_oa_travel_sale();
		$rs = $d->delete($saleid);
		return $rs;
	}
	
	/**
	 * 待申请数量
	 *
	 */
	public function count(& $total)
	{
		$where = array(
			'status<?'	=>	3,
			'sale_status'	=>	0,
		);
		$d = new voa_d_oa_travel_sale();
		$total = $d->count_by_conds($where);
		
		return true;
	}
	
	/**
	 * 审核通过
	 *
	 * @param int $sale	直销员id
	* @return boolean 返回值
	 */
	public function pass($saleid)
	{
		//添加二维码映射关系
		$qrcode = new voa_d_oa_travel_qrcode();
		$map = $qrcode->get($saleid);
		if(!$map) {
			//添加
			$max = $qrcode->list_by_conds(array(), array(0,1), array('code_id' => 'desc'));
			if($max) {
				$max = current($max);
				$code_id = $max['code_id'] + 1;
			}else{
				$code_id = 1001;
			}
			$d = array('sale_id' => $saleid, 'code_id' => $code_id);
			$qrcode->insert($d);
		}else{
			$code_id = $map['code_id'];
		}
		
		
		$d = new voa_d_oa_travel_sale();
		$data = array('sale_status' => 1);
		$rs = $d->update($saleid, $data);
		if(!$rs) return false;
			
		return $rs;
	}
}
