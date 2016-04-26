<?php
/**
 * 微信企业支付类
 */
namespace Common\Common\wepay;
use Common\Common\Cache;
use Common\Common\Wxqy\Service;
use Think\Log;

class BlessingRedpackPay {
	// 支付参数
	public $parameters = array();
	// 请求的错误信息
	public $errmsg = '';
	// 请求的错误码
	public $errcode = 0;
	// 企业付款密钥
	private $__mchkey;
	// 企业付款的接口url
	const SEND_PAY_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    // 查询企业付款结果url(post)
    const SELECT_PAY_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';

	// domain
	protected $_domain = '';

    // 缓存key前缀
    protected $_redis_key = null;

	public function __construct() {

		// 获取商户号和密钥
        $cache = &Cache::instance();
        $cache_bless_setting = $cache->get('BlessingRedpack.setting');
        $this->__mchkey = $cache_bless_setting['mchkey'];
        $mchid = $cache_bless_setting['mchid'];

        $cache_setting = $cache->get('Common.setting');
        $this->_redis_key = $cache_setting['domain'];

		// 获取二级域名
		$domains = explode('.', $cache_setting['domain']);
		$this->_domain = $domains[0];

		// 配置参数
		$this->parameters = array(
            'mch_appid' => $cache_setting['corp_id'],
			'mchid' => $mchid,
            'nonce_str' => random(16)
		);
	}

	/**
	 * 参数赋值（数组合并）
	 *
	 * @param unknown $options
	 */
	private function __set_parameter($options) {

		$this->parameters = array_merge($this->parameters, $options);
	}

	/**
	 * 获取给定的参数值
	 *
	 * @param string $parameter
	 * @return multitype:
	 */
	private function __get_parameter($parameter) {

		return $this->parameters[$parameter];
	}

	/**
	 * 签名参数是否定义检查
	 *
	 * @return boolean
	 */
	private function __check_sign_parameters() {

        if ($this->parameters["mch_appid"] == null || $this->parameters["mchid"] == null
            || $this->parameters["nonce_str"] == null || $this->parameters["spbill_create_ip"] == null
            || $this->parameters["partner_trade_no"] == null || $this->parameters["openid"] == null
            || $this->parameters["check_name"] == null || $this->parameters["amount"] == null
            || $this->parameters["desc"] == null) {
            return false;
        }

		return true;
	}

	/**
	 * 获取签名字符串
	 * 例如：
	 * appid： wxd930ea5d5a258f4f
	 * mch_id： 10000100
	 * device_info： 1000
	 * Body： test
	 * nonce_str： ibuaiVcKdpRxkhJA
	 * 第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
	 * stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_i
	 * d=10000100&nonce_str=ibuaiVcKdpRxkhJA";
	 * 第二步：拼接支付密钥：
	 * stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
	 * sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A
	 * 9CF3B7"
	 *
	 * @return boolean|string
	 */
	private function __get_sign($redis, $rid) {

		if (null == $this->__mchkey || '' == $this->__mchkey) {
            //解锁
            $redis->unlock($this->_redis_key."redpack".$rid);

            E('_ERR_WX_PAY_SIGN_IS_EMPTY');
            return false;
		}

       // var_dump($this->parameters);
     //   exit;
		if ($this->__check_sign_parameters() == false) { // 检查生成签名参数
            //解锁
            $redis->unlock($this->_redis_key."redpack".$rid);

            E('_ERR_WX_PAY_PARAM_LOSE');
            return false;
		}

        //ASCII码从小到大排序（字典序）
		ksort($this->parameters);
		$unSignParaString = $this->formatQueryParaMap($this->parameters);
		$signStr = $unSignParaString . "&key=" . $this->__mchkey;
		return strtoupper(md5($signStr));
	}

	// 格式化签名参数
	private function formatQueryParaMap($paraMap) {

		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v) {
			if (null != $v && "null" != $v && "sign" != $k) {
				$buff .= $k . "=" . $v . "&";
			}
		}

