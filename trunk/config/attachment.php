<?php
/**
 * 附件的系统默认配置
 *
 * 此配置可以被具体的app配置所覆盖
 *
 * 调用方式:
 * config::get('global.attachment.key');
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/** 用户站点信息缓存目录 */
$conf['dir'] = '/data/attachments';

/** 允许上传的文件类型 */
$conf['file_type'] = array('jpg', 'png', 'jpeg', 'gif', 'zip', 'rar', 'txt', 'doc', 'gz', 'tar', '7z', 'pdf');

/** 允许上传文件的最大值(字节) */
$conf['max_size'] = 800 * 1024;
