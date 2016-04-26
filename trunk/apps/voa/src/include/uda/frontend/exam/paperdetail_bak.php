<?php
/**
 * voa_uda_frontend_news_insert
 * 统一数据访问/新闻公告/添加新闻公告
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_exam_paperdetail extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_exam_paperdetail();
		}
	}

	public function add_detail(array $request, $args) {
		// 定义参数请求规则
		$fields = array(
			'ids' => array(
				'ids', parent::VAR_ARR,
				array($this->__service, 'validator_ids'),
				null, false
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		$tis = array();
		$orders = array();
		foreach ($this->__request['ids'] as $id) {
			if(!isset($request['orders'][$id]) 
				|| !is_numeric($request['orders'][$id])
				|| in_array($request['orders'][$id], $orders)) {
				return false;
			}

			if(!isset($request['scores'][$id]) 
				|| !is_numeric($request['scores'][$id])) {
				return false;
			}

			$tis[] = array(
				'paper_id' => $args['id'],
				'ti_id' => $id,
				'orderby' => $request['orders'][$id],
				'score' => $request['scores'][$id]
			);

			$orders[] = $request['orders'][$id];
		}

		try {
			$this->__service->begin();
			$this->__service->insert_multi($tis);
			// 计算总分
			$total_score = 0;
			foreach ($tis as $ti) {
				$total_score += $ti['score'];
			}
			// 更新试卷总分
			$s_paper = new voa_s_oa_exam_paper();
			$s_paper->update_by_conds(array('id' => $args['id']), array('total_score' => $total_score, 'ti_num' => count($tis)));

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		return true;
	}

	public function edit_detail(array $request, $args) {
		// 定义参数请求规则
		$fields = array(
			'detail_ids' => array(
				'detail_ids', parent::VAR_ARR,
				array($this->__service, 'validator_ids'),
				null, false
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		$orders = array();
		foreach ($this->__request['detail_ids'] as $id) {
			if(!isset($request['orders'][$id]) 
				|| !is_numeric($request['orders'][$id])
				|| in_array($request['orders'][$id], $orders)) {
				return false;
			}

			$orders[] = $request['orders'][$id];
		}

		try {
			$oldetails = $this->__service->list_by_paperid($args['id']);
			$deleteids = array();
			$total_score = $ti_num = 0;
			foreach($oldetails as $oldetail) {
				$orderby = $request['orders'][$oldetail['id']];
				if(!in_array($oldetail['id'], $this->__request['detail_ids'])) {
					$deleteids[] = $oldetail['id'];
				} else {
					if($orderby != $oldetail['orderby']) {
						$this->__service->update_by_conds(array('id' => $oldetail['id']), array('orderby' => $orderby));
					}
					$total_score += $oldetail['score'];
					$ti_num++;
				}
			}

			if(!empty($deleteids)) {
				$this->__service->real_delete_details($deleteids);
				// 更新试卷总分及题数
				$s_paper = new voa_s_oa_exam_paper();
				$s_paper->update_by_conds(array('id' => $args['id']), array('total_score' => $total_score, 'ti_num' => $ti_num));
			}
		} catch (Exception $e) {
			$this->__service->rollBack();
			return $this->set_errmsg(voa_errcode_oa_exam::DELETE_PAPER_FAILED);
		}
		return true;
	}

	public function delete_detail($ids) {
		try {
			$this->__service->begin();
			$this->__service->delete($ids);
			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollBack();
			return $this->set_errmsg(voa_errcode_oa_exam::DELETE_PAPER_FAILED);
		}
		return true;
	}
}