		$reqPar = '';
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff) - 1);
		}

		return $reqPar;
	}

	/**
	 * 生成接口XML信息
	 *
	 * @param number $retcode
	 * @param string $reterrmsg
	 * @return boolean|string
	 */
	private function __create_redpack_xml($redis, $rid) {

		$sign = $this->__get_sign($redis, $rid);
		if (!$sign) {
			return false;
		}

		$this->__set_parameter(array('sign' => $sign));
		return $this->__array_to_xml($this->parameters);
	}

	/**
	 * 数组转XML格式
	 *
	 * @param array $arr
	 * @return string
	 */
	private function __array_to_xml(array $arr) {

		$xml = "<xml>";
		foreach ($arr as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
		}

		$xml .= "</xml>";
		return $xml;
	}

	/**
	 * 使用证书发送post请求
	 *
	 * @param string $vars
	 * @param number $timeout 请求超时时间，单位：秒，默认：30秒
	 * @param array $aHeader 额外的header头信息
	 * @return mixed|boolean
	 */
	private function __curl_post_ssl($vars, $redis, $rid, $timeout = 20, $aHeader = array()) {

      //  echo $vars;
      //  exit;

		$ch = curl_init();
		// 超时时间
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, self::SEND_PAY_URL);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        $cache = &Cache::instance();
        $cache_setting = $cache->get('BlessingRedpack.setting');
        $wxpay_certificate1 = $cache_setting['wxpay_certificate1'];
        $wxpay_certificate2 = $cache_setting['wxpay_certificate2'];
        $wxpay_certificate3 = $cache_setting['wxpay_certificate3'];
        if(empty($wxpay_certificate1)){
            //解锁
            $redis->unlock($this->_redis_key."redpack".$rid);

            E('_ERR_WX_PAY_CERT_IS_EMPTY');
            return false;
        }

        if(empty($wxpay_certificate2)){
            //解锁
            $redis->unlock($this->_redis_key."redpack".$rid);

            E('_ERR_WX_PAY_KEY_IS_ERROR');
            return false;
        }

        if(empty($wxpay_certificate3)){
            //解锁
            $redis->unlock($this->_redis_key."redpack".$rid);

            E('_ERR_WX_PAY_CA_IS_ERROR');
            return false;
        }

        $_serv_attachment = D('Common/CommonAttachment', 'Service');
        $f1 = $_serv_attachment->get($wxpay_certificate1[0]);
        $f2 = $_serv_attachment->get($wxpay_certificate2[0]);
        $f3 = $_serv_attachment->get($wxpay_certificate3[0]);

        //APP_PATH.'BlessingRedpack/Controller/Api/apiclient_cert.pem'
        //APP_PATH.'BlessingRedpack/Controller/Api/apiclient_key.pem'
        // APP_PATH.'BlessingRedpack/Controller/Api/rootca.pem'
        // ssl证书
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, get_pemdir().$f1['at_attachment']);
        curl_setopt($ch, CURLOPT_SSLKEY, get_pemdir().$f2['at_attachment']);
        curl_setopt($ch, CURLOPT_CAINFO, get_pemdir().$f3['at_attachment']);

        if (count($aHeader) >= 1) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);

        try{
            $data = curl_exec($ch);
            if ($data) {
                curl_close($ch);
                return $data;
            } else {
                $errno = curl_errno($ch);
                $err = curl_error($ch);
                curl_close($ch);

                if($errno == 58){
                    Log::record('读取支付证书出错');
                    E('_ERR_WX_SERVER_BUSY');
                    return false;
                }

                //解锁
                $redis->unlock($this->_redis_key."redpack".$rid);

                Log::record(var_export(self::SEND_PAY_URL . '|' . print_r($vars, true) . '|' . $errno . '|' . $err, true));
                E('_ERR_WX_SERVER_BUSY');
                return false;
            }
        }catch (Exception $e){
            Log::record('读取支付证书出错');
            E('_ERR_WX_SERVER_BUSY');
            return false;
        }
	}

    private function __curl_post_orde($vars, $timeout = 20, $aHeader = array()) {

        $ch = curl_init();
        // 超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, self::SELECT_PAY_URL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        $cache = &Cache::instance();
        $cache_setting = $cache->get('BlessingRedpack.setting');
        $wxpay_certificate1 = $cache_setting['wxpay_certificate1'];
        $wxpay_certificate2 = $cache_setting['wxpay_certificate2'];
        $wxpay_certificate3 = $cache_setting['wxpay_certificate3'];

        $_serv_attachment = D('Common/CommonAttachment', 'Service');
        $f1 = $_serv_attachment->get($wxpay_certificate1[0]);
        $f2 = $_serv_attachment->get($wxpay_certificate2[0]);
        $f3 = $_serv_attachment->get($wxpay_certificate3[0]);

        //APP_PATH.'BlessingRedpack/Controller/Api/apiclient_cert.pem'
        //APP_PATH.'BlessingRedpack/Controller/Api/apiclient_key.pem'
        // APP_PATH.'BlessingRedpack/Controller/Api/rootca.pem'
        // ssl证书
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, get_pemdir().$f1['at_attachment']);
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, get_pemdir().$f2['at_attachment']);
        curl_setopt($ch, CURLOPT_CAINFO, get_pemdir().$f3['at_attachment']);

        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        $data = curl_exec($ch);
        // \Think\log::record(var_export($data, true));
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $errno = curl_errno($ch);
            echo($errno);
            $err = curl_error($ch);
            echo($err);
            curl_close($ch);

            Log::record(var_export(self::SELECT_PAY_URL . '|' . print_r($vars, true) . '|' . $errno . '|' . $err, true));
            E('_ERR_WX_SERVER_BUSY');
            return false;
        }
    }


    /**
	 * 根据参数调用支付接口
	 *
	 * @param array $options
	 */
	public function send($options, &$send_result = array(), $redis, $rid) {

		$this->__set_parameter($options);
		$postXml = $this->__create_redpack_xml($redis, $rid);
		if ($this->errcode) {
			return false;
		}

		$responseXml = $this->__curl_post_ssl($postXml, $redis, $rid);
		if ($this->errcode) {
			return false;
		}

		if ($responseXml === false) {
			return false;
		}

		try {
			$r = simplexml_load_string($responseXml, 'SimpleXMLElement');
		} catch (Exception $e) {
           Log::record("1:".$postXml."\n---------------\n".$responseXml);
		}

		// 网络错误
		if (empty($r->return_code)) {
            //解锁
            $redis->unlock($this->_redis_key."redpack".$rid);

           Log::record("2:".$postXml."\n---------------\n".$responseXml);
            E('_ERR_WX_PAY_NETWORK_IS_ERROR');
            return false;
		}

		// 通讯失败
		if (rstrtolower($r->return_code) != 'success') {
            //解锁
            $redis->unlock($this->_redis_key."redpack".$rid);
           Log::record("3:".$postXml."\n---------------\n".$responseXml);
            E('_ERR_WX_SERVER_BUSY');
            return false;
		}

		// 通讯成功，处理交易结果
		if (empty($r->result_code)) {
           Log::record("4:".$postXml."\n---------------\n".$responseXml);
			if (!empty($r->err_code)) {
                //解锁
                $redis->unlock($this->_redis_key."redpack".$rid);
                E('_ERR_BLESSING_REDPACK_SYSTEM_BUSY');
                return false;
			}

            //解锁
            $redis->unlock($this->_redis_key."redpack".$rid);
            E('_ERR_WX_PAY_OTHER_IS_ERROR');
            return false;
		}

		// 交易错误
		if (rstrtolower($r->result_code) != 'success') {
            //解锁
            $redis->unlock($this->_redis_key."redpack".$rid);
           Log::record("5:".$postXml."\n---------------\n".$responseXml);
            E('_ERR_BLESSING_REDPACK_SYSTEM_BUSY');
            return false;
		}

		// 交易成功，返回的结果集
		$send_result = array(
			'partner_trade_no' => (string)$r->partner_trade_no,  // 商户订单号
            'payment_no' => (string)$r->payment_no,  // 微信订单号
			'mchid' => (string)$r->mchid,  // 商户号
			'mch_appid' => (string)$r->mch_appid,  // 商户corpid
            'return_code' => (string)$r->return_code, //返回状态码
            'return_msg' => (string)$r->return_msg, //返回信息
            'result_code' => (string)$r->result_code, //业务结果
            'err_code' => (string)$r->err_code, //错误代码
            'err_code_des' => (string)$r->err_code_des, //错误代码描述
            'payment_time' => (string)$r->payment_time, //支付成功返回时间
			'pay_openid' => $options['openid'],  // 用户openid
			'total_amount' => $options['amount']
		);

       Log::record("pay send success");
		return true;
	}


    /**
     * 查看企业付款结果
     * @param $options 请求参数
     * nonce_str=随机字符串，sign＝签名，partner_trade_no＝商户订单号,mch_id=商户号,appid=Appid
     */
    public function getTransferInfo_post($options, &$send_result = array()){

        // 得到签名信息
        $sign = $this->__get_select_pay_sign($options);
        if (!$sign) {
            return false;
        }
        // 将sign参数合并到请求的数组中
        $req_params = array_merge(array('sign' => $sign), $options);
        $postXml = $this->__array_to_xml($req_params);
        $responseXml = $this->__curl_post_orde($postXml);
        if ($responseXml === false) {
            return false;
        }

        try {
            $r = simplexml_load_string($responseXml, 'SimpleXMLElement');
        } catch (Exception $e) {
           Log::record("1:".$postXml."\n---------------\n".$responseXml);
        }

        // 网络错误
        if (empty($r->return_code)) {
           Log::record("2:".$postXml."\n---------------\n".$responseXml);
            return $this->__set_error(7000, '与微信通讯发生意外网络错误');
        }

        // 通讯失败
        if (rstrtolower($r->return_code) != 'success') {
           Log::record("3:".$postXml."\n---------------\n".$responseXml);
            E('_ERR_WX_SERVER_BUSY');
            return false;
        }

        // 通讯成功，处理交易结果
        if (empty($r->result_code)) {
            $self_errcode = 70002;
            $errmsg = '未知的微信错误结果';
            $errcode = 0;
            if (! empty($r->err_code)) {
                $self_errcode = 70002;
                $errmsg = '服务器繁忙, 请稍候再试';
                $errcode = $r->err_code;
            }

           Log::record("4:".$postXml."\n---------------\n".$responseXml);
            return $this->__set_error($self_errcode, $errmsg, $errcode);
        }

        // 交易错误
        if (rstrtolower($r->result_code) != 'success') {
           Log::record("5:".$postXml."\n---------------\n".$responseXml);
            return $this->__set_error(70003, "服务器繁忙, 请稍候再试", $r->err_code);
        }

        // 交易成功，返回的结果集  o0k3psg0TnnChi_zyhs4P_UidtXw
        $send_result = array(
            'partner_trade_no' => (string)$r->partner_trade_no,  // 商户订单号
            'mch_id' => (string)$r->mch_id,  // 商户号
            'status' => (string)$r->status,  // 转账状态
            'reason' => (string)$r->reason,  // 失败原因
            'openid' => (string)$r->openid,  //收款用户的openid
            'transfer_time' => (string)$r->transfer_time,  //发起转账的时间
            'payment_amount' => (string)$r->payment_amount
        ); //

       Log::record("select pay status send success");
    }

    /**
     * 获取查询企业支付的签名信息
     * @param $options 请求参数
     * @return bool|string
     */
    private function __get_select_pay_sign($options) {

        // 密钥参数校验
        if (null == $this->__mchkey || '' == $this->__mchkey) {
            E('_ERR_WX_PAY_SIGN_IS_EMPTY');
            return false;
        }

        // 检查生成签名参数
        if ($options["partner_trade_no"] == null || $options["mch_id"] == null
            || $options["nonce_str"] == null || $options["appid"] == null) {
            E('_ERR_WX_PAY_PARAM_LOSE');
            return false;
        }


        //ASCII码从小到大排序（字典序）
        ksort($options);
        $unSignParaString = $this->formatQueryParaMap($options);
        $signStr = $unSignParaString . "&key=" . $this->__mchkey;
        return strtoupper(md5($signStr));
    }


	/**
	 * 设置错误信息
	 *
	 * @param number $self_errcode
	 * @param string $errmsg
	 * @param string $errcode
	 */
	private function __set_error($self_errcode, $errmsg, $errcode = '') {

		$this->errcode = $self_errcode;
		$this->errmsg = ($errcode ? $errcode . ':' : '').$errmsg;
		return $self_errcode > 0 ? false : true;
	}


    /**
     * 生成红包单号
     * @param string $mchid 支付账号
     * @return string
     */
    public static function billno($mchid) {

        return substr($mchid, 0, 10) . rgmdate(time(), 'YmdHi') . random(6);
    }

    /**
     * 求一个数的平方
     *
     * @param $n
     */
    public static function sqr($n) {

        return $n * $n;
    }

    /**
     * 生产min和max之间的随机数，但是概率不是平均的，从min到max方向概率逐渐加大。
     * 先平方，然后产生一个平方值范围内的随机数，再开方，这样就产生了一种“膨胀”再“收缩”的效果。
     */
    public static function x_random($bonus_min, $bonus_max) {

        $sqr = intval(self::sqr($bonus_max - $bonus_min));
        $rand_num = rand(0, ($sqr - 1));
        return intval(sqrt($rand_num));
    }

    /**
     *
     * @param $bonus_total 红包总额
     * @param $bonus_count 红包个数
     * @param $bonus_max 每个小红包的最大额
     * @param $bonus_min 每个小红包的最小额
     * @return 存放生成的每个小红包的值的一维数组
     */
    public static function get_bonus($bonus_total, $bonus_count, $bonus_max, $bonus_min) {

        $result = array();
        $average = $bonus_total / $bonus_count;
        $a = $average - $bonus_min;
        $b = $bonus_max - $bonus_min;
        // 这样的随机数的概率实际改变了，产生大数的可能性要比产生小数的概率要小。
        // 这样就实现了大部分红包的值在平均数附近。大红包和小红包比较少。
        for($i = 0; $i < $bonus_count; $i ++) {
            // 因为小红包的数量通常是要比大红包的数量要多的，因为这里的概率要调换过来。
            // 当随机数>平均值，则产生小红包
            // 当随机数<平均值，则产生大红包
            if (rand($bonus_min, $bonus_max) > $average) {
                // 在平均线上减钱
                $temp = $bonus_min + self::x_random($bonus_min, $average);
                $result[$i] = $temp;
                $bonus_total -= $temp;
            } else {
                // 在平均线上加钱
                $temp = $bonus_max - self::x_random($average, $bonus_max);
                $result[$i] = $temp;
                $bonus_total -= $temp;
            }
        }

        // 如果还有余钱，则尝试加到小红包里，如果加不进去，则尝试下一个。
        while ($bonus_total > 0) {
            for($i = 0; $i < $bonus_count; $i ++) {
                if ($bonus_total > 0 && $result[$i] < $bonus_max) {
                    $result[$i] ++;
                    $bonus_total --;
                }
            }
        }

        // 如果钱是负数了，还得从已生成的小红包中抽取回来
        while ($bonus_total < 0) {
            for($i = 0; $i < $bonus_count; $i ++) {
                if ($bonus_total < 0 && $result[$i] > $bonus_min) {
                    $result[$i] --;
                    $bonus_total ++;
                }
            }
        }

        return $result;
    }

    /**
     * 验证微信支付的openid是否存在，不存在则需要调用接口主动去获取
     * @param $_user
     */
    public function get_wx_pay_openid($_user){
        // 获取支付openid
        $ser = new Service();
        $ser->get_pay_openid($openid, $_user['m_openid']);


        return $openid;
    }

}
