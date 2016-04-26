<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace QyApi\Controller\Api;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	// 套件接口实例
	protected $_serv_suite = null;
	// 微信企业号消息内容(已转成数组)
	protected $_wxmsg = array();

	public function before_action() {

		// 为企业号提供的接口, 不需要基类做前置操作
		// return parent::before_action();

		try {

			// 初始化套件接口
			$this->_serv_suite = \Common\Common\WxqySuite\Service::instance();
			// 如果签名验证失败
			if (!$this->_serv_suite->check_signature()) {
				$this->_response($this->_serv_suite->retstr);
				return false;
			}

			// 取消息内容
			$this->_wxmsg = $this->_serv_suite->recv();
			if (empty($this->_wxmsg)) {
				$this->_response();
				return false;
			}

		} catch (\Think\Exception $e) {
			$this->_response($e);
			return false;
		} catch (\Exception $e) {
			// 记录日志
			\Think\Log::record($e->getMessage() . ':' . $e->getCode());
			$this->_response($e);
			return false;
		}

		return true;
	}

	/**
	 * 输出返回数据
	 *
	 * @access protected
	 * @param mixed $data 要返回的数据
	 * @param String $type 返回类型 JSON XML
	 * @param integer $code HTTP状态
	 * @return void
	 */
	protected function _response($data = '', $type = '', $code = 200) {

		// 如果需要返回的信息为空
		if (empty($data)) {
			$data = $this->_serv_suite->retstr;
		}

		// 如果返回值非标量(数字, 字串)
		if (!is_scalar($data)) {
			$data = 'Success';
		}

		// 如果还是空
		if (empty($data)) {
			$data = 'Success';
		}

		exit($data);
	}

}
