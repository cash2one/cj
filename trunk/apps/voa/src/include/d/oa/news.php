<?php
/**
 * voa_d_oa_news
 * 文章
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_news extends voa_d_abstruct {

	/** 标题最短字符 */
	const LENGTH_TITLE_MIN = 0;
	/** 标题最长字符 */
	const LENGTH_TITLE_MAX = 64;

	/** 分类--公司动态 */
	const CATGEGORY_COMPANY = 1;
	/** 分类--通知公告 */
	const CATGEGORY_NOTICE = 2;
	/** 分类--员工动态 */
	const CATGEGORY_EMPLOYEE = 3;

	/** 草稿 */
	const IS_DRAFT = 0;
	/** 已发布 */
	const IS_PUBLISH = 1;

	const NEWS_NO_CHECK = 0; //无需审核
	const NEWS_CHECKING = 1; //审核中
	const NEWS_CHECK_OK = 2; //审核通过
	const NEWS_CHECK_NO = 3; //审核未通过

	/** 消息保密关闭 */
	const IS_CLOSE = 0;
	/** 消息保密开启 */
	const IS_OPEN = 1;


	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.news';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'ne_id';
		parent::__construct(null);
	}
	
	public function list_by_complex($nca_id, $uid, $page, $limit) {
		//$ne_ids = implode(',',$ne_ids);

		$cd_ids = $this->get_department_ids($uid);
		$str = '';
		if (!empty($cd_ids)) {
			$str = " OR oar.cd_id in(".implode(',', $cd_ids).")";
		}
		$where = "AND oar.is_all=1 OR (oar.m_uid=$uid".$str.")";

		$sql = $where.' WHERE ((oan.is_publish='.self::IS_PUBLISH.') OR (oan.is_publish='.self::IS_DRAFT.' AND oan.m_uid='.$uid.')) AND oan.nca_id ='.$nca_id.' AND oan.status<'.self::STATUS_DELETE.' ORDER BY oan.updated DESC LIMIT '.$page.','.$limit;

		$sql = 'SELECT DISTINCT(oan.ne_id),oan.title,oan.is_publish,oan.m_uid,oan.updated FROM '.$this->_table.' as oan LEFT JOIN oa_news_right as oar ON oan.ne_id = oar.ne_id '.$sql;
		//var_dump($sql);
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			// 读取数据
			if (!$list = $sth->fetchAll(PDO::FETCH_ASSOC)) {
				return false;
			}
			return $list;
		}
	}

	/**
	 * 读取新闻列表
	 * @param $ne_id
	 * @param $data
	 * @param $uid
	 * @param $page
	 * @param $limit
	 * @return bool
	 */
	public function list_by_news($ne_id, $data, $uid, $page, $limit) {
		$titlewhere = '';
		//判断是否需要搜索
		if(!empty($data['keyword'])){
			$titlewhere = ' AND title LIKE \'%'.$data['keyword'].'%\'';
		}
		$where = ' WHERE ((is_publish='.self::IS_PUBLISH.') OR (is_publish='.self::IS_DRAFT.' AND m_uid='.$uid.')) AND ne_id in('.implode(',', $ne_id).') AND nca_id ='.$data['nca_id'].$titlewhere.' AND status<'.self::STATUS_DELETE.' ORDER BY updated DESC LIMIT '.$page.','.$limit;
		$sql = 'SELECT DISTINCT(ne_id), multiple, title, is_publish, m_uid, updated FROM`'.$this->_table.'`'.$where;
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			// 读取数据
			if (!$list = $sth->fetchAll(PDO::FETCH_ASSOC)) {
				return false;
			}
			return $list;
		}
	}

	/**
	 * 找到指定用户所关联的部门ID
	 * @param number $m_uid 用户id
	 * @return array $ids 部门ID
	 */
	public  function get_department_ids($m_uid) {

		$department = new voa_d_oa_member_department();
		$ids = $department->fetch_all_by_uid($m_uid);

		$all = $this->_get_all_departments($ids);
		$new = array();
		$new = array_flip(array_flip($all));
		if (!empty($new)) {
			foreach ($new as $k => $v) {
				if ($v ==0){
					unset($new[$k]);
				}
			}
		}

		return $new;
	}

	/**
	 * 获取部门
	 * @param $cd_ids
	 * @return mixed
	 */
	private function _get_all_departments($cd_ids) {

		$d_departments = new voa_d_oa_common_department();
		$departments = $d_departments->fetch_all();
		$departments_ids = array_column($departments, 'cd_upid', 'cd_id');
		$all = $cd_ids;
		$this->__get_parents($cd_ids, $departments_ids, $all);

		return $all;
	}

	/**
	 * 获取人员
	 * @param $cd_ids
	 * @param $departments_ids
	 * @param $all
	 */
	private function __get_parents($cd_ids, $departments_ids, &$all){
		$temp = array();
		$temp = array_intersect_key($departments_ids,$cd_ids);
		if (!empty($temp)){
			$all = array_merge($all, $temp);
			self::__get_parents(array_flip($temp), $departments_ids, $all);
		}
	}

}

