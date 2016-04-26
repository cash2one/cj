<?php
/**
 * voa_uda_frontend_exam_ti
 * 统一数据访问/试题相关操作
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_uda_frontend_exam_ti extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_exam_ti();
		}
	}

	/**
	 * 新增一个题目
	 * @param array $request 请求的参数
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function add_ti(array $request, $args) {
		// 定义参数请求规则
		$fields = array(
			'type' => array(
				'type', parent::VAR_INT,
				array($this->__service, 'validator_type'),
				null, false
			),
			'orderby' => array(
				'orderby', parent::VAR_INT,
				array($this->__service, 'validator_orderby'),
				null, false
			),
			'score' => array(
				'score', parent::VAR_INT,
				array($this->__service, 'validator_score'),
				null, false
			),
			// 标题
			'title' => array(
				'title', parent::VAR_STR,
				array($this->__service, 'validator_title'),
				null, false,
			),
			'answer' => array(
				'answer', parent::VAR_STR,
				array($this->__service, 'validator_answer'),
				null, false,
			),
		);

		if($request['type'] == voa_d_oa_exam_ti::TYPE_DAN || $request['type'] == voa_d_oa_exam_ti::TYPE_DUO) {
			$fields['options'] = array(
				'options', parent::VAR_STR,
				array($this->__service, 'validator_options'),
				null, false,
			);
		}

		if(!$args['id']) {
			$fields['tiku_id'] = array(
				'tiku_id', parent::VAR_INT,
				array($this->__service, 'validator_tiku_id'),
				null, false,
			);
		}

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		try {
			$this->__service->begin();

			$ti = array(
				'type' => $this->__request['type'],
				'orderby' => $this->__request['orderby'],
				'score' => $this->__request['score'],
				'title' => $this->__request['title'],
				'options' => isset($this->__request['options']) ? $this->__request['options'] : '',
				'answer' => $this->__request['answer'],
			);

			if($args['id']) {
				$this->__service->update($args['id'], $ti);
			} else {
				// 重名不提交
				/*
				$exist_ti=$this->__service->get_by_conds(array('title'=>$ti['title']));
				if($exist_ti){
					return false;
				}
				*/

				$ti['tiku_id'] = $this->__request['tiku_id'];
				$this->__service->insert($ti);
				
			}
			$s_tiku = new voa_s_oa_exam_tiku();
			$s_tiku->update_count($request['tiku_id'], $ti['type']);

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		return true;
	}

	public function delete_ti($ids) {
		try {
			$this->__service->begin();
			$tis = $this->__service->list_by_pks($ids);
			
			$this->__service->delete($ids);

			$s_tiku = new voa_s_oa_exam_tiku();
			foreach($tis as $ti) {
				$s_tiku->update_count($ti['tiku_id'], $ti['type']);
			}
			
			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollBack();
			return $this->set_errmsg(voa_errcode_oa_exam::DELETE_TI_FAILED);
		}
		return true;
	}

	/**
	 * 根据条件查找题目列表
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 */
	public function list_ti(&$result, $conds, $pager) {
		$result['list'] =  $this->_list_ti_by_conds($conds, $pager);
		$result['total'] = $this->_count_ti_by_conds($conds);
		return true;
	}

	/**
	 * 根据条件查找题目
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 * @return array $list
	 */
	protected function _list_ti_by_conds($conds, $pager) {
		$list = array();
		$list = $this->__service->list_by_conds($conds, $pager, array('orderby'=>'ASC', 'id' => 'DESC'));
		return $list;
	}

	/**
	 * 根据条件计算题目数据数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_ti_by_conds($conds) {
		$total = $this->__service->count_by_conds($conds);
		return $total;
	}
}
