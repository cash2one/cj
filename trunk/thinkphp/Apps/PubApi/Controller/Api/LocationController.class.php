<?php
/**
 * @Author: ppker
 * @Date:   2015-09-16 16:11:31
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-09-24 16:35:44
 * @Last Modified time: 2016-03-06 22:25:00
 */
namespace PubApi\Controller\Api;

use Think\Log;

class LocationController extends AbstractController {

	/**
	 * [GPSToAddress_post 接口的执行方法]
	 */
	public function GPSToAddress_post() {

		// 接收到的参数
		$params = I('post.');
		$wx_d = D('Common/WeixinLocation', 'Service'); // 记录表
		// 扩展的参数
		$extends = array(
			'm_uid' => $this->_login->user['m_uid'],
			'm_username' => $this->_login->user['m_username'],
		);

		$just_weixin = false; // 是否直接找公共表数据
		$update_weixin = false; // 是否更新微信表数据
		// 校验经纬度参数
		$this->check_params($params, $just_weixin, $update_weixin);

        // 公共数据获取最近的一条数据 并 赋值经纬度
        if ($just_weixin){
            $this->just_weixin($params);
        }

        // 如果手机端没获取到微信返回的经纬度、也没有获取到最近的打卡记录，就提示网络异常信息，必须有gps信息才可以打卡
        if (empty($params['longitude']) || empty($params['latitude'])) {
            E('_ERR_LATI_LONG_IS_NOT_NULL');
            return false;
        }

        // 根据经纬度进行解析地理位置
        // 先去经纬度 地址 存储表 oa_sign_record_location 获取一下address，如果没有才调用接口获取
        $_serv_sign_Record_location =  D('Sign/SignRecordLocation', 'Service');
        $conds = array(
            'longitude' => $params['longitude'],
            'latitude' => $params['latitude']
        );
		//Log::record('经纬度-----------------'.var_export($conds, true));
        $re_data = $_serv_sign_Record_location->get_by_conds($conds);
		//Log::record('$re_data------'.var_export($re_data, true));
        if(empty($re_data)){
            $re_data = $wx_d->get_address_by_location($params);
        }

		// 将最新的经纬度信息存入weixin 公共表数据
		if ($update_weixin) {
			$wx_d->insert_weixin($params, $extends);
		}

		$this->_result = array (
			'address' => $re_data['address'],
            'longitude' => $params['longitude'],
            'latitude' => $params['latitude']
		);


		return true;
	}


	/**
	 * [last_location 获取用户最后一次位置数据]
	 * @return [array] [返回的结果集]
	 */
	protected function last_location() {

		$wx_d = D('Common/WeixinLocation', 'Service'); // 记录表
		$m_uid = $this->_login->user['m_uid'];
		return $wx_d->get_last($m_uid);
	}


	/**
	 * [just_weixin 获取微信公共数据的信息] 10分钟之内重复打卡，则使用最近一次的地址
	 * @param  [array] &$params [传递的参数]
	 * @param  [string] &$just_ip [是否需要通过IP来取地址]
	 * @return
	 */
	protected function just_weixin(&$params) {
		$last_location = $this->last_location();
		if (!empty($last_location) && isset($last_location['longitude']) && isset($last_location['latitude']) && (NOW_TIME - $last_location['wl_created']) < 600) {
			$params['longitude'] = $last_location['wl_longitude'];
			$params['latitude'] = $last_location['wl_latitude'];
		}
	}


	/**
	 * [check_params]
	 * @param  [array] $params         [传递过来的数据]
     * @param  [bool] &$just_weixin   [是否通过微信表来取数据]
     * @param  [bool] &$update_weixin [是否更新微信表的数据]
	 * @return
	 */
	protected function check_params($params, &$just_weixin, &$update_weixin) {

		if (empty($params['longitude']) || empty($params['latitude'])) {
            $just_weixin = true;
		}else{
//            $wx_d = D('Common/WeixinLocation', 'Service'); // 记录表
//            $m_uid = $this->_login->user['m_uid'];
//            $conds = array(
//                'm_uid' => $m_uid,
//                'wl_latitude' => $params['latitude'],
//                'wl_longitude' => $params['longitude']
//            );
//            $t_d = $wx_d->get_by_conds_for_filter($conds);
//            // 如果库中已有记录就不做保存了
//            if(empty($t_d)){
//                $update_weixin = true;
//            }
            $just_weixin = $this->get_canuse_location($params['longitude'], $params['latitude']);
            $update_weixin = true;
        }

        return true;
	}

    /**
     * [get_canuse_location 是否要使用公共经纬度]
     * @param  [string] $longitude [传递的经度]
     * @param  [string] $latitude  [传递的维度]
     * @return [bool]
     */
    protected function get_canuse_location($longitude, $latitude) {

        // 不是有效数据
        if (!is_numeric($longitude) || !is_numeric($latitude)) {
            return true;
        }

        return false;
    }

}
