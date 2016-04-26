<?php
/**
 * 接收来自微信的消息
 * $Author$
 * $Id$
 */

class voa_wepay_service extends voa_wepay_base {

	static function &instance() {

		static $object;
		if(empty($object)) {
			$object	= new self();
		}

		return $object;
	}

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 申请退款
	 * @param array $order 订单详情
	 * @param string $trade_no 商户订单号
	 * @param int $fee 订单总金额(单位:分)
	 * @param string $refund_fee 商户退款单号
	 * @param int $refund_fee 退款金额(单位:分)
	 */
	public function apply_refund(&$order, $trade_no, $fee, $refund_no, $refund_fee) {

		//使用退款接口
		$refund = new Refund_pub();
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$refund->setParameter("out_trade_no", $trade_no);//商户订单号
		$refund->setParameter("out_refund_no", $refund_no);//商户退款单号
		$refund->setParameter("total_fee", $fee);//总金额
		$refund->setParameter("refund_fee", $refund_fee);//退款金额
		$refund->setParameter("op_user_id", WxPayConf_pub::$mchid);//操作员
		//非必填参数，商户可根据实际情况选填
		//$refund->setParameter("sub_mch_id","XXXX");//子商户号
		//$refund->setParameter("device_info","XXXX");//设备号
		//$refund->setParameter("transaction_id","XXXX");//微信订单号

		//调用结果
		$order = $refund->getResult();

		//商户根据实际情况设置相应的处理流程,此处仅作举例
		if ($order["return_code"] == "FAIL") {
			logger::error("通信出错：".$order['return_msg']);
			return false;
		}

		/**echo "业务结果：".$order['result_code']."<br>";
		echo "错误代码：".$order['err_code']."<br>";
		echo "错误代码描述：".$order['err_code_des']."<br>";
		echo "公众账号ID：".$order['appid']."<br>";
		echo "商户号：".$order['mch_id']."<br>";
		echo "子商户号：".$order['sub_mch_id']."<br>";
		echo "设备号：".$order['device_info']."<br>";
		echo "签名：".$order['sign']."<br>";
		echo "微信订单号：".$order['transaction_id']."<br>";
		echo "商户订单号：".$order['out_trade_no']."<br>";
		echo "商户退款单号：".$order['out_refund_no']."<br>";
		echo "微信退款单号：".$order['refund_idrefund_id']."<br>";
		echo "退款渠道：".$order['refund_channel']."<br>";
		echo "退款金额：".$order['refund_fee']."<br>";
		echo "现金券退款金额：".$order['coupon_refund_fee']."<br>";*/

		return true;
	}

	/**
	 * 根据订单号获取订单信息
	 * @param array $order 订单详情
	 * @param string $trade_no 商户订单号
	 */
	public function get_refund(&$order, $trade_no) {

		//使用退款查询接口
		$query = new RefundQuery_pub();
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$query->setParameter("out_trade_no", $order);//商户订单号
		// $query->setParameter("out_refund_no","XXXX");//商户退款单号
		// $query->setParameter("refund_id","XXXX");//微信退款单号
		// $query->setParameter("transaction_id","XXXX");//微信退款单号
		//非必填参数，商户可根据实际情况选填
		//$query->setParameter("sub_mch_id","XXXX");//子商户号
		//$query->setParameter("device_info","XXXX");//设备号

		//退款查询接口结果
		$order = $query->getResult();

		//商户根据实际情况设置相应的处理流程,此处仅作举例
		if ($order["return_code"] == "FAIL") {
			logger::error("通信出错：".$order['return_msg']);
			return false;
		}

		/**echo "业务结果：".$order['result_code']."<br>";
		echo "错误代码：".$order['err_code']."<br>";
		echo "错误代码描述：".$order['err_code_des']."<br>";
		echo "公众账号ID：".$order['appid']."<br>";
		echo "商户号：".$order['mch_id']."<br>";
		echo "子商户号：".$order['sub_mch_id']."<br>";
		echo "设备号：".$order['device_info']."<br>";
		echo "签名：".$order['sign']."<br>";
		echo "微信订单号：".$order['transaction_id']."<br>";
		echo "商户订单号：".$order['out_trade_no']."<br>";
		echo "退款笔数：".$order['refund_count']."<br>";
		echo "商户退款单号：".$order['out_refund_no']."<br>";
		echo "微信退款单号：".$order['refund_idrefund_id']."<br>";
		echo "退款渠道：".$order['refund_channel']."<br>";
		echo "退款金额：".$order['refund_fee']."<br>";
		echo "现金券退款金额：".$order['coupon_refund_fee']."<br>";
		echo "退款状态：".$order['refund_status']."<br>";*/

		return true;
	}

