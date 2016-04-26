<?php
/**
 * @Author: ppker
 * @Date:   2015-09-15 10:49:10
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-09-24 16:54:23
 * @Description 外出考勤签到接口
 */
namespace Sign\Controller\Api;

use Sign\Model\SignSettingModel;
use Common\Common\Cache;
use Think\Log;

class OutSignController extends AbstractController {

    //外出考勤
    const OUT_SIGN_FLAG = 1;

	/**
	 * [Sign_post 接口的执行方法]
	 */
	public function Sign_post() {

		// 接受到的参数
		$params = I('post.');

        if(empty($params['send_remark'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if($params['send_remark'] != self::OUT_SIGN_FLAG){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(empty($params['address'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(empty($params['location'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        $sl_d = D('Sign/SignLocation', 'Service'); // 记录表
        $sa_d = D('Sign/SignAttachment', 'Service'); // 附件表
        $this->send_remark($params, $sl_d, $sa_d);
	}


	/**
	 * [send_remark 进行外出考勤签到]
	 * @param  [type] $params [description]
	 * @param  [type] $sl_d   [description]
	 * @param  [type] $sa_d   [附件表]
	 * @return [type]         [description]
	 */
	protected function send_remark($params, $sl_d, $sa_d) {

		// 扩展数据
		$extend = array(
			'uid' => $this->_login->user['m_uid'],
			'username' => $this->_login->user['m_username']
		);
//		$address = (string)$params['address'];
//		// 地址信息不能为空
//		if (empty($address)) {
//			//$this->_set_error("_ERR_ADDRESS_IS_NOT_EXIST");
//			E('_ERR_ADDRESS_IS_NOT_EXIST');
//			return false;
//		}

        $cache = &Cache::instance();
        $cache_setting = $cache->get('Sign.setting');
        $upload_img = SignSettingModel::OUT_SIGN_UPLOAD_IMG;
        if(!empty($cache_setting['out_sign_upload_img'])){
            $upload_img = $cache_setting['out_sign_upload_img'];
        }

        if(empty($params['atids'])){
            //是否开启外出考勤必须上传图片
            if($upload_img == SignSettingModel::OUT_SIGN_UPLOAD_IMG){
                E('_ERR_SIGN_UPLOAD_IMG_FAILD');
                return false;
            }
        }

        try{

            //开启事务
            $sl_d->start_trans();

            // 进行签到数据操作
            $re_sl_d = $sl_d->sign_insert($params, $extend);
            if (false == $re_sl_d) {
                E($sl_d->get_errcode() . ":" . $sl_d->get_errmsg());
                return false;
            }

            //上传附件图片
            $sa_d->upload_fj($params['atids'], $re_sl_d);

            $sl_d->commit();

        }catch (\Exception $e){
            Log::record('外出考勤签到异常：');
            Log::record($e->getMessage());
            $sl_d->rollback();
            E('_ERR_SIGN_OUT_ERROR');
            return false;
        }

		$this->su_page($re_sl_d);
		return true;
	}

	/**
	 * [su_page 上传成功后进行的页面初始化 生成数据]
	 * @param  [array] $post [待处理的数据]
	 * @return [bool]
	 */
	protected function su_page($data) {

		if (empty($data['sl_id'])) {
			E('_ERR_SL_ID_IS_NOT_EXIST');
			return false;
		}
		// 格式化部分数据
		$f_data = $this->format($data);
		$format_d = D('Sign/Format', 'Service'); //格式化Service
		$end_data = $format_d->make_data($f_data);
		$this->_result = $end_data;
		return true;
	}


	/**
	 * [format 格式化部分数据]
	 * @param  [array] $in [待格式化的数据]
	 * @return [array]     [格式化后的数据]
	 */
	protected function format($in) {

		$sa_d = D('Sign/SignAttachment', 'Service'); // 附件表
		$in ['sl_signtime'] = rgmdate($in ['sl_signtime'], 'Y-m-d H:i');

		// 获取设置
		$cache = &\Common\Common\Cache::instance();

		$sets = $cache->get('Common.setting');
		$url = $sets['domain'];

		$conds ['outid'] = $in ['sl_id'];
		$data = $sa_d->list_by_conds($conds);

		$img_array = array_column($data, 'atid'); // 图片atid 数组
		// 获取关联图片数据
		$sa_d = D('Common/CommonAttachment', 'Service'); // 附件表

		$img_info = null;
		if ($img_array) {
			$img_info = $sa_d->list_by_pks($img_array);
		}

		$sign_l = D('Sign/SignLocation', 'Service');
		// 生成返回的附件数据
		$sign_l->make_data($in, $img_info, $url);
		return $in;
	}




}
