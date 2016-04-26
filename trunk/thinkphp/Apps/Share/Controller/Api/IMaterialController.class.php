<?php
namespace Share\Controller\Api;
use Common\Common\Pager;
class IMaterialController extends AbstractController {

    public function list_get() {
        // 参数: status =>素材状态 1审核中，2 已通过，3以驳回；m_weix=>作者;title=>素材标题;


        $page   = I('get.page', 1, 'intval');
        $limit  = I('get.limit', 10, 'intval');
        $material_model = D('Share/Material');

        //分页
        $start = ($page - 1) * $limit;

        /***模拟登陆参数**start**/
        $params = empty($this->_login->user) ? array('m_uid'=>2) : $this->_login->user;

        /***模拟登陆参数**start**/

        $list = $material_model->getMaterialList($start, $limit,$params);
        $count = $material_model->getCount($params);
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
        $re['reject_desc'] = '';
        if (!empty($re)){
            $member = M('Member')->field('m_username,m_weixin')->find($re['m_uid']);
            !empty($member) ? $re['username'] = $member['m_username'] : $re['username'] = '';
            $re['arr_file'] = array();
            if(!empty($re['file_ids'])) {
                $re['arr_file'] = $this->getArrFile($re['file_ids']);
            }

            if (3 == $re['status']) {
                $info = M("MaterialLog")->where('material_id='.$material_id .' AND status=3')->order('material_log_id DESC')->find();
                $re['reject_desc'] = !empty($info) ? $info['desc'] : '';
            }
            $re['desc'] = htmlspecialchars_decode($re['desc']);
            $this->_result = $re;
            return true;
        } else {
            return false;
        }

    }




    //验证参数
    function check() {
        $post   = I('post.');
        if(empty($post['title']) ) { //验证标题空
            E('_ERR_STRLEN_EMPEY');
        }
        if(mb_strlen($post['title'],'utf-8') > 64) { //验证标题长度
            E('_ERR_STRLEN_MAX');
        }
        if(!empty($post['file_ids'])) { //验证附件参数格式
            if(!preg_match('/^\d{1,}(,{1}\d{1,}){0,}\d{0,}$/',$post['file_ids'])){
                E('_ERR_FILE_ERROR');
            }
        }
    }

    //删除文件
    public function delFile_post($at_id) {
        $at_id   = I('post.at_id', 0, 'intval');
        if(empty($at_id)) {
            E('_ERR_PARAMS');
        }
        D('Share/Material')->delFile($at_id);
        return true;
    }

    public function add_post() {
        $this->check();

        $material_model = D('Share/Material');
        //如果获取不到用户信息那么模拟用户信息 m_uid = 2
        $arr_login = empty($this->_login->user) ? array('m_uid'=>2) : $this->_login->user;

        if($material_model->add($arr_login)) {
            return true;
        }
    }

    public function edit_post() {
        $this->check();

        $material_model = D('Share/Material');
        //用户信息
        $arr_login = empty($this->_login->user) ? array('m_uid'=>2) : $this->_login->user;
        if($material_model->edit($arr_login)) {
            return true;
        }
    }

    function getArrFile($file_ids) {
        $arr_file = M('CommonAttachment')->field('at_id,at_filename,at_attachment,at_filesize')->where('at_id in ('.$file_ids.')')->select();

        return $arr_file = $this->getExtension($arr_file);
    }

    //获取扩展名
    function getExtension($arr_file) {
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
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