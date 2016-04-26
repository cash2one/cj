<?php
/**
 * voa_uda_frontend_thread_list
 * 统一数据访问/社区应用/帖子列表(后台list)
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_alllist extends voa_uda_frontend_thread_abstract {
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
		// 查询表格的条件
		$fields = array(
		    array('username',self::VAR_STR,null,null,true), 
		    array('subject',self::VAR_STR,null,null,true),
		    array('starttime', self::VAR_INT, null, null, true),
		    array('endtime', self::VAR_INT, null, null, true),
		    array('sort_type', self::VAR_INT, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}
		
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
		

		// 分页信息
		$option = array();
		$this->_get_page_option($option, $conds);
		
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
	        default:
	            $conds_order['updated'] = 'DESC';
	            break;
	    }
		
	    if (isset($conds['sort_type'])) {
	        unset($conds['sort_type']);
	    }
	    
		// 读取总数
		$this->_total = $this->_serv->count_by_conds($conds);
		// 读取
		$out = $this->_serv->list_by_conds($conds, $option, $conds_order);
		if (empty($out)) {
			$out = array();
		}

		// 判断是否需要过滤
		$this->_fmt && $this->_format($out, true);

		return true;
	}
	
	
	

}
