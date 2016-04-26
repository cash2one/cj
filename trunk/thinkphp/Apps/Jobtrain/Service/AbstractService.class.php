<?php
/**
 * AbstractService.class.php
 * $author$
 */

namespace Jobtrain\Service;

abstract class AbstractService extends \Common\Service\AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

    /**
     * 获取附件
     * @param int $atid
     * @return mixed
     */
    public function get_attachment($atid) {
        $cache = &\Common\Common\Cache::instance();
        $sets = $cache->get('Common.setting');
        $face_base_url = cfg('PROTOCAL') . $sets ['domain'];
        // 返回附件URL地址
        $url = $face_base_url . '/attachment/read/' . $atid;
        return $url;
    }
}
