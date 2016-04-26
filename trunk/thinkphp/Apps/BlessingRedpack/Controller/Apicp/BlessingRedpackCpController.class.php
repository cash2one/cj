<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 15/11/13
 * Time: 下午2:37
 */
namespace BlessingRedpack\Controller\Apicp;
use BlessingRedpack\Model\BlessingRedpackModel;
use Common\Common\Pager;
use Common\Common\PagerOnclick;
use Common\Common\Cache;
use Com\QRcode;

class BlessingRedpackCpController extends AbstractController {

    /**
     * 发送红包消息
     * @author anything
     */
    public function test() {

    	return true;
    }
    /**
     * 红包列表分页接口(后台)
     * @author: anything
     * @createTime: 2015/11/17
     */
    public function list_page_get(){

        $is_search = false;

        /*请求参数*/
        $page = I('get.page', 1, 'intval');
        $limit = I('get.limit', 10, 'intval');
        $actname = I('get.actname');//活动主题

        //查询条件
        $params = array();

        if(!empty($actname)){
            $params['actname'] = $actname;

            $is_search = true;
        }

        list($start, $limit, $page) = page_limit($page, $limit);

        // 分页
        $page_option = array($start, $limit);

        // 排序
        $order_by = array('created' => 'DESC');

        $__blessing_redpack_service = D('BlessingRedpack/BlessingRedpack', 'Service');//实例化模型类

        //根据条件查询红包列表
        list($list, $total) = $__blessing_redpack_service->list_page($params, $page_option, $order_by);

        /*格式化数据*/
        foreach ($list as &$_v) {
            $_v = $__blessing_redpack_service->format($_v);
        }

        // 分页
        $multi = '';
        if ($total > 0) {
            $pagerOptions = array(
                'total_items' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'show_total_items' => true
            );

            $multi = Pager::make_links($pagerOptions);
        }

        // 返回数据
        $this->_result = array(
           // 'page' => $page,
            'total' => $total,
            'list' => $list,
            'multi' => $multi,
            'issearch' => $is_search
        );

    }

    /**
     * 新增红包
     * @author: anything
     */
    public function add_post(){

        $__blessing_redpack_service = D('BlessingRedpack/BlessingRedpack', 'Service');

        $params = I('post.');

        $actname = $params['actname'];//活动主题
        $inviteContent = $params['inviteContent'];//被邀请语
        $type = $params['type'];
        $total = $params['total'];
        $wishing = $params['wishing'];
        $allCompany = $params['allCompany'];
        $specifiedHiddenObj = $params['specifiedHiddenObj'];
        $blessHiddenObj = $params['blessHiddenObj'];
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $imgReceiveBg = $params['imgReceiveBg'];
        $imgChatBg = $params['imgChatBg'];


        if($type == BlessingRedpackModel::TYPE_AVERAGE){
            if(empty($params['single'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
        }

        //如果是自由红包
        if($type == BlessingRedpackModel::TYPE_FREE){
            if(empty($params['freeSum'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
            if(empty($params['freeTotal'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }

        }


        if(empty($actname)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(empty($inviteContent)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(empty($type)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if($type == BlessingRedpackModel::TYPE_RAND){
            if(empty($total)){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
        }

        if(empty($wishing)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if($type != BlessingRedpackModel::TYPE_FREE){
            if($allCompany == ''){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
            if($allCompany == '1'){
                if(empty($specifiedHiddenObj)){
                    E('_ERROR_PARAM_VAL_NULL');
                    return false;
                }
            }
        }

        if(empty($blessHiddenObj)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(empty($startTime)){
            E('_ERROR_PARAM_VAL_NULL');
            return true;
        }
        if(empty($endTime)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }


        $ca_uid = $this->_login->user['ca_id'];
        $ca_username = $this->_login->user['ca_username'];
        $params['login_uId'] = $ca_uid;
        $params['login_username'] = $ca_username;

        $__blessing_redpack_service->add($params);


    }

    /**
     * 红包详情
     * @author: anything
     */
    public function detail_get(){

        /*请求参数*/
        $id = I('get.id', '', 'intval');

        if(empty($id)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $blessing_redpack_service = D('BlessingRedpack/BlessingRedpack', 'Service');

        $data = $blessing_redpack_service->get_redpack($id);

        if(empty($data)){
            E('_ERR_BLESSING_REDPACK_IS_EMPTY');
            return false;
        }
        $t = array(
            'm_uid' => '111',
            'm_username' => '222'
        );
        $t[] = array(
            'm_uid' => 'aaaaa',
            'm_username' => 'aaaaa'
        );

        $data['_persons'] = unserialize($data['persons']);
        $data['_content'] = unserialize($data['content']);

        //格式化数据
        $data = $blessing_redpack_service->format($data);

        // 返回数据
        $this->_result = $data;

    }


    /**
     * 红包领取详情列表
     */
    public function list_receive_get(){

        /*请求参数*/
        $id = I('get.id', '', 'intval');
        $page = I('get.page', 1, 'intval');
        $limit = I('get.limit', 10, 'intval');

        if(empty($id)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        //查询条件
        $params = array(
            'id' => $id
        );

        list($start, $limit, $page) = page_limit($page, $limit);

        // 分页
        $page_option = array($start, $limit);

        // 排序
        $order_by = array('redpack_time' => 'DESC');

        $blessing_redpack_log_service = D('BlessingRedpack/BlessingRedpackLog', 'Service');

        list($list, $total) = $blessing_redpack_log_service->list_receive_page($params, $page_option, $order_by);

        /*格式化数据*/
        foreach ($list as &$_v) {
            $_v = $blessing_redpack_log_service->format($_v);
        }

        // 分页
        $multi = '';
        if ($total > 0) {
            $pagerOptions = array(
                'total_items' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'show_total_items' => true
            );

            $multi = PagerOnclick::make_links($pagerOptions);
        }

        // 返回数据
        $this->_result = array(
            'total' => $total,
            'list' => $list,
            'multi' => $multi
        );
    }

    /**
     * 生成、下载二维码(限自由红包使用)
     */
    public function qrcodeDownload_get()
    {
        $params = I('get.');
        $id = $params['id'];
        $is_download = $params['isDownload'];

        $cache = &Cache::instance();
        $setting = $cache->get('Common.setting');

        //跳转地址
        $url = 'http://' . $setting['domain']. '/BlessingRedpack/Frontend/Index/Redpack?id='.$id;

        // 纠错级别：L、M、Q、H
        $errorCorrectionLevel = 'L';
        // 点的大小：1到10
        $matrixPointSize = 10;
        $qrcode = QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);


        //创建背景,并将二维码贴到左边
        $bk = imagecreate(370, 370);
        imagecolorallocate($bk, 255, 255, 255);
        imagecopy($bk, $qrcode, 0, 0, 0, 0, 350, 350);

        if($is_download){
            //直接下载
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length:6000");
            Header("Content-Disposition: attachment; filename=qcode" . $id . ".png");
            imagepng($bk);
        }else{
            //直接输出图片
            header('Content-Type: image/png');
            imagepng($bk);
        }
    }
}
