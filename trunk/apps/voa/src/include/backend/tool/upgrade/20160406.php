<?php
/**
 * 工作报告应用迭代.20160314
 * php -q tool.php -n upgrade -version 20160314 -epid vchangyi_oa
 * User: wowxavi
 * Date: 2016/03/14
 * Time: 15:33
 * Email: wowxavi@qq.com
 */

class execute {

    /** 数据库操作对象 */
    protected $_db = null;

    /** 表前缀 */
    protected $_tablepre = 'oa_';

    /** 当前站点系统设置 */
    protected $_settings = array();

    /** 来自命令行请求的参数 */
    protected $_options = array();

    /** 来自触发此脚本的父级参数 */
    protected $_params = array();

    /** 储存已执行的SQL语句，文件路径 */
    protected $_sql_logfile = '';

    /** 储存已执行SQL语句的恢复语句，文件路径 */
    protected $_sql_restore_logfile = '';

    /** 当前升级的应用信息 */
    private $__plugin = array();

    public function __construct() {

    }

    /**
     * 初始化环境参数
     * @param object $db 数据库操作对象
     * @param string $tablepre 表前缀
     * @param array  $settings 当前站点的setting
     * @param array  $options 传输进来的外部参数
     * @param array  $params 一些环境参数，来自触发执行本脚本
     * @see voa_backend_tool_upgrade::main()
     */
    public function init($db, $tablepre, $settings, $options, $params) {
        $this->_db = $db;
        $this->_tablepre = $tablepre;
        $this->_settings = $settings;
        $this->_options = $options;
        $this->_params = $params;
    }

