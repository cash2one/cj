<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/11/17
 * Time: 17:29
 */

class voa_uda_frontend_banner_insert extends voa_uda_frontend_banner_base {

	public function __construct() {

		parent::__construct();
		$this->__service = new voa_s_oa_banner();
	}

	/**
	 * 入库操作
	 * @param $in
	 * @param $out
	 * @param object $session
	 * @return bool
	 */
	public function addact($in, &$out=array()) {
		$data = array();

		// 提交的值进行过滤
		if (!$this->getact($in, $data)) {
			return false;
		}
		//判断首页是否重复
		if (!$this->_has_banner($in)) {
			$this->errmsg(10002, '重复添加!!');
			return false;
		}
		//更新活动order+1
		$this->__service->update_order_all();
		// 社群活动入库
		$out = $this->__service->insert($data);

		return true;
	}

	protected function _has_banner($in) {

		$data = array(
			'handpicktype' => $in['category'],
			'lid' => $in['list']
		);
		if($this->__service->get_by_conds($data)) {
			return false;
		}

		return true;
	}

	/**
	 * 处理提交的数据
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	public function getact($in, &$out) {

		//获取数据
		if (!empty($in)) {
			$data['handpicktype'] = $in['category'];
			$data['lid'] = $in['list'];
			$data['attid'] = $in['cover'];
			$data['title'] = $in['title'];
			$data['created'] = $in['created'];
		}

		$fields = array(
			'handpicktype' => array('handpicktype', parent::VAR_INT, null, null, false),
			'lid' => array('lid', parent::VAR_INT, null, null, false),
			'attid' => array('attid', parent::VAR_INT, null, null, false),
			'title' => array('title', parent::VAR_STR, null, null, false),
			'created' => array('b_created', parent::VAR_INT, null, null, false)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $data)) {
			return false;
		}

		$out = $this->__request;
		if (empty($out['handpicktype'])) {
			$this->errmsg(10001, '类型选择不能为空');
			return false;
		}
		if (empty($out['lid'])) {
			$this->errmsg(10002, '具体内容不能为空');
			return false;
		}
		//$result = $this->__service->list_by_conds('',1,$myorder);

		$out['b_order'] = 1;

		return true;
	}

	public function update($in, &$out) {
		$bid = $in['bid'];

		$fields = array(
			'handpicktype' => array('handpicktype', parent::VAR_INT, null, null, false),
			'title' => array('title', parent::VAR_STR, null, null, false),
			'lid' => array('lid', parent::VAR_INT, null, null, false),
			'attid' => array('attid', parent::VAR_INT, null, null, false)
		);
		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $in)) {
			return false;
		}

		$data = $this->__request;
		if (empty($data['handpicktype'])) {
			$this->errmsg(10001, '类型选择不能为空');
			return false;
		}
		if (empty($data['lid'])) {
			$this->errmsg(10002, '具体内容不能为空');
			return false;
		}
		$out = $this->__service->update_by_conds($bid, $data);
		return true;
	}
}
