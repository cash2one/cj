<?php
/**
 * ReasonController.class.php
 * $author$
 */

namespace Sign\Controller\Api;

class ReasonController extends AbstractController {
	// 实例化数据库
	protected $_serv_detail = null;

	/**
	 * 备注信息提交接口
	 * @return bool
	 */
	public function Submit_post() {

		// 获取提交数据
		$post = I('post.');
		$this->_serv_detail = D('Sign/SignDetail', 'Service');

		// 验证数据
		if (!$this->__execute($post)) {
			return false;
		}

		/** 入库操作 */
		if (!$this->_insert($post)) {
			E('_ERR_INSERT_ERROR');
		}

		// 查询要返回给前端需要的数据
		$this->_result = $this->_serv_detail->list_by_reason_post($post);

		return true;
	}

	/**
	 * 验证数据
	 * @param $post
	 * @return bool
	 */
	private function __execute($post) {

		if (empty($post['reason'])) {
			E('_ERR_MISS_PARAMETER_REASON');
		}
		if (empty($post['id'])) {
			E('_ERR_MISS_PARAMETER_ID');
		}
		if (empty($post['type'])) {
			E('_ERR_MISS_PARAMETER_TYPE');
		}
		// 验证错误
		if (!$this->__is_string_count_in_range($post['reason'], 0, 240)) {
			E('_ERR_OUTNUMBER');
		}

		return true;
	}

	/** 提交插入 */
	protected function _insert($post) {

		if (!empty($post['reason'])) {
			$data = array(
				'sd_reason' => $post['reason'],
				'sr_id' => $post['id'],
				'type' => $post['type']
			);

			if (!$this->_serv_detail->insert_reason($data)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 计算字符串字符个数是否在两值之间（含）
	 * 无论任何字符均按一个来计算同mb_strlen
	 * @param string $string
	 * @param number $min
	 * @param number $max
	 * @param string $charset
	 * @return boolean
	 */
	private function __is_string_count_in_range($string, $min, $max, $charset = 'utf-8') {

		$length = mb_strlen($string, $charset);

		return $length >= $min && $length <= $max;
	}

}
