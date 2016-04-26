<?php
/**
 * voa_uda_frontend_thread_list
 * 统一数据访问/社区应用/帖子列表(前端有权限list)
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_list extends voa_uda_frontend_thread_abstract {
	// 列表
	protected $_pu_serv = null;

	public function __construct() {
		parent::__construct();
// 		$this->_pu_serv = new voa_s_oa_thread_permit_user();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {
		$this->_params = $in;
		// 查询条件
		$fields = array(
			array('uid', self::VAR_INT, null, null, true),
		    array('likes', self::VAR_INT, null, null, true),
		    array('replies', self::VAR_INT, null, null, true),
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
		if(!empty($conds['likes'])){
		    $conds['likes>=?']= $conds['likes'];
		    unset($conds['likes']);
		}
	
		if(!empty($conds['replies'])){
		    $conds['replies>=?']= $conds['replies'];
		    unset($conds['replies']);
		}
		
		// 读取总数
		$this->_total = $this->_serv->count_by_conds($conds);

		// 读取
		$out = $this->_serv->list_by_conds($conds, $option, array('created' => 'DESC'));
		if (empty($out)) {
			$out = array();
		}
		// 判断是否需要过滤
		$this->_fmt && $this->_format($out, true);

		return true;
	}

}
