<?php
/**
 * voa_upgrade_ver15120411
 * $Author$
 * $Id$
 */

class voa_upgrade_ver15120411 extends voa_upgrade_base {


	public function __construct() {

		parent::__construct();
		$this->_ver = '15120411';
	}

	// 升级
	public function upgrade() {

        $this->_member_table();

		return true;
	}

	/**
	 * 用户表结构升级
	 */
	protected function _member_table() {
        logger::error('开始升级member表结构');

        // 判断用户表是否存在
        $row = $this->_db->query("SHOW TABLES LIKE 'oa_member'");
        if ($this->_db->fetch_row($row)) {
            // 应用表结构升级,新增pay_openid(企业支付openid)、m_source(用户来源1:扫码,2:系统,3:其它)
            $q = $this->_db->query("SHOW FIELDS FROM oa_member LIKE 'pay_openid'");
            if (!$row = $this->_db->fetch_row($q)) {
                $this->_db->query("ALTER TABLE `oa_member` ADD COLUMN `pay_openid` CHAR(64) NOT NULL DEFAULT '' COMMENT '微信企业支付用户openid' AFTER `m_openid`");
            }

            $q = $this->_db->query("SHOW FIELDS FROM oa_member LIKE 'm_source'");
            if (!$row = $this->_db->fetch_row($q)) {
                $this->_db->query("ALTER TABLE `oa_member` ADD COLUMN `m_source` TINYINT(1) NOT NULL DEFAULT 2 COMMENT '用户来源1:扫码,2:系统,3:其它' AFTER `m_qywxstatus`");
            }

            $q = $this->_db->query("SELECT * FROM oa_common_plugin WHERE cp_identifier='blessingredpack'");
            if (!$row = $this->_db->fetch_array($q)) {
            	$this->_db->query("INSERT INTO `oa_common_plugin` (`cp_identifier`, `cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`, `cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`, `cp_datatables`, `cp_directory`, `cp_url`, `cp_version`, `cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`, `cp_created`, `cp_updated`, `cp_deleted`) VALUES('blessingredpack',	1,	4,	'',	'',	1025,	0,	0,	'祝福红包',	'blessredpack.png',	'激励员工自发了解企业文化，员工、企业心连心；打破传统，让企业福利变得更生动诱人。',	'blessredpack*',	'blessredpack',	'blessredpack.php',	'0.1',	0,	0,	0,	1,	1417145069,	0,	0)");
            }
        }

        logger::error('结束升级member表结构');

		return true;
	}



}
