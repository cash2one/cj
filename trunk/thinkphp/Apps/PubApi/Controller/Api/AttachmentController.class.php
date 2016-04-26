<?php
/**
 * AttachmentController.class.php
 * $author$
 */
namespace PubApi\Controller\Api;

use Common\Common\Cache;

class AttachmentController extends AbstractController {

	public function before_action($action = '') {

		if (in_array($action, array('Upload'))) {
			$this->_require_login = false;
		}

		return parent::before_action($action);
	}

	/**
	 * 附件上传
	 * @return bool
	 */
	public function Upload_post($uid = 0, $username = '') {

		// 附件信息
		$attachment = array();
		if (0 == $uid && !empty($this->_login->user)) {
			$uid = $this->_login->user['m_uid'];
			$username = $this->_login->user['m_username'];
		}

		// 扩展参数
		$extend = array(
			'm_uid' => $uid,
			'm_username' => $username
		);

		// 实例化
		$serv_att = D('PubApi/CommonAttachment', 'Service');

		// 上传失败
		if (!$serv_att->upload($attachment, $extend)) {
			$this->_set_error($serv_att->get_errmsg(), $serv_att->get_errcode());
			return false;
		}

		// 上传成功，返回自增主键
		$this->_result = $attachment;
		return $attachment;
	}

	/**
	 * [get_aid 通过微信serverid/medaid获取媒体文件]
	 * @return [bool] [返回值]
	 */
	public function Get_aid_get() {

		$params = I('get.'); // 获取的参数
		$extend = array(
			'uid' => $this->_login->user['m_uid'],
			'username' => $this->_login->user['m_username']
		);
		$serv_att = D('PubApi/CommonAttachment', 'Service'); // 实例化

		// 检测参数
		if (!$serv_att->params_check($params)) {
			E($serv_att->get_errcode().':'.$serv_att->get_errmsg());
			return false;
		}

		// 获取需要的数据
		$result = $serv_att->get_by_serverid($params, $extend);
		$this->_result = $result;
		return true;
	}

}
