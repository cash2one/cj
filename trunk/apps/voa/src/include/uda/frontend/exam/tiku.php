<?php
/**
 * voa_uda_frontend_exam_tiku
 * 统一数据访问/题库相关操作
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_uda_frontend_exam_tiku extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_exam_tiku();
		}
	}

	/**
	 * 新增一个题库
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)新增的新闻公告
	 * @param array $args 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function add_tiku(array $request, &$result, $args) {
		// 定义参数请求规则
		$fields = array(
			// 标题
			'name' => array(
				'name', parent::VAR_STR,
				array($this->__service, 'validator_name'),
				null, false,
			),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		try {
			$this->__service->begin();

			$tiku = array(
				'name' => $this->__request['name']
			);

			if($args['id']) {
				$this->__service->update($args['id'], $tiku);

				$result = $tiku;
				$result['id'] = $args['id'];

			} else {
				$tiku['username'] = $args['username'];
				$result=$this->__service->insert($tiku);
			}

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		return true;
	}

	public function delete_tiku($ids) {
		try {
			$this->__service->begin();
			$s_ti = new voa_s_oa_exam_ti();

			$conds = array(
				'tiku_id' => $ids
			);
			$s_ti->delete_by_conds($conds); 
			$this->__service->delete($ids);
			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollBack();
			return $this->set_errmsg(voa_errcode_oa_exam::DELETE_TIKU_FAILED);
		}
		return true;
	}

	public function list_all_tiku() {
		return $this->__service->list_all(null, array('id' => 'DESC'));
	}

	/**
	 * 根据条件查找题库列表
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 */
	public function list_tiku(&$result, $conds, $pager) {
		$result['list'] =  $this->_list_tuku_by_conds($conds, $pager);
		$result['total'] = $this->_count_tuku_by_conds($conds);
		return true;
	}

	/**
	 * 根据条件查找题库
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 * @return array $list
	 */
	protected function _list_tuku_by_conds($conds, $pager) {
		$list = array();
		$list = $this->__service->list_by_conds($conds, $pager, array('updated' => 'DESC'));
		return $list;
	}

	/**
	 * 根据条件计算题库数据数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_tuku_by_conds($conds) {
		$total = $this->__service->count_by_conds($conds);
		return $total;
	}
}