    /**
     * 脚本执行的主方法，不同的升级脚本具体操作动作不相同
     * @return void
     */
    public function run() {

        error_reporting(E_ALL);
        $this->_cache_clear();

        // 获取应用信息
        $query = $this->_db->query("SELECT * FROM `oa_common_plugin` WHERE `cp_identifier`='dailyreport' LIMIT 1");
        $this->__plugin = $this->_db->fetch_array($query);
        $query = $this->_db->query("SHOW TABLES LIKE 'oa_dailyreport'");
        if ($this->_db->fetch_row($query)) {
            //初始化表
            $this->_init_table(); //ok
            //更新设置表结构
            $setting_data = "INSERT INTO `oa_dailyreport_setting` (`drs_key`, `drs_value`, `drs_type`, `drs_comment`, `drs_status`, `drs_created`, `drs_updated`, `drs_deleted`) VALUES
('wechat_menu_new','[{\"type\":\"view\",\"name\":\"新建报告\",\"url\":\"\/Dailyreport\/Frontend\/Index\/NewDailyreport\",\"form_name\":\"menu_1\"},{\"name\":\"我收到的\",\"form_name\":\"menu_2\",\"sub_button\":[{\"type\":\"view\",\"name\":\"与我相关的\",\"url\":\"\/Dailyreport\/Frontend\/Index\/AboutMe\",\"form_name\":\"menu_2_1\"},{\"type\":\"view\",\"name\":\"我负责的\",\"url\":\"\/Dailyreport\/Frontend\/Index\/Responsibles\",\"form_name\":\"menu_2_2\"}]},{\"name\":\"我发起的\",\"form_name\":\"menu_3\",\"sub_button\":[{\"type\":\"view\",\"name\":\"草稿\",\"url\":\"\/Dailyreport\/Frontend\/Index\/Draft\",\"form_name\":\"menu_3_1\"},{\"type\":\"view\",\"name\":\"已发出的\",\"url\":\"\/Dailyreport\/Frontend\/Index\/SendList\",\"form_name\":\"menu_3_2\"}]}]', '0', '新的微信菜单', '1', '1419249045', '0', '0'),
('wechat_menu_old','[{\"type\":\"view\",\"name\":\"新建报告\",\"url\":\"\/Dailyreport\/Frontend\/Index\/NewDailyreport\",\"form_name\":\"menu_1\"},{\"name\":\"我收到的\",\"form_name\":\"menu_2\",\"sub_button\":[{\"type\":\"view\",\"name\":\"与我相关的\",\"url\":\"\/Dailyreport\/Frontend\/Index\/AboutMe\",\"form_name\":\"menu_2_1\"},{\"type\":\"view\",\"name\":\"我负责的\",\"url\":\"\/Dailyreport\/Frontend\/Index\/Responsibles\",\"form_name\":\"menu_2_2\"}]},{\"name\":\"我发起的\",\"form_name\":\"menu_3\",\"sub_button\":[{\"type\":\"view\",\"name\":\"草稿\",\"url\":\"\/Dailyreport\/Frontend\/Index\/Draft\",\"form_name\":\"menu_3_1\"},{\"type\":\"view\",\"name\":\"已发出的\",\"url\":\"\/Dailyreport\/Frontend\/Index\/SendList\",\"form_name\":\"menu_3_2\"}]}]', '0', '旧的微信菜单用于还原', '1', '1419249045', '0', '0')";
            $this->_db->query($setting_data);
            //更新报告表
            $this->_db->query("ALTER TABLE `oa_dailyreport` MODIFY COLUMN `dr_type`  int(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '日报类型'");
            $this->_db->query("ALTER TABLE `oa_dailyreport` ADD COLUMN `dr_is_new`  tinyint(255) NULL DEFAULT '0' COMMENT '是否是新版报告1是0不是'");
            $this->_db->query("ALTER TABLE `oa_dailyreport` ADD COLUMN `dr_from_dr_id` int(10) NOT NULL DEFAULT '0' COMMENT '转发来源报告id 0没有来源 或者为旧版'");
            $this->_db->query("ALTER TABLE `oa_dailyreport` ADD COLUMN `dr_remark` varchar(300) DEFAULT '' COMMENT '转发备注'");
            $this->_db->query("ALTER TABLE `oa_dailyreport` ADD COLUMN `dr_forword_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转发人 id'");
            $this->_db->query("ALTER TABLE `oa_dailyreport` ADD COLUMN `dr_forword_uname` varchar(255) NOT NULL DEFAULT '' COMMENT '转发人姓名'");
            //更新post表
            $this->_db->query("ALTER TABLE `oa_dailyreport_post` ADD COLUMN `drp_comment_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论回复给谁'");
            $this->_db->query("ALTER TABLE `oa_dailyreport_post` ADD COLUMN `drp_comment_user_name` varchar(255) NOT NULL DEFAULT '' COMMENT '回复人的姓名'");
            $this->_db->query("ALTER TABLE `oa_dailyreport_post` ADD COLUMN `drp_comment_content` varchar(255) NOT NULL DEFAULT '' COMMENT '上一层评论的内容'");
            $this->_db->query("ALTER TABLE `oa_dailyreport_post` ADD COLUMN `drp_is_new` tinyint(255) NOT NULL DEFAULT '0' COMMENT '1是新的0不是'");
            $this->_db->query("ALTER TABLE `oa_dailyreport_post` ADD COLUMN `drp_new_message` text NOT NULL COMMENT '新版本的报告数据'");
            $this->_db->query("ALTER TABLE `oa_dailyreport_post` ADD COLUMN `drp_forword_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转发人 id'");
            $this->_db->query("ALTER TABLE `oa_dailyreport_post` ADD COLUMN `drp_forword_uname` varchar(255) NOT NULL DEFAULT '' COMMENT '转发人姓名'");
            $this->_db->query("ALTER TABLE `oa_dailyreport_mem` ADD COLUMN `get_level` tinyint(255) NOT NULL DEFAULT '1' COMMENT '1接收人0抄送人'");

            //插入模板默认数据
            $this->_db->query("INSERT INTO `oa_dailyreport_tpl` (`drt_id`, `drt_name`, `drt_switch`, `drt_departments`, `drt_status`, `drt_module`, `drt_sort`, `drt_created`, `drt_updated`, `drt_deleted`) VALUES
('1', '日报', '1', '[]', '1', '[{\"type\":\"textarea\",\"title\":\"报告内容\",\"value\":[],\"is_null\":1,\"name\":\"textarea_1\"},{\"type\":\"img\",\"title\":\"附件\",\"value\":[{\"max\":9}],\"is_null\":0,\"name\":\"img_2\"}]', '1', '1458193484', '0', '0'),
('2', '周报', '1', '[]', '1', '[{\"type\":\"textarea\",\"title\":\"报告内容\",\"value\":[],\"is_null\":1,\"name\":\"textarea_1\"},{\"type\":\"img\",\"title\":\"附件\",\"value\":[{\"max\":9}],\"is_null\":0,\"name\":\"img_2\"}]', '2', '1458196022', '0', '0'),
('3', '月报', '1', '[]', '1', '[{\"type\":\"textarea\",\"title\":\"报告内容\",\"value\":[],\"is_null\":1,\"name\":\"textarea_1\"},{\"type\":\"img\",\"title\":\"附件\",\"value\":[{\"max\":9}],\"is_null\":0,\"name\":\"img_2\"}]', '3', '1458196033', '0', '0'),
('4', '季报', '1', '[]', '1', '[{\"type\":\"textarea\",\"title\":\"报告内容\",\"value\":[],\"is_null\":1,\"name\":\"textarea_1\"},{\"type\":\"img\",\"title\":\"附件\",\"value\":[{\"max\":9}],\"is_null\":0,\"name\":\"img_2\"}]', '4', '1458196041', '0', '0'),
('5', '年报', '1', '[]', '1', '[{\"type\":\"textarea\",\"title\":\"报告内容\",\"value\":[],\"is_null\":1,\"name\":\"textarea_1\"},{\"type\":\"img\",\"title\":\"附件\",\"value\":[{\"max\":9}],\"is_null\":0,\"name\":\"img_2\"}]', '5', '1458196047', '0', '0'),
('6', '其它', '1', '[]', '1', '[]', '0', '1458196047', '0', '0')");
            // 应用菜单升级
            $this->_plugin_cpmenu();
            //微信菜单升级
            $this->_plugin_wxqymenu();
            // 清除缓存
            $this->_clear_dailyreport();
        }

        return true;
    }

	// 清除工作报告缓存
	protected function _clear_dailyreport() {

		$query = $this->_db->query("SELECT * FROM `oa_common_setting` WHERE `cs_key`='domain'");
		$sets = $this->_db->fetch_array($query);
		$domains = explode('.', $sets['cs_value']);
		startup_env::set('domain', $domains[0]);
		voa_h_cache::get_instance()->remove('plugin.dailyreport.setting', 'oa');
		$query = $this->_db->query("SELECT * FROM `oa_common_syscache`");
		while ($row = $this->_db->fetch_array($query)) {
			if (preg_match('/^adminergroupcpmenu/i', $row['csc_name'])) {
				voa_h_cache::get_instance()->remove($row['csc_name'], 'oa');
			}
		}

		return true;
	}

    protected function _init_table() {
        //添加模板表
        $dr_tpl_table = "CREATE TABLE IF NOT EXISTS `oa_dailyreport_tpl` (
  `drt_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板类型Id',
  `drt_name` varchar(255) NOT NULL DEFAULT '' COMMENT '模板名称(用于显示到前端)',
  `drt_switch` tinyint(255) NOT NULL DEFAULT '1' COMMENT '1启用0禁用',
  `drt_departments` text NOT NULL COMMENT '存储可见部门id 逗号分开, 为空则为全公司',
  `drt_status` tinyint(255) NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已更新, 3=已删除',
  `drt_module` text COMMENT '组件配置',
  `drt_sort` smallint(255) unsigned NOT NULL DEFAULT '0' COMMENT '序号(排序)',
  `drt_created` int(255) unsigned NOT NULL DEFAULT '0' COMMENT '模板创建的时间',
  `drt_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `drt_deleted` int(255) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`drt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工作日报模板表'";
        $this->_db->query($dr_tpl_table);
        //添加模板部门表
        $dr_tpl_dp_table = "CREATE TABLE IF NOT EXISTS `oa_dailyreport_tpl_department` (
  `drt_id` int(11) NOT NULL DEFAULT '0' COMMENT '模板id',
  `dp_id` int(11) NOT NULL COMMENT '部门id',
  `dp_is_show` tinyint(255) NOT NULL DEFAULT '1' COMMENT '1启用0隐藏',
  KEY `drt_id_dp_id` (`drt_id`,`dp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='部门模板关系表'";
        $this->_db->query($dr_tpl_dp_table);
        //添加草稿表
        $drx_table = "CREATE TABLE IF NOT EXISTS `oa_dailyreport_draftx` (
  `drd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `m_uid` int(32) unsigned NOT NULL DEFAULT '0' COMMENT '草稿所属人的id',
  `drd_title` varchar(81) NOT NULL COMMENT '报告主题',
  `drt_module` text NOT NULL COMMENT '报告内容',
  `drd_a_uid` text NOT NULL COMMENT '接收人',
  `drd_cc_uid` text NOT NULL COMMENT '抄送人uid, 以\",\"分隔',
  `drd_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1=正常，2=已删除',
  `drd_created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `drd_updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `drd_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `drt_id` int(11) NOT NULL DEFAULT '0' COMMENT '模板类型',
  PRIMARY KEY (`drd_id`),
  KEY `m_uid` (`drd_status`),
  KEY `m_openid` (`m_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='报告草稿表'";
        $this->_db->query($drx_table);
    }

    /**
     * 更新微信企业号的自定义菜单
     */
    protected function _plugin_wxqymenu() {

        $api_url = 'http://' . $this->_settings['domain'] . '/api/common/post/updatewxqymenu/';

        $result = array();
        $post = array(
            'pluginid' => $this->__plugin['cp_pluginid'],
            'agentid' => $this->__plugin['cp_agentid'],
            'identifier' => 'dailyreport'
        );
        if (!$this->__get_json_by_post($result, $api_url, $post)) {
            logger::error($api_url . "||" . print_r($result, true));
        }

        return true;
    }

    /**
     * 读取远程api数据
     * @param unknown $data
     * @param unknown $url
     * @param string $post
     * @return boolean
     */
    private function __get_json_by_post(&$data, $url, $post = '') {

        $snoopy = new snoopy();
        $result = $snoopy->submit($url, $post);
        // 如果读取错误
        if (!$result || 200 != $snoopy->status) {
            logger::error('$snoopy->submit error: ' . $url . '|' . $result . '|' . $snoopy->status);
            return false;
        }

        $data = (array) json_decode($snoopy->results, true);
        if (empty($data) || !empty($data['errcode'])) {
            logger::error('$snoopy->submit error: ' . $url . '|' . $snoopy->results . '|' . $snoopy->status);
            return false;
        }

        if ($data['errcode'] == '45009') {
            // 如果接口请求超限，则稍等10秒重试
            echo '[...wait retry...]';
            sleep(mt_rand(6, 12));
            $data = array();
            return $this->__get_json_by_post($data, $url, $post);
        }

        return true;
    }

    /**
     * 应用菜单升级
     */
    protected function _plugin_cpmenu() {
        // 删除旧有的菜单
        $this->_db->query("DELETE FROM `oa_common_cpmenu` WHERE ccm_operation='dailyreport'");
        // 添加新的菜单
        $query = $this->_db->query("SELECT ccm_id FROM `oa_common_cpmenu` WHERE ccm_operation='dailyreport'");
        $menu = $this->_db->fetch_row($query);
        if (!$menu) {
            $menu = " INSERT INTO `oa_common_cpmenu` (`cp_pluginid`, `ccm_system`, `ccm_module`, `ccm_operation`, `ccm_subop`, `ccm_type`, `ccm_default`, `ccm_name`, `ccm_icon`, `ccm_display`, `ccm_displayorder`, `ccm_subnavdisplay`, `ccm_status`, `ccm_created`, `ccm_updated`, `ccm_deleted`) VALUES ('11', '0', 'office', 'dailyreport', '', 'operation', '1', '工作报告', '', '1', '1011', '1', '1', '1458830681', '1458830681', '0'),('11', '0', 'office', 'dailyreport', 'main', 'subop', '1', '报告列表', 'fa-list', '1', '105', '1', '1', '1458830681', '1458830681', '0'),('11', '0', 'office', 'dailyreport', 'template', 'subop', '0', '报告模板设置', 'fa-file-text-o', '1', '105', '1', '1', '1458830681', '1458830681', '0'),( '11', '0', 'office', 'dailyreport', 'wechat', 'subop', '0', '微信菜单设置', 'fa-cog', '1', '105', '1', '1', '1458830681', '1458830681', '0')";
            $this->_db->query($menu);
        }
    }

    /**
     * 清理缓存
     */
    protected function _cache_clear() {

        // 当前站点的缓存目录
        $cachedir = $this->_params['cachedir'];

        $handle = opendir($cachedir);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if (false === stripos($file, 'dbconf.inc.php')) {
                    @unlink($cachedir . '/' . $file);
                }
            }
            closedir($handle);
        }
    }
}