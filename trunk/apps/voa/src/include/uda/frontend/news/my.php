<?php
/**
 * voa_uda_frontend_news_my
 * 统一数据访问/新闻公告/我的公告列表(供前端使用)
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_my extends voa_uda_frontend_news_abstract {

	/** service 类 */
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if ($this->__service === null) {
			$this->__service = new voa_s_oa_news();
		}
	}

	/**
	 * 根据条件查找新闻公告列表
	 * @param array $request 条件数组
	 * @param int|array $result 分页参数
	 */
	public function list_my_news(array $request, array &$result) {
		// 定义参数请求规则
		$fields = array(
			// 用户ID
			'm_uid' => array(
				'm_uid', parent::VAR_INT,
				array($this->__service, 'validator_m_uid'),
				null, false
			),
			// 分类ID
			'nca_id' => array(
				'nca_id', parent::VAR_INT,
				array($this->__service, 'validator_nca_id'),
				null, false
			),
			// 分页
			'page' => array(
				'page', parent::VAR_INT,
				array($this->__service, 'validator_page'),
				null, false
			),
			// 每页显示数量
			'limit' => array(
				'limit', parent::VAR_INT,
				array($this->__service, 'validator_limit'),
				null, false
			),
			'current' => array(
				'current', parent::VAR_INT,
				null,null,false
			),
			'keyword' => array(
				'keyword', parent::VAR_STR,
				null,null,false
			)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		$nca_id = $this->__request['nca_id'];//分类id
		$m_uid = $this->__request['m_uid'];
		$page = $this->__request['page'];
		$limit = $this->__request['limit'];
		$uid = $this->__request['current'];
		//条件组合
		$data = array(
			'nca_id' => $this->__request['nca_id'],
			'keyword' => $this->__request['keyword']
		);
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		/* 取得单个用户有阅读权限的公告*/
		$s_news_right = new voa_s_oa_news_right();
		$rights = $s_news_right->list_rights_for_single_user($nca_id, $m_uid);
		$result = array();
		if ($rights) {
			$mylist = array();
			$ne_ids = array_column($rights, 'ne_id');
			$result = $this->__service->list_by_news($ne_ids, $data, $m_uid, $start, $limit);
			$this->__format_list($result, $m_uid);
			$this->__more_list($result, $mylist);
			$result = $mylist;
		}
		return true;
	}

	/**
	 * 格式化数据列表
	 * @param array $list 列表（引用）
	 */
	private function __format_list(&$list, $m_uid) {
		if ($list) {
			//获取阅读情况
			$ne_ids = array_column($list, 'ne_id');
			$s_read = new voa_s_oa_news_read();
			$records = $s_read->list_by_conds(array('ne_id' => $ne_ids, 'm_uid' => $m_uid));
			$read = array();
			if (!empty($records)){
				$read = array_column($records, 'm_uid', 'ne_id');
			}
			foreach ($list as $k => &$v) {
				$v['title'] = rsubstr(rhtmlspecialchars($v['title']),40);
				$v['updated'] = rgmdate($v['updated'], 'Y-m-d H:i');
				$v['is_read'] = isset($read[$v['ne_id']]) ? 1 : 0;//判读用户是否阅读
			}
		}
	}

	/**
	 * 格式化合并多条新闻
	 * @param $request
	 * @param $result
	 * @return bool
	 */
	private function __more_list($request, &$result) {
		if(empty($request)){
			return $result;
		}
		foreach($request as $k => $v){
			$ok = true;
			//判断是否是多条新闻
			if($v['multiple'] > 0){
				if(!empty($result)){
					foreach ($result as $key => $value) {
						//判断是否是同组新闻
						if(isset($value[0]) && $value[0]['multiple'] == $v['multiple']){
							$ok = false;
							$result[$key][] = $v;
							break;
						}
					}
				}
				if($ok){
					$result[][] = $v;
				}

			}else{
				$result[] = $v;
			}
		}
		return true;
	}

}
