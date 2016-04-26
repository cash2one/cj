<?php

namespace Jobtrain\Controller\Apicp;

class QcloudController extends AbstractController {
    /**
     * 获取签名
     */
    public function signature_get() {

        $argStr = $_GET['args'];
        cfg('JOBTRAIN', load_config(APP_PATH.'Jobtrain/Conf/config.php'));
        // 返回操作
        $this->_result = base64_encode(hash_hmac('sha1', $argStr, cfg('JOBTRAIN.SECRET_KEY'), true));
    }
}