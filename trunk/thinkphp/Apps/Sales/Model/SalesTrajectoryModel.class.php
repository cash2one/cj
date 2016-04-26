<?php
/**
 * SalesTrajectoryModel.class.php
 * $author$
 */

namespace Sales\Model;

class SalesTrajectoryModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
        $this->prefield = 'st_';
	}

    /**
     * 编辑轨迹
     * @param int $st_id 轨迹id
     * @param int $trajectory 编辑的轨迹信息
     * $author: husendong@vchangyi.com
     */
    public function edit_trajectory($st_id,$trajectory){

        // sql
        $sql = "UPDATE __TABLE__ SET `sc_id`=?,`st_content`=? , `st_address`=?,`st_type`=?,`at_ids`=?,`st_status`=? , `st_updated`=?  WHERE `st_id`=? AND `st_status`<?";

        // 条件
        $params = array(
            $trajectory['sc_id'] ,
            $trajectory['st_content'] ,
            $trajectory['st_address'],
            $trajectory['st_type'],
            (string)$trajectory['at_ids'],
            $this->get_st_update(),
            NOW_TIME,
            $st_id,
            $this->get_st_delete()
        );

        return $this->_m->update($sql, $params);
    }

    /**
     * 轨迹查询
     * @param int $m_uid 销售人id
     * @param int $sc_source 客户来源
     * @param int $st_type 销售阶段
     * $author: husendong@vchangyi.com
     */
    public function list_trajectory_get($params, $page_option){

        // 查询sql
        $sql = "SELECT A.st_id, A.sc_id, A.m_uid, A.m_username, A.st_content, A.st_address, A.st_type, A.at_ids FROM __TABLE__ A LEFT JOIN oa_sales_customer B ON A.sc_id=B.sc_id";

        // 条件
        $wheres = array("A.st_status< ? ");
        $where_params = array(
            $this->get_st_delete()
        );

        // 客户来源匹配
        if (!empty($params["sc_source"])) {
            $wheres[] = 'B.sc_source=?';
            $where_params[] =$params["sc_source"];
        }

        // 销售阶段匹配
        if (!empty($params["st_type"])) {
            $wheres[] = 'A.st_type=?';
            $where_params[] =$params["st_type"];
        }

        // 销售人匹配
        if (!empty($params["m_uid"])) {
            $wheres[] = 'A.m_uid=?';
            $where_params[] =(int)$params["m_uid"];
        }

        // 分页参数
        $limit = '';
        if (!$this->_limit($limit, $page_option)) {
            return false;
        }

        return $this->_m->fetch_array($sql." WHERE ".implode(' AND ', $wheres)."{$limit}", $where_params);

    }

}
