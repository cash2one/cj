<?php
/**
 * voa_d_oa_nvote_setting.
 * 投票调研选项
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:16
 */

class voa_d_oa_nvote_setting extends voa_d_abstruct {
	/** 数组数据 */
	const TYPE_ARRAY = 1;
	/** 标量数据 */
	const TYPE_NORMAL = 0;
    // 初始化
    public function __construct() {

        // 表名
        $this->_table = 'orm_oa.nvote_setting';
        // 允许的字段
        $this->_allowed_fields = array();
        // 必须的字段
        $this->_required_fields = array();
        // 主键
        $this->_pk = 'key';
        // 字段前缀
        $this->_prefield = '';

        parent::__construct();
    }

    /**
     * 更新多个变量值
     * @param array $data array(key=>value, key2=>value2, ...)
     * @return boolean
     */
    public function update_setting($data) {

        try {
            // 更新时间
            if (!isset($data[$this->_prefield.'updated'])) {
                $data[$this->_prefield.'updated'] = startup_env::get('timestamp');
            }

            // 更新状态值
            if (!isset($data[$this->_prefield.'status'])) {
                $data[$this->_prefield.'status'] = self::STATUS_UPDATE;
            }

            // 更新基础数据
            $ups = array(
                $this->_prefield.'updated' => startup_env::get('timestamp'),
                $this->_prefield.'status' => self::STATUS_UPDATE
            );
            // 循环更新
            foreach ($data as $_k => $_v) {
                $this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
                $this->_condi($this->_prefield.'key=?', $_k);
	            $ups[$this->_prefield.'value=?'] = $_v;
                $this->_update($ups);
            }

            return true;
        } catch (Exception $e) {
            logger::error($e);
            throw new service_exception($e->getMessage(), $e->getCode());
        }
    }

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public function update_settings($data) {
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
					$ups[$this->_prefield . 'type'] = $_type;
					$ups[$this->_prefield . 'value'] = $_v;
					$this->update($_k, $ups);
				} else {
					// 添加
					if ($_type == self::TYPE_NORMAL && @unserialize($_v) !== false) {
						$_type = self::TYPE_ARRAY;
					}
					$ups[$this->_prefield . 'value'] = $_v;
					$ups[$this->_prefield . 'type'] = $_type;
					$ups[$this->_prefield . 'key'] = $_k;
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
	 * @param int $m_uid 用户id
	 * @return boolean true 允许 false 不允许
	 */
	public function get_html5_issue($m_uid) {

		$ac_id = $this->get_department_ids($m_uid); // 获得用户的关联的部门id
		$p_setting = voa_h_cache::get_instance()->get('plugin.nvote.setting', 'oa'); //获取投票设置


		// 如果当前是全部可操作
		if ($p_setting['all'] == 1) {
			return true;
		}

		/* 判断当前用户所属的部门 */
		if (!empty($ac_id) && !empty($p_setting['cd_ids'])) {
			if(array_intersect($ac_id, $p_setting['cd_ids'])) {
				return true;
			}
		}

		/* 判断当前用户是否在用户权限里 */
		if(!empty($p_setting['m_uids'])) {
			if(in_array($m_uid, $p_setting['m_uids'])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 找到指定用户所关联的部门ID
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
