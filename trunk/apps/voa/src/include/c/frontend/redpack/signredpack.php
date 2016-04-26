<?php
/**
 * 签到红包领取
 */

class voa_c_frontend_redpack_signredpack extends voa_c_frontend_redpack_base {

	public function execute() {

		if ('changyineibu.vchangyi.com' == $_SERVER['HTTP_HOST'] || 231 == $this->_user['m_uid']) {
			/**$vars = '<xml><act_name><![CDATA[红包]]></act_name><client_ip><![CDATA[222.90.149.111]]></client_ip><max_value>123</max_value><mch_billno><![CDATA[1249225801201506191016SkWAAV]]></mch_billno><mch_id>1249225801</mch_id><min_value>123</min_value><nick_name><![CDATA[畅移科技]]></nick_name><nonce_str><![CDATA[k6CtDAibAtK8kfTY]]></nonce_str><re_openid><![CDATA[o0k3psoU6Z1j94Gz0205viWIclbk]]></re_openid><remark><![CDATA[红包]]></remark><send_name><![CDATA[畅移科技]]></send_name><total_amount>123</total_amount><total_num>1</total_num><wishing><![CDATA[万事捷顺]]></wishing><wxappid><![CDATA[wxd10d18360734b570]]></wxappid><sign><![CDATA[6194C9EB167AD7CF1BBB5443A1CC7421]]></sign></xml>';
			$timeout = 20;
			$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
			$ch = curl_init();
			// 超时时间
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			// ssl证书
			curl_setopt($ch, CURLOPT_SSLCERT, ROOT_PATH . '/apps/voa/src/config/wepay/changyineibu/apiclient_cert.pem');
			curl_setopt($ch, CURLOPT_SSLKEY, ROOT_PATH . '/apps/voa/src/config/wepay/changyineibu/apiclient_key.pem');
			curl_setopt($ch, CURLOPT_CAINFO, ROOT_PATH . '/apps/voa/src/config/wepay/changyineibu/rootca.pem');
			if (count($aHeader) >= 1) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
			}

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
			$data = curl_exec($ch);
			logger::error($data);exit("AAA");*/

			// 载入微信支付红包类
			/**$redpack = new voa_wepay_redpack();
			// 设置参数
			// $openid = 'o06msuFDO7_xZOdSNAHZq6fe_zJ0'; // 测试用openid
			// $money = 100; // 红包金额，单位分
			$options = array(
				'nick_name' => '畅移科技', // 提供方名称
				'send_name' => '畅移科技',  // 红包发送者名称
				're_openid' => $this->_user['wx_openid'],  // 接收者
				'total_amount' => 123,  // 付款金额，单位分
				'min_value' => 123,
				'max_value' => 123,
				'total_num' => 1,  // 红包収放总人数
				'wishing' => '万事捷顺',
				'client_ip' => controller_request::get_instance()->get_client_ip(),
				'act_name' => '红包',  // 活劢名称
				'remark' => '红包'
			); // 备注信息

			// 推送微信发放红包
			if (! $redpack->send($options, $send_result)) {
				$this->errcode = $redpack->errcode;
				$this->errmsg = $redpack->errmsg;
				exit("failed");
			}

			exit("succeed");*/
		}

		// 分配红包
		$uda_rp = new voa_uda_frontend_redpack_send();
		$result = array();
		$params = array(
			'redpack_id' => $this->_p_sets['sign_redpack_id'],
			'uid' => $this->_user['m_uid'],
			'username' => $this->_user['m_username'],
			'openid' => $this->_user['wx_openid']
		);
		try {
			if (!$uda_rp->doit($params, $result)) {
				$this->_error_message($uda_rp->errmsg);
				return true;
			}

		} catch (help_exception $e) {
			logger::error($e);
			$this->_error_message($e->getMessage());
			return true;
		} catch (Exception $e) {
			logger::error($e);
			$this->_error_message('服务器繁忙');
			return true;
		}

		$this->view->set('rplog', $result);
		$this->_output('mobile/redpack/signredpack');

		return true;
	}

}
