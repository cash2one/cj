<?php
/**
 * voa_uda_frontend_campaign_customcols
 * 活动推广 自定义字段
 * User: Muzhitao
 * Date: 2015/8/26 0026
 * Time: 14:02
 */

class voa_uda_frontend_campaign_customcols extends voa_uda_frontend_campaign_base {

	/**
	 * service 类
	 */
	private $__service = null;

	public function __construct() {

		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_campaign_customcols();
		}
	}

	/**
	 * 添加自定义字段
	 *
	 * @param array $request
	 * @param $result
	 * @param $session
	 * @return bool
	 * @throws service_exception
	 */
	public function add_customcols(array $request, $is_push = 1) {

		$data = array();

		// 序列化自定义字段
		if (isset($request['cols'])) {
			$data['other'] = serialize($request['cols']);
		} else {
			$data['other'] = serialize(array());
		}

		$data['cid'] = $request['cid'];
		$data['is_push'] = $is_push;

		$conds = array('cid' => $request['cid']);

		$ls = $this->__service->get_by_conds($conds);
		$s = new voa_d_oa_campaign_campaign();

		// 如果存在数据 则是更新数据
		if (!empty($ls)) {
			$this->__service->update($ls['afcc_id'], $data);

			// 更新活动主表的状态 设置为发布
			$push['is_push'] = 1;
			$s->update($request['cid'], $push);
			return true;
		}
		// 插入数据
		$re = $this->__service->insert($data);

		/* 如果操作成功，则更新活动的状态为发布 */
		if ($re && $is_push != 0) {
			$r = new voa_s_oa_campaign_orders();

			// 更新活动主表的状态 设置为发布
			$push['is_push'] = 1;
			$s->update($request['cid'], $push);

			// 更新接单详情中状体 设置为发布
			$temp['cid'] = $request['cid'];
			$r->update_by_conds($temp, $push);
		}

		return true;
	}

	/**
	 * 编辑自定义字段
	 *
	 * @param array $request
	 * @param $result
	 * @param $session
	 * @return bool
	 */
	public function edit_customcols(array $request, &$result, $session = null, $is_push = 1) {

		$data = array();

		// 序列化自定义字段
		if (isset($request['cols'])) {
			$data['other'] = serialize($request['cols']);
		} else {
			$data['other'] = serialize(array());
		}

		$data['cid'] = $request['cid'];
		$data['is_push'] = $is_push;

		// 是否存在数据
		$conds = array('cid' => $request['cid']);
		$ls = $this->__service->get_by_conds($conds);

		// 判断当前是更新还是新增数据
		if (!empty($ls)) {
			$result = $this->__service->update($ls['afcc_id'], $data);
		} else {
			$result = $this->__service->insert($data);
		}

		// 更新活动的状态为正式发布
		if ($result) {
			$s = new voa_d_oa_campaign_campaign();
			$r = new voa_s_oa_campaign_orders();

			// 更新活动主表的状态 设置为发布
			$push['is_push'] = 1;
			$s->update($request['cid'], $push);

			// 更新接单详情中状体 设置为发布
			$temp['cid'] = $request['cid'];
			$r->update_by_conds($temp, $push);
		}

		return true;
	}

}

// end