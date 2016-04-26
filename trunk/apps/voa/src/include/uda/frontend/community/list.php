<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/20
 * Time: 15:36
 */
class voa_uda_frontend_community_list extends voa_uda_frontend_community_abstract {
	// 列表
	protected $_serv = null;

	public function __construct() {
		parent::__construct();
		if($this->_serv == null) {
			$this->_serv = new voa_s_oa_community();
		}
	}



	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($request, &$result) {
		$this->_params = $request;
		// 查询条件
		$fields = array(
			array('username',self::VAR_STR,null,null,true),
			array('subject',self::VAR_STR,null,null,true),
			array('starttime', self::VAR_INT, null, null, true),
			array('tid', self::VAR_INT, null, null, true),
			array('endtime', self::VAR_INT, null, null, true),
			array('sort_type', self::VAR_INT, null, null, true),
			array('status', self::VAR_INT, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		$option = array();
		$this->_get_page_option($option, $conds);
		if (!empty($conds['username'])) {
			$conds['username like ?'] = "%".$conds['username']."%";
		}

		if (!empty($conds['subject'])) {
			$conds['subject like ?'] = "%".$conds['subject']."%";
		}

		if (!empty($conds['starttime'])) {
			$conds['created>?'] = $conds['starttime'];
		}

		if (!empty($conds['endtime'])) {
			$conds['created<?'] = $conds['endtime'];

		}
		if (empty($conds['tid'])) {
			unset($conds['tid']);
		}

		if (isset($conds['username'])) {
			unset($conds['username']);
		}

		if (isset($conds['starttime'])) {
			unset($conds['starttime']);
		}

		if (isset($conds['endtime'])) {
			unset($conds['endtime']);
		}

		if (isset($conds['subject'])) {
			unset($conds['subject']);
		}
		$conds_order = array();
		switch ($conds['sort_type']){
			case 1:
				$conds_order['created'] = 'DESC';
				break;
			case 2:
				$conds_order['replies'] = 'DESC';
				break;
			case 3:
				$conds_order['replies'] = 'ASC';
				break;
			case 4:
				$conds_order['likes'] = 'DESC';
				break;
			case 5:
				$conds_order['likes'] = 'ASC';
				break;
			case 6:
				$conds_order['browses'] = 'DESC';
				break;
			case 7:
				$conds_order['browses'] = 'ASC';
				break;
			default:
				$conds_order['created'] = 'DESC';
				break;
		}

		switch ($conds['status']){
			case 1:
				$conds['draft'] = 0;
				break;
			case 2:
				$conds['draft'] = 1;
				break;
			default:
				break;
		}

		if (isset($conds['status'])) {
			unset($conds['status']);
		}

		if (isset($conds['sort_type'])) {
			unset($conds['sort_type']);
		}

		// 读取总数
		$this->_total = $this->_serv->count_by_conds($conds);

		// 读取
		$result = $this->_serv->list_by_conds($conds, $option, $conds_order);
		if (empty($result)) {
			return $result = array();
		}
		foreach ($result as $v) {
			if ($v['attach_id']) {
					$at_ids = explode(',', $v['attach_id']);
					$at_id = $at_ids[0];
					$v['attach_url'] = voa_h_attach::attachment_url($at_id);
					$v['attach_num'] = count($at_ids);
			}
			$result[$v['cid']] = $v;
		}
		// 判断是否需要过滤
		//$this->_fmt && $this->_format($result);

		return true;
	}

}
