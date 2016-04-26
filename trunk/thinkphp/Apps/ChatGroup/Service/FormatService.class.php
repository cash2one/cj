<?php
/**
 * FormatService.class.php
 * $author$
 */

namespace ChatGroup\Service;

class FormatService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化数据
	 * @param array &$data 待格式化操作
	 */
	public function chatgroup_format(&$data) {

		$data['_created'] = rgmdate($data['cg_created']);
		$data['_updated'] = rgmdate($data['cg_updated']);
		$chatgroup_type = D("ChatGroup/Chatgroup");

		if ($data['cg_type'] == $chatgroup_type->get_type_chatgroup()) {
			$data['_type'] = L('CHATGROUP_NAME');
		} else {
			$data['_type'] = L('ONE_CHATGROUP_NAME');
		}

		return true;
	}

	/**
	 * 聊天信息格式化
	 * @param $data聊天内容
	 */
	public function chatgroup_record_format(&$data) {

		$attachment_type = D("ChatGroup/ChatgroupRecord");
		foreach ($data as $_key => $_val) {

			if($data[$_key]['cgr_attachment']==$attachment_type->get_type_attachment()){
				$data[$_key]['_attachment'] =L("ATTACHMENT");
			}elseif($data[$_key]['cgr_attachment']==$attachment_type->get_type_content()){
				$data[$_key]['_attachment'] =L("CONTENT");
			}elseif($data[$_key]['cgr_attachment']==$attachment_type->get_type_image()){
				$data[$_key]['_attachment'] =L("IMAGE");
			}
			$data[$_key]['_at_attachment'] = "/attachment/read/{$data[$_key]['at_id']}";
			$data[$_key]['_created'] = rgmdate($data[$_key]['cgr_created']);
			$data[$_key]['_updated'] = rgmdate($data[$_key]['cgr_updated']);
		}

		return true;
	}

}
