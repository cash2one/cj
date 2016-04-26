<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/15
 * Time: 下午3:29
 *
 * body  支付产品描述
 * notify_url  接收微信支付异步通知回调地址 (base64_encode)
 * out_trade_no  商家订单号
 * total_fee  支付金额
 * success_url 支付成功后的跳转url
 * error_url 支付失败后的跳转url
 */

namespace PubApi\Controller\Frontend;

use Com\WxPay as WxPay;
use Common\Common\Wxqy as Wxqy;

class WqpayController extends AbstractController {

	/** 支付实例化 */
	protected $_WxPay = null;
	/** 微信支付单位 分 */
	const WX_MONEY_UNIT = 100;

	public function before_action($action = '') {

		$this->_exterior_openid = true;

		if (!parent::before_action($action)) {
			return false;
		}

		return true;
	}

	/**
	 * 统一下单接口
	 * @return bool
	 */
	public function Wqpay() {

		/** 发送订单信息 */
		// 支付实例化
		$this->_WxPay = new WxPay\UnifiedOrder();
		// 组成传送参数
		$this->_order_xml();

		/** 接收微信返回信息 */
		// 把微信返回的数据还原成数组
		$Wx_result = $this->_WxPay->xmlToArray($this->_WxPay->response);
		// 验证微信返回的签名
		$WxSign = $Wx_result['sign'];
		$provingSign = $Wx_result;
		unset($provingSign['sign']); // 排除微信的签名
		$ReWxSign = $this->_WxPay->getSign($provingSign); // 计算签名
		if ($WxSign != $ReWxSign) {
			E('_ERR_WX_SIGN_ORRER');
			return false;
		}

		// 给微信JSAPI的数据
		$result = array(
			'appId' => $Wx_result['appid'],
			'timeStamp' => NOW_TIME,
			'nonceStr' => $this->_WxPay->createNoncestr(),
			'package' => 'prepay_id=' . $Wx_result['prepay_id'],
			'signType' => 'MD5',
		);

		// 计算签名并赋值
		$this->assign('paySign', $this->_WxPay->getSign($result));
		// 其他数据
		$this->assign('appId', $result['appId']);
		$this->assign('timeStamp', $result['timeStamp']);
		$this->assign('nonceStr', $result['nonceStr']);
		$this->assign('package', $result['package']);
		$this->assign('signType', $result['signType']);

		$this->_output('Wqpay/Wqpay');

		return true;
	}

	/**
	 * 组成订单数据xml
	 * @return bool
	 */
	protected function _order_xml() {

		// 获取openid
		if (empty($this->_login->user)) {
			$wx_openid = $this->_login->getcookie('wx_openid');
		} else {
			$wxqy = new Wxqy\Service();
			$wxqy->convert_to_openid_for_pay($wx_openid, $this->_login->user['m_openid']);
		}
		$get = I('get.');
		if (empty($get) || empty($wx_openid)) {
			return false;
		}

		// 调用统一支付 并设置商品参数
		$this->_WxPay->setParameter('body', $get['body']); // 商品描述
		$this->_WxPay->setParameter('notify_url', rbase64_decode($get['notify_url'])); // 接收微信支付异步通知回调地址
		$this->_WxPay->setParameter('out_trade_no', $get['out_trade_no']); // 商家订单号
		$this->_WxPay->setParameter('total_fee', $get['total_fee']); // 金额
		$this->_WxPay->setParameter('trade_type', 'JSAPI'); // 类型
		$this->_WxPay->setParameter('openid', $wx_openid); // pay_openid
		// 发送Xml数据
		$this->_WxPay->postXml();

		// 支付金额
		$this->assign('total_fee', $get['total_fee'] / self::WX_MONEY_UNIT);
		// 描述
		$this->assign('body', $get['body']);

		// 成功后的跳转URL
		$success_url = I('get.success_url', '', 'rbase64_decode');
		// 失败后的跳转URL
		$error_url = I('get.error_url', '', 'rbase64_decode');
		// 成功后的跳转URL
		$this->assign('success_url', $success_url);
		// 失败后的跳转URl
		$this->assign('error_url', $error_url);

		return true;
	}
}