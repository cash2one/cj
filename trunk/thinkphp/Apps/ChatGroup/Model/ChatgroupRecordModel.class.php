<?php
/**
 * ChatgroupModel.class.php
 * $author$
 */

namespace ChatGroup\Model;

class ChatgroupRecordModel extends AbstractModel {

	// 附件
	const TYPE_ATTACHMENT = 1;

	// 聊天信息
	const TYPE_CONTENT = 2;

	// 图片
	const TYPE_IMAGE = 3;

	// 附件类型 图片
	const TYPE_ATTACHMENT_IMAGE = 1;

	// 构造方法
	public function __construct() {

		$this->prefield = 'cgr_';

		parent::__construct();
	}

	// 附件
	public function get_type_attachment() {
		return self::TYPE_ATTACHMENT;
	}

	// 聊天信息
	public function get_type_content() {
		return self::TYPE_CONTENT;
	}

	// 图片
	public function get_type_image(){
		return self::TYPE_IMAGE;
	}

	// 附件类型 图片
	public function get_type_attachment_image(){
		return self::TYPE_ATTACHMENT_IMAGE;
	}
	/**
	 * 获取聊天内容
	 * @param int $cgid 聊天组ID
	 * @param int $limit 返回最大记录数
	 * @param int $max_record_id 最大记录ID
	 * @param int $min_record_id 最小记录ID
	 * @return array
	 */
	public function list_group_msg($cgid, $limit, $max_record_id, $min_record_id) {

		$sql = "SELECT r.*, m.m_uid, m.m_username, m.m_face FROM __TABLE__ r
				LEFT JOIN oa_member m ON r.cgr_send_uid=m.m_uid
				WHERE ";

		// 查询条件
		$where = array("r.cg_id=?", "r.cgr_status<?");

		// 参数
		$array_params = array(
			$cgid,
			$this->get_st_delete()
		);

		// 排序条件
		$order_option = array();
		$order_by = array();


		// 如果是获取历史的聊天记录（最小的聊天记录ID大于0）
		if ($min_record_id > 0) {
			$where[] = "r.cgr_id<?";
			$array_params[] = $min_record_id;
			$order_option = array('r.cgr_id' => 'DESC');
		} elseif($max_record_id>0) { // 获取未读聊天记录（最大的聊天记录ID大于等于0）
			$where[] = "r.cgr_id>?";
			$array_params[] = $max_record_id;
			$order_option = array('r.cgr_id' => 'ASC');
		}else{ //如果最大ID值和最小ID值都没有
			$order_option = array('r.cgr_id' => 'DESC');
		}

		// 每页数量
		if (!$this->_limit($limit, $limit)) {
			return false;
		}

		// 排序
		if (!$this->_order_by($order_by, $order_option)) {
			return false;
		}

		$list_record =$this->_m->fetch_array($sql . implode(' AND ', $where) . "{$order_by}{$limit}", $array_params);

		// 如果
		if($max_record_id<0){
			$list_record = array_reverse($list_record);
		}

		return $list_record;
	}
}
