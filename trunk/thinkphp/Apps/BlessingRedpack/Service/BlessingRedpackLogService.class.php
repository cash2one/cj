<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 15/11/18
 * Time: 21:44
 */
namespace BlessingRedpack\Service;

use BlessingRedpack\Model\BlessingRedpackLogModel;
use Common\Common\wepay\BlessingRedpackPay;
use Common\Common\Cache;
use Think\Log;

class BlessingRedpackLogService extends AbstractService
{
    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = D('BlessingRedpack/BlessingRedpackLog');

    }

    /**
     * 红包领取明细列表
     * @param $params
     * @param $page_option
     * @param $order_option
     * @return array
     *///nonce_str=随机字符串，sign＝签名，partner_trade_no＝商户订单号,mch_id=商户号,appid=Appid
    public function list_receive_page($params, $page_option, $order_option)
    {

        $data = $this->_d->list_receive_page($params, $page_option, $order_option);

        return $data;
    }

    /**
     * 格式化数据
     * @param $data
     * @return mixed
     */
    public function format($data)
    {

        // 时间字段
        $time_fields = array('redpack_time', 'payment_time', 'created', 'updated', 'deleted');
        foreach ($time_fields as $_key) {
            if (!empty($data[$_key])) {
                $data['_' . $_key] = rgmdate($data[$_key], 'Y-m-d H:i:s');
            }
        }

        if (BlessingRedpackLogModel::REDPACK_OPEN == $data['redpack_status']) {
            $data['_redpack_status'] = '待拆';
        } else if (BlessingRedpackLogModel::REDPACK_PAY_ERROR == $data['redpack_status']) {
            $data['_redpack_status'] = '支付失败';
        } else if (BlessingRedpackLogModel::REDPACK_OK == $data['redpack_status']) {
            $data['_redpack_status'] = '已领取';
        } else if (BlessingRedpackLogModel::REDPACK_PAY_SUCCESS == $data['redpack_status']) {
            $data['_redpack_status'] = '已支付';
        }

        //格式化领取金额(单位:元)
        $data['_money'] = number_format($data['money'] / 100, 2);

        return $data;
    }

    /**
     * 红包领取明细列表(此方法只在手动修改红包的结束日期时使用)
     * @param $params
     * @param $page_option
     * @param $order_option
     * @return array
     */
    public function list_receive_for_update_time($params, $page_option, $order_option)
    {

        $data = $this->_d->list_receive_page($params, $page_option, $order_option);
        return $data;
    }

    /**
     *
     * 同步微信支付状态
     * @param $params
     */
    public function syncWePayResult($params){

        Log::record("同步微信支付结果开始", Log::INFO);

        $conds = array(
            'id' => $params['redpack_id']
        );
        $data = $this->_d->list_receive_page($conds);

        if(empty($data)){
            return true;
        }

        // 获取商户号和密钥
        $cache = &Cache::instance();
        $common_cache = $cache->get('Common.setting');
        $cache_bless_setting = $cache->get('BlessingRedpack.setting');

        $blessList = &$data[0];
        $redpackPay = new BlessingRedpackPay();

        foreach ($blessList as &$v) {

            //如果是待支付，查询微信支付结果，更新状态
            if ($v['redpack_status'] == BlessingRedpackLogModel::REDPACK_OK) {

                if(empty($v['payment_no'])){
                    Log::record("查询微信支付结果----订单payment_no为空, 红包明细id:" . $v['id'], Log::INFO);
                    continue;
                }

                $nonce_str = random(16);
                $params = array(
                    'nonce_str' => $nonce_str,
                    'partner_trade_no' => $v['mch_billno'],
                    'mch_id' => $cache_bless_setting['mchid'],
                    'appid' => $common_cache['corp_id']
                );

                try{
                    //Log::record("开始调用微信接口,参数------" . var_export($params, true), Log::INFO);

                    //调用微信企业支付结果查询接口
                    $result = array();
                    $redpackPay->getTransferInfo_post($params, $result);

                    //Log::record("调用微信接口结束,返回结果------" . var_export($result, true), Log::INFO);

                } catch (\Exception $e) {
                    Log::record('查询支付结果异常：', Log::ERR);
                    Log::record($e->getMessage(), Log::ERR);
                    continue;
                }

                //更新明细表的支付状态、支付成功时间
                if (rstrtolower($result['status']) == 'success') {
                    $blessLog = array(
                        'redpack_status' => BlessingRedpackLogModel::REDPACK_PAY_SUCCESS,
                        'payment_time' => $result['transfer_time']//支付成功返回时间
                    );

                    $this->_d->update($v['id'], $blessLog);

                }else if(rstrtolower($result['status']) == 'failed'){
                    $blessLog = array(
                        'redpack_status' => BlessingRedpackLogModel::REDPACK_PAY_ERROR
                    );

                    $this->_d->update($v['id'], $blessLog);
                }

            }
        }

        Log::record("同步微信支付结果结束", Log::INFO);

        return true;

    }


    public function count_by_params($conds){
        return $this->_d->count_by_params($conds);
    }

    public function list_receive_excel($params, $page_option, $order_option){
        return $this->_d->list_receive_excel($params, $page_option, $order_option);
    }
}
