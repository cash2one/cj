<?php
/**
 * @Author: ppker
 * @Date:   2015-09-16 16:44:07
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-09-16 17:40:29
 */

namespace Common\Model;
use Common\Model\AbstractModel;

class WeixinLocationModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = '';
	}

	/**
	 * 获取用户的最后一条记录
	 * @param int $uid 用户UID
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_last($uid) {

		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `m_uid`=? AND `wl_status`<?  ORDER BY `wl_id` desc Limit 1", array(
			$uid, $this->get_st_delete()
		));
	}

    /**
     * 根据经纬度查询
     * @param $params
     * @return array|bool
     */
    public function get_by_conds_for_filter($conds){
        $params = array();
        // 条件
        $wheres = array();
        if (!$this->_parse_where($wheres, $params, $conds)) {
            return false;
        }

        // 状态条件
        $wheres[] = "`{$this->prefield}wl_status`<?";
        $params[] = $this->get_st_delete();
        // 执行 SQL
        return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE ".implode(' AND ', $wheres), $params);
    }



}
