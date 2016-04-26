<?php
/**
 * 红包活动->返回公司二维码
 * $Author$
 * $Id$
 */
class voa_c_api_blessingredpack_get_qrcode extends voa_c_api_blessingredpack_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {
        $this->_p_blessageredpack = voa_h_cache::get_instance()->get('plugin.blessingredpack.setting', 'oa');
        $qrcode_id = $this->_p_blessageredpack['qrcode_id'];
        $setting = voa_h_cache::get_instance()->get('setting', 'oa');
        if(!empty($qrcode_id)){
            $qrcode_url = "http://". $setting['domain'] . '/attachment/read/'.$qrcode_id;
            logger::error("qrcode_id已存在，则直接返回公司二维码地址: ".$qrcode_url);
        }else{
            // 获取公司二维码绝对路径地址
            $qrcode_url = $setting['qrcode'];
            // 上传公司二维码到本地
            if(!empty($qrcode_url)){
                // 处理上传并写入附件
                $uda = &uda::factory('voa_uda_frontend_attachment_insert');
                $file_content = base64_encode(file_get_contents($qrcode_url));
                $_POST['pic'] = $file_content;
                $attachment = array();
                if ($uda->upload($attachment, 'pic', "base64")) {
                    $qrcode_url = "http://" . $setting['domain'] . '/attachment/read/'.$attachment['at_id'];

                    // 更新红包配置表
                    $serv = &service::factory('voa_s_oa_blessingredpack_setting');
                    $_update = array(
                        "qrcode_id" => $attachment['at_id']
                    );
                    $serv->update_setting($_update);

                }
                logger::error("获取公司二维码qrcode_url地址为: ".$qrcode_url);
            } else {
                logger::error("获取公司二维码qrcode_url地址为空: ");
            }
        }

        $this->_result = array(
            "qrcode" => $qrcode_url
        );

        return true;

	}
}
