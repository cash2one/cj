<?php
namespace Share\Controller\Apicp;
use Common\Common\Pager;
class MaterialController extends AbstractController {

    public function list_get() {
        // 参数: status =>素材状态 1审核中，2 已通过，3以驳回；m_weix=>作者;title=>素材标题;

        $page   = I('get.page', 1, 'intval');
        $limit  = I('get.limit', 10, 'intval');
        $material_model = D('Share/Material');

        //分页
        $start = ($page - 1) * $limit;
        $list = $material_model->getMaterialList($start, $limit);
        $count = $material_model->getCount();
        $pages = ceil($count / $limit);

        if ($count < 1) {
            return false;
        }

        // p($_SERVER);
        $pages = ceil($count / $limit);
        $this->_result = array(
            'list' => $list,
            'count' => $count,//总数据条数
            'page' => $page, //当前页
            'pages'=> $pages, //总也
            'limit' => $limit, //每页显示数量
        );
        return true;
    }

    //素材详情
    public function detail_get() {
        // 参数: 素材id=>material_id状
        $material_id = isset($_GET['material_id']) ? intval($_GET['material_id']) : 0;
        // $material_id = I('get.material_id', 0, 'intval');
        if(empty($material_id)) {
            E('_ERR_SHARE_MATERIAL_ID');
        }
        $model = M('Material');
        $re = $model->find($material_id);
        if (!empty($re)){
            $member = M('Member')->field('m_username,m_weixin')->find($re['m_uid']);
            !empty($member) ? $re['username'] = $member['m_username'] : $re['username'] = '';
            $re['arr_file'] = array();
            if(!empty($re['file_ids'])) {
                $re['arr_file'] = $this->getArrFile($re['file_ids']);
            }

            $re['desc'] = htmlspecialchars_decode($re['desc']);
            $this->_result = $re;
            return true;
        } else {
            return false;
        }

    }


    public function updateStatus_post() {
        $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
        if(empty($material_id)) {
            E('_ERR_SHARE_MATERIAL_ID'); //素材id参数错误
        }

        $material_model = D('Share/Material');
        if($material_model->updateStatus()) {
            return true;
        }
    }

    public function set_get() {
        $this->_result = array(
            'url' => $this->getDomin().'/pc', //素材推广url
        );
        return true;
    }


    function getArrFile($file_ids) {
        $arr_file = M('CommonAttachment')->field('at_id,at_filename,at_attachment,at_filesize')->where('at_id in ('.$file_ids.')')->select();

        return $arr_file = $this->getExtension($arr_file);
    }

    //获取域名
    function getDomin() {
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        return $domain = C('PROTOCAL') . $setting['domain'];
    }
    //获取扩展名
    function getExtension($arr_file) {
        $domain = $this->getDomin();
        $arr = array();
        if(count($arr_file) > 0) {
            foreach($arr_file as $k=>$v) {
                foreach($v as $tmp_k=>$tmp_v) {
                    $arr[$k][$tmp_k] = $tmp_v;
                    if('at_attachment' == $tmp_k) {
                        $ext = pathinfo(APP_PATH.'/'.$tmp_v); //文件绝对路径
                        $arr[$k]['ext'] = $ext['extension'];
                    }
                    if('at_id' == $tmp_k) { //返回文件下载地址
                        $arr[$k]['down_url'] = $domain.'/attachment/read/'.$tmp_v;
                    }
                }
            }
        }
        return $arr;
    }
}