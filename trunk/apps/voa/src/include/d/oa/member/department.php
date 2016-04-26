<?php
/**
 * voa_d_oa_member_department
 * 用户与部门关联表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_member_department extends dao_mysql {
    /** 表名 */
    public static $__table = 'oa.member_department';
    /** 主键 */
    private static $__pk = 'md_id';
    private static $__fields = array();
    /** 字段前缀 */
    private static $__prefix = 'md_';
    /** 正常 */
    const STATUS_NORMAL = 1;
    /** 已更新过 */
    const STATUS_UPDATE = 2;
    /** 已删除 */
    const STATUS_REMOVE = 3;

    /**********************/

    /**********************/

    /**
     * <p><strong style="color:blue">【D】获取带前缀的字段名</strong></p>
     * @author Deepseath
     * @param string $field 无前缀的字段名
     * @return string 带前缀的字段名
     */
    public static function fieldname($field) {
        return self::$__prefix.$field;
    }

    /**
     * <p><strong style="color:blue">【D】根据主键值获取单条数据</strong></p>
     * @author Deepseath
     * @param int $value 主键值
     */
    public static function fetch($value, $shard_key = array()) {
        return parent::_fetch_first(self::$__table,
            "SELECT * FROM %t WHERE %i='%d' AND %i<'%d' LIMIT 1",
            array(self::$__table, self::$__pk, $value, self::fieldname('status'), self::STATUS_REMOVE), $shard_key
        );
    }

    /**
     * <p><strong style="color:blue">【D】根据主键更新</strong></p>
     * @author Deepseath
     * @param array $data 需要更新的数据数组
     * @param string|number $value 主键值
     */
    public static function update($data, $value, $shard_key = array()) {
        if (empty($data[self::fieldname('status')])) {
            $data[self::fieldname('status')] = self::STATUS_UPDATE;
        }

        if (empty($data[self::fieldname('update')])) {
            $data[self::fieldname('updated')] = startup_env::get('timestamp');
        }

        return parent::_update(self::$__table, $data, array(self::$__pk => $value), false, false, $shard_key);
    }

    /**
     * <p><strong style="color:blue">【D】根据主键删除</strong></p>
     * @author Deepseath
     * @param array|number $value 主键值
     */
    public static function delete($value, $shard_key = array()) {
        return self::delete_by_conditions(array(self::$__pk => $value), $shard_key);
    }

    /**
     * <p><strong style="color:blue">【D】获取表字段默认数据</strong></p>
     * @author Deepseath
     * @return array
     */
    public static function fetch_all_field($shard_key = array()) {
        return parent::_fetch_all_field(self::$__table, $shard_key);
    }

    /**
     * <p><strong style="color:blue">【D】读取所有数据</strong></p>
     * @author Deepseath
     * @param int $start
     * @param int $limit
     * @return array
     */
    public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
        return parent::_fetch_all(self::$__table,
            "SELECT * FROM %t WHERE %i<'%d' ".db_help::limit($start, $limit),
            array(self::$__table, self::fieldname('status'), self::STATUS_REMOVE), self::$__pk, $shard_key
        );
    }

    /**
     * <p><strong style="color:blue">【D】统计所有未删除的记录总数</strong></p>
     * @author Deepseath
     * @return number
     */
    public static function count_all($shard_key = array()) {
        return (int) parent::_result_first(self::$__table,
            "SELECT COUNT(%i) FROM %t WHERE %i<'%d'",
            array(self::$__pk, self::$__table, self::fieldname('status'), self::STATUS_REMOVE), $shard_key
        );
    }
    /**
     * 根据查询条件拼凑 sql 条件
     * @param array $conditions 查询条件
     *  $conditions = array(
     *  	'field1' => '查询条件', // 运算符为 =
     *  	'field2' => array('查询条件', '查询运算符'),
     *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
     *  	...
     *  );
     */
    public static function parse_conditions($conditions = array()) {
        $wheres = array();
        /** 遍历条件 */
        foreach ($conditions as $field => $v) {
            /** 非当前表字段 */
            if (!empty(self::$__fileds) && !in_array($field, self::$__fields)) {
                continue;
            }

            $f_v = $v;
            $gule = '=';
            /** 如果条件为数组, 则 */
            if (is_array($v)) {
                $f_v = $v[0];
                $gule = empty($v[1]) ? '=' : $v[1];
            }

            $wheres[] = db_help::field($field, $f_v, $gule);
        }

        return empty($wheres) ? 1 : implode(' AND ', $wheres);
    }

    /**
     * 根据条件计算总数
     * @param array $conditions
     * @return number
     */
    public static function count_by_conditions($conditions, $shard_key = array()) {
        return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND md_status<%d", array(
            self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
        ), $shard_key);
    }

    /**
     * <p><strong style="color:blue">【D】数据入库</strong></p>
     * @author Deepseath
     * @param array $data 入库数据数组
     * @param boolean $return_insert_id
     * @param boolean $replace
     */
    public static function insert($data, $return_insert_id = false, $replace = false, $shard_key = array()) {
        if (empty($data[self::fieldname('status')])) {
            $data[self::fieldname('status')] = self::STATUS_NORMAL;
        }

        if (empty($data[self::fieldname('created')])) {
            $data[self::fieldname('created')] = startup_env::get('timestamp');
        }

        if (empty($data[self::fieldname('updated')])) {
            $data[self::fieldname('updated')] = $data[self::fieldname('created')];
        }

        return parent::_insert(self::$__table, $data, $return_insert_id, $replace, false, $shard_key);
    }

    /**
     * <p><strong style="color:blue">【D】根据条件更新</strong></p>
     * @author Deepseath
     * @param array $data 需要更新的数据数组
     * @param array|string $conditions 更新条件
     */
    public static function update_by_conditions($data, $conditions, $shard_key = array()) {
        if (empty($data[self::fieldname('status')])) {
            $data[self::fieldname('status')] = self::STATUS_UPDATE;
        }

        if (empty($data[self::fieldname('update')])) {
            $data[self::fieldname('updated')] = startup_env::get('timestamp');
        }

        return parent::_update(self::$__table, $data, $conditions, false, false, $shard_key);
    }

    /**
     * <p><strong style="color:blue">【D】根据条件删除 </strong></p>
     * @author Deepseath
     * @param array $conditions 删除条件
     * @return void
     */
    public static function delete_by_conditions($conditions, $shard_key = array()) {
        return self::update_by_conditions(array(
            self::fieldname('status') => self::STATUS_REMOVE,
            self::fieldname('deleted') => startup_env::get('timestamp')
        ), $conditions, $shard_key);
    }

    /**
     * (d) 根据条件找到一条记录
     * @param array $conditions
     * @return array
     */
    public static function fetch_by_conditions($conditions, $shard_key = array()) {
        $where = array();
        foreach ($conditions AS $k=>$v) {
            $where[] = db_help::field($k, $v);
        }

        $where = $where ? implode(' AND ', $where) : 1;
        return parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1",
            array(self::$__table, $where, db_help::field(self::fieldname('status'), self::STATUS_REMOVE, '<')), $shard_key
        );
    }

    /**********************************************/

    /**
     * 删除指定用户的部门关系
     * @param number $m_uid
     * @param array $shard_key
     * @return Ambigous <void, boolean>
     */
    public static function delete_by_m_uid($m_uid, $shard_key = array()) {
        return self::delete_by_conditions(array('m_uid' => $m_uid), $shard_key);
    }

    /**
     * 找到指定用户所关联的部门ID
     * @param number $m_uid
     * @param array $shard_key
     * @return array
     */
    public static function fetch_all_by_uid($m_uid = 0, $shard_key = array()) {
        $cd_ids = array();
        $list = (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i ", array(
            self::$__table, db_help::field('m_uid', $m_uid), db_help::field('md_status', self::STATUS_REMOVE, '<')
        ), self::$__pk, $shard_key);
        foreach ($list as $md) {
            $cd_ids[$md['cd_id']] = $md['cd_id'];
        }

        return $cd_ids;
    }


    /**
     * 找到指定用户所关联的部门ID
     * @param number $m_uid
     * @param array $shard_key
     * @return array
     */
    public static function fetch_all_field_by_uid($m_uid = 0, $shard_key = array()) {
        $cd_ids = array();
        $list = (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i ", array(
            self::$__table, db_help::field('m_uid', $m_uid), db_help::field('md_status', self::STATUS_REMOVE, '<')
        ), self::$__pk, $shard_key);

        $cd_mps = array();
        foreach ($list as $md) {
            $cd_mps[$md['cd_id']] = $md;
        }
        return $cd_mps;
    }

    /**
     * <strong style="color:blue">【D】根据条件列出数据</strong>
     * @param array $conditions 查询条件，
     * @see self::parse_conditions
     * @param array $orderby 排序方式
     * @param number $start
     * @param number $limit
     * @return array
     */
    public static function fetch_all_by_conditions($conditions, $orderby, $start = 0, $limit = 0, $shard_key = array()) {
        if (empty($orderby)) {
            $orderby = array(self::$__pk => 'DESC');
        }
        return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i %i %i", array(
            self::$__table, self::parse_conditions($conditions), db_help::field('md_status', self::STATUS_REMOVE, '<'), db_help::orders($orderby), db_help::limit($start, $limit)
        ), self::$__pk, $shard_key);
    }

    /**
     * <strong style="color:blue">【D】获取用户id多条SQL UNION</strong>
     * @param array $conditions 查询条件，
     * @see self::parse_conditions
     * @return array
     */
    public static function fetch_muid_multi_sql_union($conditions, $shard_key = array()) {

	    if (empty($conditions)) {
		    return array();
	    }

        $sql = "SELECT `m_uid` FROM %t WHERE %i AND %i ";

        $delete = db_help::field('md_status', self::STATUS_REMOVE, '<');
        $sql_list = array();
        $params = array();

        foreach ($conditions as $condition) {
            $sql_list[] = $sql;
            $params[] = self::$__table;
            $params[] = self::parse_conditions($condition);
            $params[] = $delete;
        }

        return (array)parent::_fetch_all(self::$__table, implode(' UNION ', $sql_list) , $params);
    }

    /**
     * 通过部门id统计部门人数
     * @param array $cdids 部门id数组
     * @param array $shard_key
     * @return array
     */
    public static function list_count_by_cdid($cdids, $shard_key = array()) {

    	$params = array(self::$__table);
    	$wheres = array();
    	if (!empty($cdids)) {
    		$wheres[] = 'cd_id IN (%n)';
    		$params[] = $cdids;
    	}

    	$params[] = self::STATUS_REMOVE;
    	$wheres[] = 'md_status<%d';

    	return (array)parent::_fetch_all(self::$__table, "SELECT COUNT(*) AS ct, cd_id FROM %t WHERE " . implode(" AND ", $wheres) . " GROUP BY cd_id", $params);
    }

    public static function count_by_cdid($cdids, $shard_key = array()) {

    	$params = array(self::$__table);
    	$wheres = array();
    	$wheres[] = 'cd_id IN (%n)';
    	$params[] = $cdids;

    	$params[] = self::STATUS_REMOVE;
    	$wheres[] = 'md_status<%d';

    	return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE " . implode(" AND ", $wheres), $params);
    }

}
