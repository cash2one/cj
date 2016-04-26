<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/11
 * Time: 下午12:10
 */
namespace Askfor\Service;

use Askfor\Model\AskforAttachmentModel;

class AskforAttachmentService extends AbstractService {

	//构造方法
	public function __construct() {

		$this->_d = D('Askfor/AskforAttachment');
		parent::__construct();
	}

	/**
	 * 自由流程新增图片
	 * @param $params array 接收参数
	 * @param $af_id int 审批id
	 * @return bool 返回值
	 */
	public function img_add($params, $af_id) {

		//判断图片数量是否大于9张
		if (count($params['atids']) > AskforAttachmentModel::IMG_COUNT) {
			E('_ERR_OVER_IMGCOUNT');

			return false;
		}

		$img_list = $params['atids'];
		$img_data = array();
		//格式入库的数据
		foreach ($img_list as $v) {
			$img_data[] = array(
				'af_id' => $af_id,
				'at_id' => $v,
				'm_uid' => $params['m_uid'],
				'm_username' => $params['m_username'],
			);
		}

		//入库操作
		$this->_d->insert_all($img_data);

		return true;
	}

}
