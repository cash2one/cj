#!/usr/local/php/bin/php
<?php
/**
 * cron后台程序入口
 *
 * 执行某个程序的方法：
 *   $ php cron.php -n [src/include/backend/tool/下的文件名] [args]
 * 例如：
 *   $ php cron.php -n test
 *   $ php cron.php -n test -f 10 -t 20
 *   其中，参数使用$this->_opts['key']获取
 *
 * $Author$
 * $Id$
 */

define('CLI_CMD', 'cron');

require_once dirname(__FILE__).'/cmd.php';
