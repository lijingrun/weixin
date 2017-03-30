<?php
/**
 * 企事业单位内部订餐系统模块处理程序
 *
 * @author ljr
 * @url http://www.haoid.cn/
 */
defined('IN_IA') or exit('Access Denied');

class Ljr_business_orderModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微擎文档来编写你的代码
	}
}