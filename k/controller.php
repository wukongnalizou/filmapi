<?php
/* 
 * 控制器父类
 * @Package Name: Controller
 * @Author: Keboy xolox@163.com
 * @Modifications:No20170629
 *
 */

class Controller {
	public static $data = array(
		'webconfig' => array()
	);
	public function __construct() {
		$data = post();
		if ( array_isset($data,'formid') ) {
			if ( $data['formid'] && $data['formid'] != 'the formId is a mock one' ) rlog('addModelformid',array(
				'openid' => array_isset($data,'openid') ? $data['openid'] : (array_isset($data,'open_id') ? $data['open_id'] : ''),
				'formid' => $data['formid']
			));
		}
	}
}