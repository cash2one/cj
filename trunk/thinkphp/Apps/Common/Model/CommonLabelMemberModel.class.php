<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:45
 */
namespace Common\Model;

class CommonLabelMemberModel extends AbstractModel{

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 标签里的人搜索
	 * @param $params array 接收参数
	 * @param $page_option array 分页条件
	 * @return array 返回数据
	 */
	public function list_by_conds_member($params, $page_option){

		$sql = "SELECT * FROM __TABLE__";
		//搜索条件
		if(!empty($params['m_username'])){
			$where[] = 'm_username LIKE ?';
			$where_params[] = '%'.$params['m_username'].'%';
		}
		if(!empty($params['laid'])){
			$where[] = 'laid = ?';
			$where_params[] = $params['laid'];
		}
		$where[] = 'status < (?)';
		$where_params[] = $this->get_st_delete();

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		$order_option = array('created' => 'DESC');
		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where)."{$orderby}{$limit}", $where_params);
	}

	/**
	 * 根据条件统计标签里的人的总数
	 * @param $params array 条件
	 * @return array
	 */
	public function count_by_conds_member($params){

		$sql = "SELECT count(*) FROM __TABLE__";
		//搜索条件
		if(!empty($params['m_username'])){
			$where[] = 'm_username LIKE ?';
			$where_params[] = '%'.$params['m_username'].'%';
		}
		if(!empty($params['laid'])){
			$where[] = 'laid = ?';
			$where_params[] = $params['laid'];
		}
		$where[] = 'status < (?)';
		$where_params[] = $this->get_st_delete();

		return $this->_m->result($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}
	/**
	 * 根据laid和m_uid删除数据
	 * @param $uid_list array 用户id
	 * @param $laid int 标签id
	 */
	public function delete_by_laid_muid($uid_list, $laid){

		$sql = "UPDATE __TABLE__ SET ";
		//删除
		$sets = array("`{$this->prefield}status`=?", "`{$this->prefield}deleted`=?");
		$where_params[] = $this->get_st_delete();
		$where_params[] = NOW_TIME;

		//标签id
		$where[] = 'laid = (?)';
		$where_params[] = $laid;
		$where[] = 'status < (?)';
		$where_params[] = $this->get_st_delete();
		//删除uid条件
		$str_where = ' AND (';
		foreach($uid_list as &$uid){
			$new_uid[] = 'm_uid = (?)';
			$where_params[] = $uid;
		}
		$str_where .= implode(' OR ', $new_uid);
		$str_where .= ')';

		return $this->_m->execsql($sql . implode(',', $sets) . ' WHERE ' . implode(' AND ', $where)."{$str_where}", $where_params);
	}
}
