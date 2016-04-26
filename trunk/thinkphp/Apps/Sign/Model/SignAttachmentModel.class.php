<?php
/**
 * SignAttachmentModel.class.php
 * $author$
 */

namespace Sign\Model;

class SignAttachmentModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获取外勤记录相关图片
	 * @param array $record_list 记录id
	 */
	public function out_img($record_list) {
		
		$sql = "SELECT * FROM __TABLE__";
		//拼装查询条件
		$where[] = "outid IN (?)";
		$where_params[] = $record_list;
		//不查询已经删除的
		$where[] = "status < ?";
		$where_params[] = $this->get_st_delete();

		return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where), $where_params);
	}

	/**
	 * [insert_multi 插入数据]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function insert_multi($data) {
		
		return $this->_m->insert_all($data);
	}

}
