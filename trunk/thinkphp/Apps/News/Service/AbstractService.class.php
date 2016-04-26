<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/15 0015
 * Time: 14:22
 */

namespace News\Service;

abstract class AbstractService extends \Common\Service\AbstractService {

    const IS_READ = 1; // 已经阅读
    const NO_READ = 0; // 未阅读
    const IS_LIKE = 2; // 已经点赞过
    const IS_AUTHOR = 1; // 是作者
    const NO_AUTHOR = 0; // 不是作者
    const IS_CHECK = 3; // 已经审核过了


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
