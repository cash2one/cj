<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace BlessingRedpack\Controller\Frontend;

use Com\QRcode;
use Common\Common\Login;
use Common\Common\WxqyMsg;
use Common\Common\Cache;
use Think\Log;


class IndexController extends AbstractController {

    public function before_action($action = '') {
        if($action == 'CreateQrcode'){
            $this->_require_login = false;
        }
        return parent::before_action($action);
    }

    public function Index() {

        $this->show('[IndexController->Index]');
        $this->_output("Frontend/Index/Index");
    }

    /**
     * 活动二维码扫码请求入口
     * @return bool
     */
    public function Redpack() {
        $id = I('get.id');
        if(empty($id)){
            E('_ERR_BLESSING_ID_IS_EMPTY');
            return true;
        }

        $_serv_redpack = D('BlessingRedpack/BlessingRedpack', 'Service');
        $redpack = $_serv_redpack->get($id);
        $title = $redpack['actname'];
        // 验证活动是否已开始
        $starttime = $redpack['starttime'];
        $endtime = $redpack['endtime'];
        $currtime = time();

        $cache = &Cache::instance();
        $setting = $cache->get('Common.setting');
        $bless_setting = $cache->get('BlessingRedpack.setting');

        // 活动尚未开始
        if($starttime > $currtime){
            $url = 'http://' . $setting['domain'] . $bless_setting['redpack_no_start_url'];
            redirect($url);
        }

        // 活动已结束
        if($endtime < $currtime){
            $url = 'http://' . $setting['domain'] . $bless_setting['redpack_end_url'];
            redirect($url);
        }

        if ($this->_login->user) {
            // 记录老用户的扫码进入信息
            $user = $this->_login->user;

            Log::record("老用户扫码进入....".$user['m_username'], Log::INFO);

            $uid = $user['m_uid'];
            $_serv_redpack_member = D('BlessingRedpack/BlessingRedpackMember', 'Service');
            $_params = array(
                "redpack_id" => $id,
                "m_uid" => $uid
            );
            // 验证是否已扫过码
            $redpack_member = $_serv_redpack_member->get_by_conds($_params);
            if(empty($redpack_member)){
                // 新增活动表
                $_params['m_mobilephone'] = $user['m_mobilephone'];
                $_params['m_username'] = $user['m_username'];
                $_params['is_new'] = 1; //是否是新人 1:是否
                $_serv_redpack_member->insert($_params);

                // 发送红包消息提醒
                $this->_send($id, $title, $redpack['invite_content'], array($uid));
            }
            // 跳转红包页面
            $url = 'http://' . $setting['domain'] . $bless_setting['redpack_url'] . '?id=' . $id . '&title=' . $title . '&visit=view';
            redirect($url);
        } else {
            Log::record("新用户扫码进入....", Log::INFO);
            // 跳转注册页面
            $url = 'http://' . $setting['domain'] . $bless_setting['register_url'] . '?id=' . $id . '&visit=preview';
            redirect($url);
        }
    }

	/**
	 * 生成二维码(测试使用)
	 *
	 * @param $url 绝对路径地址
	 */
	/**public function CreateQrcode() {

		$cache = &Cache::instance();
		$setting = $cache->get('Common.setting');

		$id = I('get.id');
		if (empty($id)) {
			$url = 'http://' . $setting['domain'] . '/BlessingRedpack/Frontend/Index/Redpack?id=10';
		} else {
			$url = 'http://' . $setting['domain'] . '/BlessingRedpack/Frontend/Index/Redpack?id=' . $id;
		}
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel = 'L';
		// 点的大小：1到10
		$matrixPointSize = 10;
		$qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
		// 直接输出图片
		header('Content-Type: image/png');
		imagepng($qrcode);
	}*/

	// 判断是否登陆
	public function _is_login() {

		// 用户信息初始化
		$this->_login = &Login::instance();
		$this->_login->init_user();
		// 如果用户信息为空
		$need_auth = false;
		if (empty($this->_login->user)) {
			$this->_login->auto_login($need_auth, $this->_require_login);
		} else { // 有 code 就转向剔除后的 URL
			$code = I('get.code');
			if (!empty($code)) {
				$boardurl = preg_replace('/\&?code\=(\w+)/i', '', boardurl());
				redirect($boardurl);
				return true;
			}
		}

		// 如果需要转向授权地址
		if ($need_auth) {
			$this->assign('redirectUrl', $this->_login->get_wxqy_auth_url());
			$this->_output('Common@Frontend/Redirect');
			return false;
		}

		return true;
	}

    /**
     * 发送红包消息
     * @param $redpackId
     * @param $title
     * @param $inviteContent
     * @param $users
     * @return bool
     */
    public function _send($redpackId, $title, $inviteContent, $users) {

        Log::record('红包消息推送开始------红包ID：' . $redpackId . ',  接收人数：' . count($users), Log::INFO);


        $cache = &Cache::instance();
        $setting = $cache->get('Common.setting');
        $bless_setting = $cache->get('BlessingRedpack.setting');
        $wxqyMsg = WxqyMsg::instance();

        cfg('PLUGIN_ID', $bless_setting['pluginid']);
        cfg('AGENT_ID', $bless_setting['agentid']);

        $url = 'http://' . $setting['domain'] . $bless_setting['redpack_url'] . '?id=' . $redpackId . '&title=' . $title;
        $desc = $inviteContent;
        $rusult = $wxqyMsg->send_news($title, $desc, $url, $users, '', '', $bless_setting['agentid'], $bless_setting['pluginid']);

        Log::record('红包消息推送结束,result-' . $rusult, Log::INFO);


        return true;
    }
}
