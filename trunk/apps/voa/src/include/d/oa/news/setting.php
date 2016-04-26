<?php
/**
 * voa_d_oa_news_setting
 * 文章
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_news_setting extends voa_d_abstruct {

	/** 数组数据 */
	const TYPE_ARRAY = 1;
	/** 标量数据 */
	const TYPE_NORMAL = 0;

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.news_setting';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'key';
		$this->_prefield = '';
		parent::__construct(null);
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public function update_setting($data) {
		if (empty($data)) {
			return true;
		}
		try {
			// 确定键名需要更新还是新增
			$list = $this->list_by_pks(array_keys($data));
			// 循环更新
			foreach ($data as $_k => $_v) {

				$ups = array();
				if (is_array($_v)) {
					// 传入的是一个数组
					$_type = self::TYPE_ARRAY;
					$_v = serialize($_v);
				} else {
					$_type = self::TYPE_NORMAL;
				}

				if (isset($list[$_k])) {
					// 更新
					if ($_type == self::TYPE_NORMAL && @unserialize($_v) !== false) {
						$_type = self::TYPE_ARRAY;
					}
					$ups[$this->_prefield.'type'] = $_type;
					$ups[$this->_prefield.'value'] = $_v;
					$this->update($_k, $ups);
				} else {
					// 添加
					if ($_type == self::TYPE_NORMAL && @unserialize($_v) !== false) {
						$_type = self::TYPE_ARRAY;
					}
					$ups[$this->_prefield.'value'] = $_v;
					$ups[$this->_prefield.'type'] = $_type;
					$ups[$this->_prefield.'key'] = $_k;
					$this->insert($ups);
				}
			}


			return true;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 判读当前用户是否可发布公告
	 *
	 * @param int $m_uid 用户id
	 * @return boolean true 允许 false 不允许
	 */
	public function get_html5_issue($m_uid) {
		$ac_id = $this->get_department_ids($m_uid); // 获得用户的关联的部门id
		$p_setting = voa_h_cache::get_instance()->get('plugin.news.setting', 'oa'); //获取公告设置
		if (!empty($ac_id) && isset($p_setting['cd_ids'])) {

			if('' != $p_setting['cd_ids']) {
				if(array_intersect($p_setting['cd_ids'], $ac_id)) {
					return true;
				}
			}
				
		}
		if(isset($p_setting['m_uids'])) {
			if(in_array($m_uid, $p_setting['m_uids'])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 找到指定用户所关联的部门ID
	 *
	 * @param number $m_uid 用户id
	 * @return array $ids 部门ID
	 */
	public function get_department_ids($m_uid) {
		$department = new voa_d_oa_member_department();
		$ids = $department->fetch_all_by_uid($m_uid);

		$all = $this->_get_all_departments($ids);
		$new = array();
		$new = array_flip(array_flip($all));
		if (!empty($new)) {
			foreach ($new as $k => $v) {
				if ($v == 0) {
					unset($new[$k]);
				}
			}
		}

		return $new;
	}

	private function _get_all_departments($cd_ids) {

		$d_departments = new voa_d_oa_common_department();
		$departments = $d_departments->fetch_all();
		$departments_ids = array_column($departments, 'cd_upid', 'cd_id');
		$all = $cd_ids;
		$this->__get_parents($cd_ids, $departments_ids, $all);

		return $all;
	}

	private function __get_parents($cd_ids, $departments_ids, &$all){
		$temp = array();
		$temp = array_intersect_key($departments_ids,$cd_ids);
		if (!empty($temp)){
			$all = array_merge($all, $temp);
			self::__get_parents(array_flip($temp), $departments_ids, $all);
		}
	}

}
