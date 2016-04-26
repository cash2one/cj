<?php
/**
 * 日志/记录主题表
 * $Author$
 * $Id$
 */

class voa_d_oa_thread extends voa_d_abstruct {

	// 仅自己可见
	const FRIEND_ME = 0;
	// 指定用户可见
	const FRIEND_SOME = 1;
	// 分享给所有人
	const FRIEND_ALL = 2;

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.thread';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'tid';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}

	/**
	 * 根据条件统计总数
	 * @param array $conds 查询条件
	 * @param mixed $page_option 分页参数
	 * @param array $orderby 排序
	 */
	public function count_share_by_conds($conds, $orderby = array()) {
		try {
			$uids = (array)$conds['uid'];
			$uids[] = 0;
			unset($conds['uid']);
			$conds['b.uid'] = $uids;
			// 条件
			$this->_parse_conds($conds);

			// 只查询未删除的
			$this->_condi('a.'.$this->_prefield.'status<?', self::STATUS_DELETE);
			$this->_condi('b.'.$this->_prefield.'status<?', voa_d_oa_thread_permit_user::STATUS_DELETE);

			// 排序
			$orderby = empty($orderby) ? array('`a`.`updated`' => 'DESC') : $orderby;
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			$sql = $this->_find_ciii_sql('COUNT(a.tid)');
			// 执行
			$sth = null;
			if ($this->_execute($sql, $this->_bind_params, $sth)) {
				return $sth->fetchColumn();
			}

			return false;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

	}

	/**
	 * 根据条件读取主题
	 * @param array $conds 查询条件
	 * @param mixed $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 * @return Ambigous
	 */
	public function list_share_by_conds($conds, $page_option, $orderby = array()) {

		try {
			$uids = $conds['uid'];
			$uids[] = 0;
			$conds['uid'] = $uids;
			// 条件
			$this->_parse_conds($conds);

			// 只查询未删除的
			$this->_condi('a.'.$this->_prefield.'status<?', self::STATUS_DELETE);
			$this->_condi('b.'.$this->_prefield.'status<?', voa_d_oa_thread_permit_user::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);

			// 排序
			$orderby = empty($orderby) ? array('`a`.`updated`' => 'DESC') : $orderby;
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all('a.*', null, '_find_ciii_sql');
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取查询的sql
	 * @param string $fields
	 * @return string
	 */
	protected function _find_ciii_sql($fields = '') {

		$fields = empty($fields) ? $this->_fields : $fields;
		if (empty($fields)) {
			$fields = "a.*";
		} else {
			// need fixed
			$fields = (is_string($fields) ? $fields : implode(',', $fields));
		}

		$sql = "SELECT $fields FROM ".$this->_table('thread_permit_user')." AS b"
			 . " LEFT JOIN ".$this->_table." AS a"
			 . " ON b.tid=a.tid "
			 . $this->_where()." ".$this->_g_o_l();

		return $sql;
	}

}
