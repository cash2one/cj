<?php
/**
 * BlessingRedpackSettingService.php
 * 企业祝福红包设置表
 * @author: anything
 * @createTime: 2015/11/19 10:23
 * @version: $Id$ 
 * @copyright: 畅移信息
 */
namespace BlessingRedpack\Service;

use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class BlessingRedpackSettingService extends AbstractSettingService {

    // 构造方法
    public function __construct() {
        parent::__construct();
        $this->_d = D('BlessingRedpack/BlessingRedpackSetting');
    }


    /**
     * 读取所有并检查 pluginid, agentid 参数是否正确
     * @see \Common\Service\AbstractSettingService::list_kv()
     */
    public function list_kv() {

        // 取表中的数据
        $sets = parent::list_kv();

        // 获取插件列表
        $cache = &Cache::instance();
        $plugins = $cache->get('Common.plugin');

        // 获取 pluginid, agentid
        $pluginid = empty($sets['pluginid']) ? 0 : (int)$sets['pluginid'];
        $agentid = empty($sets['agentid']) ? 0 : (int)$sets['agentid'];
        // 如果插件信息不存在, 则从插件重新获取 pluginid 和 agentid
        if (empty($plugins[$pluginid]) || $agentid != $plugins[$pluginid]['cp_agentid'] || 'BlessingRedpack' != rstrtolower($plugins[$pluginid]['cp_identifier'])) {

            // 遍历所有插件
            foreach ($plugins as $_p) {
                // 如果不是留言本, 则取下一个
                if ('BlessingRedpack' != rstrtolower($_p['cp_identifier'])) {
                    continue;
                }

                // 取 pluginid, agentid 信息
                $pluginid = $_p['cp_pluginid'];
                $agentid = (int)$_p['cp_agentid'];

                // 更新表数据
                $this->update_kv(array(
                    'pluginid' => $pluginid,
                    'agentid' => $agentid
                ));
            }
        }

        // 更新相关值
        $sets['pluginid'] = $pluginid;
        $sets['agentid'] = $agentid;

        return $sets;
    }

    /**
     * 更新红包配置
     * @param $params
     * @return bool
     */
    public function update_setting($params){

        /*序列化证书*/
        if(!empty($params['wxpay_certificate1'])){

            $wxpay_certificate1 = explode(',', $params['wxpay_certificate1']);
            $params['wxpay_certificate1'] = serialize($wxpay_certificate1);
        }else{
            $params['wxpay_certificate1'] = '';
        }

        if(!empty($params['wxpay_certificate2'])){
            $wxpay_certificate2 = explode(',', $params['wxpay_certificate2']);
            $params['wxpay_certificate2'] = serialize($wxpay_certificate2);
        }else{
            $params['wxpay_certificate2'] = '';
        }
        if(!empty($params['wxpay_certificate3'])){
            $wxpay_certificate3 = explode(',', $params['wxpay_certificate3']);
            $params['wxpay_certificate3'] = serialize($wxpay_certificate3);
        }else{
            $params['wxpay_certificate3'] = '';
        }

        if(empty($params['invite_department'])){
            $params['invite_department'] = '';
        }
        if(empty($params['mchid'])){
            $params['mchid'] = '';
        }
        if(empty($params['mchkey'])){
            $params['mchkey'] = '';
        }

        // 循环更新
        foreach ($params as $_k => $_v) {
            $ups[$_k] = $_v;
            $this->update_kv($ups);
        }

        //reload cache
        clear_cache();

        return true;
    }
}