	/**
	 * 根据订单号获取订单信息
	 * @param array $order 订单详情
	 * @param string $trade_no 订单号号
	 */
	public function get_order(&$order, $trade_no) {

		//使用订单查询接口
		$query = new OrderQuery_pub();
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$query->setParameter("out_trade_no", $trade_no);//商户订单号
		//非必填参数，商户可根据实际情况选填
		//$query->setParameter("sub_mch_id","XXXX");//子商户号
		//$query->setParameter("transaction_id","XXXX");//微信订单号

		//获取订单查询结果
		$order = $query->getResult();

		//商户根据实际情况设置相应的处理流程,此处仅作举例
		if ($order["return_code"] == "FAIL") {
			logger::error("通信出错：".$order['return_msg']);
			logger::error("错误代码：".$order['err_code']);
			logger::error("错误代码描述：".$order['err_code_des']);
			return false;
		}

		/**echo "交易状态：".$order['trade_state']."<br>";
		echo "设备号：".$order['device_info']."<br>";
		echo "用户标识：".$order['openid']."<br>";
		echo "是否关注公众账号：".$order['is_subscribe']."<br>";
		echo "交易类型：".$order['trade_type']."<br>";
		echo "付款银行：".$order['bank_type']."<br>";
		echo "总金额：".$order['total_fee']."<br>";
		echo "现金券金额：".$order['coupon_fee']."<br>";
		echo "货币种类：".$order['fee_type']."<br>";
		echo "微信支付订单号：".$order['transaction_id']."<br>";
		echo "商户订单号：".$order['out_trade_no']."<br>";
		echo "商家数据包：".$order['attach']."<br>";
		echo "支付完成时间：".$order['time_end']."<br>";*/

		return true;
	}

	/**
	 * 获取账单列表
	 * @param mixed $bills 账单列表
	 * @param string $date 日期字串, 如: 20150104
	 * @param string $type 账单类型
	 * @return boolean
	 */
	public function get_bill(&$bills, $date, $type = 'ALL') {

		//使用对账单接口
		$dl_bill = new DownloadBill_pub();
		//设置对账单接口参数
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$dl_bill->setParameter("bill_date", $date);//对账单日期
		$dl_bill->setParameter("bill_type", $type);//账单类型
		//非必填参数，商户可根据实际情况选填
		//$dl_bill->setParameter("device_info","XXXX");//设备号

		//对账单接口结果
		$result = $dl_bill->getResult();

		if ($result['return_code'] == "FAIL") {
			logger::error("通信出错：".$result['return_msg']);
			return false;
		}

		$bills = $dl_bill->response;
		return true;
	}

	/**
	 * 发起统一下单请求,并返回支付参数供JS使用
	 * @param string $params JS调用参数
	 * @param array $options 参数
	 *  + openid 微信openid
	 * 	+ goods_name 商品名称
	 * 	+ order_id	本地订单id
	 *  + total_fee	总金额,单位分
	 */
	public function wxpay(&$params, &$prepay_id, $options) {

		//使用jsapi接口
		$js_api = new JsApi_pub();

		$openid = $options['openid'];

		//=========步骤2：使用统一支付接口，获取prepay_id============
		//使用统一支付接口
		$unified_order = new UnifiedOrder_pub();

		$unified_order->setParameter("openid", $openid);//商品描述
		$unified_order->setParameter("body", rsubstr($options['goods_name'], 0, 60));//商品描述

		$timeStamp = startup_env::get('timestamp');

		$unified_order->setParameter("out_trade_no", $options['order_id']);//商户订单号
		$unified_order->setParameter("total_fee", $options['total_fee']);//总金额
		$unified_order->setParameter("notify_url", WxPayConf_pub::$notify_url);//通知地址
		$unified_order->setParameter("trade_type", "JSAPI");//交易类型

		$prepay_id = $unified_order->getPrepayId();

		//=========步骤3：使用jsapi调起支付============
		$js_api->setPrepayId($prepay_id);

		$params = $js_api->getParameters();
		return true;
	}

	/**
	 * 继续支付,返回支付参数
	 *  @param $params 		js调用参数
	 *  @param $prepay_id	微信订单号
	 */
	public function wxpay2(&$params, $prepay_id) {

		//使用jsapi接口
		$js_api = new JsApi_pub();

		$js_api->setPrepayId($prepay_id);

		$params = $js_api->getParameters();
		return true;
	}

	/**
	 * 返回共享地址参数
	 * @param string $addr_params 发货地址参数
	 */
	public function get_addr_params(&$addr_params) {

		$url = $_SERVER["HTTP_REFERER"];
		//$url = startup_env::get('boardurl');
		//$GLOBALS['url'] = $url;
		$sign_info = array(
			'accesstoken' => startup_env::get('web_access_token'),
			'url' => $url,
			'timeStamp' => (string)time(),
			'nonceStr' => $this->_create_nonce_str(6),
			'appid' => WxPayConf_pub::$appid
		);
		$address_sign = $this->_get_address_sign($sign_info);
		$addr_params = array(
			'appId' => WxPayConf_pub::$appid,
			'scope' => 'jsapi_address',
			'signType' => "sha1",
			"addrSign" => $address_sign,
			'timeStamp'=> $sign_info['timeStamp'],
			'nonceStr' => $sign_info['nonceStr']
		);
		return true;
	}

}
