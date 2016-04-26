<?php

/**
 * voa_uda_frontend_campaign_orders
 * 活动推广  uda 接单详情
 * User: Muzhitao
 * Date: 2015/8/26 0026
 * Time: 14:02
 */
class  voa_uda_frontend_campaign_orders extends voa_uda_frontend_campaign_base {
	/** service 类 */
	private $__service = null;

	public function __construct() {

		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_campaign_orders();
		}
	}

	/**
	 * 增加接单详情
	 * @param array $request
	 * @param $result
	 * @return bool
	 */
	public function add_orders(array $request, &$result) {

		$orders = array(
			'cid'     => $request['cid'],
			'nums' => (int)$request['nums'],      // 接单人数限制
			'effect'  => (int)$request['effect'], // 影响力
			'stime'   => $request['stime'],  // 抢单开始时间
			'etime'   => $request['etime'],  // 抢单结束时间
		);

		/* 如果存在 则是编辑状态 */
		if ($request['oid']) {
			$result = $this->__service->update($request['oid'], $orders);
		} else {
			$result = $this->__service->insert($orders);
		}

		return true;
	}
}

// end