<?php

/**
 * 砸蛋抽奖模块
 *
 * [WeiZan System] Copyright (c) 2013 012wz.com
 */
defined('IN_IA') or exit('Access Denied');

class Lanyu_appointmentModuleSite extends WeModuleSite {
	//欢迎页设置
	public function doWebWelcome() {
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';

		if($op == 'index'){
			$welcome = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_welcome')." WHERE weid =".$_W['uniacid']);


			include $this->template('web/welcome');
		}

		if($op == 'add'){
			$welcome = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_welcome')." WHERE weid =".$_W['uniacid']);

			include $this->template('web/welcome_add');
		}

		if($op == 'post'){

			$id = $_GPC['id'];
			$content = $_GPC['content'];
			$title = $_GPC['title'];
			$insert = array(
					'weid' => $_W['uniacid'],
					'content' => $content,
					'title' => $title,
			);
			if (!empty($id)) {
				pdo_update('lanyu_appointment_welcome', $insert, array('id' => $id));
			} else {
				pdo_insert('lanyu_appointment_welcome', $insert);
			}
			message('操作成功！', $this->createWebUrl('welcome', array('op' => 'index')), 'success');
		}


	}

	//店铺设置
	public function doWebStore(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';

		if($op == 'index'){
			 $list = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_store')." WHERE weid=".$_W['uniacid']);


			include $this->template('web/store_list');
		}

		if($op == 'post'){
			$id = intval($_GPC['id']);
			if ($id > 0) {
				$theone = pdo_fetch('SELECT * FROM ' . tablename('lanyu_appointment_store') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));

				$one_region = pdo_fetch("SELECT * FROM ".tablename('lanyu_region')." WHERE weid=".$_W['uniacid']." AND id=".$theone['region_id']);
				$region_name = $this->find_region($one_region['parent_id'],$one_region['name'],$one_region['weid']);
			} else {
				$theone = array('status' => 1, 'listorder' => 0);
			}
			//店铺类型
			$region = pdo_fetchall("SELECT * FROM ".tablename('lanyu_region')." WHERE weid =".$_W['uniacid']." AND parent_id = 1");
			if(checksubmit('submit')){
				$listorder = $_GPC['listorder'];
				$store_name = trim($_GPC['store_name']) ? trim($_GPC['store_name']) : message('请填写店铺名称！');
				$address = trim($_GPC['address']) ? trim($_GPC['address']) : message('请填写店铺地址！');
				$traffic = trim($_GPC['traffic']) ? trim($_GPC['traffic']) : message('请填写交通指导！');
				$status = intval($_GPC['status']);
				$region_id = $_GPC['region_id'] != 0 ? $_GPC['region_id'] : message('请选择区域');
				$insert = array(
						'name' => $store_name,
						'listorder' => $listorder,
						'address' => $address,
						'region_id' => $region_id,
						'status' => $status,
						'weid' => $_W['uniacid'],
						'traffic' => $traffic
				);
				if (empty($id)) {
					pdo_insert('lanyu_appointment_store', $insert);
					!pdo_insertid() ? message('保存数据失败, 请稍后重试.', 'error') : '';
				} else {
					if (pdo_update('lanyu_appointment_store', $insert, array('id' => $id)) === false) {
						message('更新店铺数据失败, 请稍后重试.', 'error');
					}
				}
				message('更新店铺数据成功！', $this->createWebUrl('store', array('op' => 'index')), 'success');
			}
			include $this->template('web/store_post');
		}

		//根据id查下级的分类
		if($op == 'get_under_region'){
			$parent_id = $_GPC['parent_id'];
			$type_id = $_GPC['type_id'];
			$type_id++;
			$under = pdo_fetchall("SELECT * FROM ".tablename('we7car_region')." WHERE weid =".$_W['uniacid']." AND parent_id =".$parent_id);
			if(!empty($under)){
				echo "<div class='col-sm-2 col-xs-12' >";
				echo "<select class='form-control' id='region_id".$type_id."' onchange='get_under_region(".$type_id.");'>";
				echo "<option value='0'>请选择所在区域</option>";
				foreach($under as $un){
					echo "<option value='".$un['id']."' >".$un['name']."</option>";
				}
				echo "</select></div>";
			}else{
				echo 111;
			}
			exit;
		}

	}

	//档期设置
	public function doWebTimes(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';

		if($op == 'index'){
			$stores = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_store')." WHERE weid =".$_W['uniacid']);
			$first_store = current($stores);
			$store_id = empty($_GPC['store_id'])?$first_store['id']:$_GPC['store_id'];
			$times = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_times')." WHERE weid=".$_W['uniacid']." AND store_id = ".$store_id." ORDER BY listorder");
			include $this->template('web/times');
		}

		if($op == 'post'){
			$id = intval($_GPC['id']);
			$stores = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_store')." WHERE weid =".$_W['uniacid']);
			if ($id > 0) {
				$theone = pdo_fetch('SELECT * FROM ' . tablename('lanyu_appointment_times') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));

			} else {
				$theone = array( 'listorder' => 10);
			}
			if(checksubmit('submit')){
				$listorder = $_GPC['listorder'];
				$time = $_GPC['time'];
				$store_id = $_GPC['store_id'];
				$effective_times = $_GPC['effective_times'];
				$insert = array(
					'listorder' => $listorder,
					'time' => $time,
					'effective_times' => $effective_times,
					'store_id' => $store_id,
					'weid' => $_W['uniacid'],
				);
				if(empty($id)){
					pdo_insert('lanyu_appointment_times',$insert);
					!pdo_insertid() ? message('保存数据失败, 请稍后重试.', 'error') : '';
				}else{
					if (pdo_update('lanyu_appointment_times',$insert,array('id'=> $id)) == false){
						message('更新店铺数据失败, 请稍后重试.', 'error');
					}
				}
				message('更新数据成功！', $this->createWebUrl('times', array('op' => 'index')), 'success');
			}

			include $this->template('web/times_post');
		}

		//不能预约的时间段
		if($op == 'out_time'){
			$day = empty($_GPC['day'])?date("Y-m-d",time()):$_GPC['day'];
			$stores = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_store')." WHERE weid =".$_W['uniacid']);
			$first_store = current($stores);
			$store_id = empty($_GPC['store_id'])?$first_store['id']:$_GPC['store_id'];
			//查找时间
			$times = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_times')." WHERE weid =".$_W['uniacid']." AND store_id =".$store_id);
			$the_day = strtotime($day);
			$out_times = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_out_time')." WHERE weid =".$_W['uniacid']." AND app_day =".$the_day." AND store_id =".$store_id);
			$ids = array();
			foreach($out_times as $out){
				$ids[] = $out['time_id'];
			}
			foreach($times as $key=>$time){
				if(in_array($time['id'],$ids)){
					$times[$key]['status'] = true;
				}
			}
			include $this->template('web/out_times');
		}
		//设置时间
		if($op == 'to_out_time'){
			$time_id = $_GPC['time_id'];
			$day = $_GPC['day'];
			$type = $_GPC['type'];
			$store_id = $_GPC['store_id'];
			$day = strtotime($day);
			switch($type){
				case 1 : //增加
					$insert = array(
						'weid' => $_W['uniacid'],
						'create_time' => TIMESTAMP,
						'app_day' => $day,
						'time_id' => $time_id,
						'store_id' => $store_id,
					);
					if(pdo_insert('lanyu_appointment_out_time',$insert)){
						echo 111;
						exit;
					}else{
						echo 222;
						exit;
					}
					break;
				case 2 : //删除
					if(pdo_delete('lanyu_appointment_out_time',array('weid' => $_W['uniacid'], 'app_day' => $day, 'time_id' => $time_id,'store_id' => $store_id)) != false){
						echo 111;
						exit;
					}else{
						echo 222;
						exit;
					}
					break;
			}
			exit;
		}


	}


	//预约类型设置
	public function doWebType(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';

		if($op == 'index'){
			$list = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_type')." WHERE weid =".$_W['uniacid']);
			include $this->template('web/type_list');
		}

		//添加类型
		if($op == 'post'){
			$id = $_GPC['id'];
			if ($id > 0) {
				$theone = pdo_fetch('SELECT * FROM ' . tablename('lanyu_appointment_type') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));

			} else {
				$theone = array( 'listorder' => 10,'status' => 1);
			}

			if(checksubmit('submit')){
				$name = $_GPC['name'];
				$order_list = $_GPC['order_list'];
				$image = $_GPC['image'];
				$price = $_GPC['price'];
				$content = $_GPC['content'];
				$status = $_GPC['status'];
				$insert = array(
					'name' => $name,
					'order_list' => $order_list,
					'image' => $image,
					'price' => $price,
					'content' => $content,
					'status' => $status,
					'weid' => $_W['uniacid'],
				);
				if(empty($id)){
					pdo_insert('lanyu_appointment_type',$insert);
					!pdo_insertid() ? message('保存数据失败, 请稍后重试.', 'error') : '';
				}else{
					if (pdo_update('lanyu_appointment_type',$insert,array('id'=> $id)) == false){
						message('更新店铺数据失败, 请稍后重试.', 'error');
					}
				}
				message('更新数据成功！', $this->createWebUrl('type', array('op' => 'index')), 'success');
			}




			include $this->template('web/type_post');
		}
	}

	//根据id递归出区域
	function find_region($parent_id,$region_name,$weid){
		if($parent_id>1){
			$region = pdo_fetch("SELECT * FROM ".tablename('we7car_region')." WHERE id =".$parent_id." AND weid =".$weid);
			$region_name = $region['name'].'-'.$region_name;
			return $this->find_region($region['parent_id'],$region_name,$weid);
		}else{
			return $region_name;
		}
	}


	//手机端欢迎页面
	public function doMobileWelcome(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
		$open_id = $_W['openid'];

		if($op == 'index'){
			$welcome = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_welcome')." WHERE weid =".$_W['uniacid']);
			$content = html_entity_decode($welcome['content']);

			include $this->template('welcome');
		}

	}

	//类型选择列表
	public function doMobileTypes(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';

		if($op == 'index'){
			$types = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_type')." WHERE weid =".$_W['uniacid']." AND status = 1 ORDER BY order_list");

			include $this->template('types');
		}

		if($op == 'detail'){
			$type_id = $_GPC['type_id'];
			$type = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_type')." WHERE weid =".$_W['uniacid']." AND status = 1 AND id =".$type_id);
			if(empty($type)){
				message('类型不存在或者已经下架了',$this->createMobileUrl('types'),'error');
			}
			$content = html_entity_decode($type['content']);
			include $this->template('type_detail');
		}
	}

	//订单
	public function doMobileOrders(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';

		if($op == 'index'){
			echo 123;exit;
		}

		if($op == 'choose_store'){
			$type_id = $_GPC['type_id'];
			$stores = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_store')." WHERE weid=".$_W['uniacid']." AND status = 1 ORDER BY listorder");
			foreach($stores as $key=>$store){
				$one_region = pdo_fetch("SELECT * FROM ".tablename('lanyu_region')." WHERE weid=".$_W['uniacid']." AND id=".$store['region_id']);
				$region_name = $this->find_region($one_region['parent_id'],$one_region['name'],$one_region['weid']);
				$stores[$key]['region_name'] = $region_name;
			}
			include $this->template('store');
		}

		if($op == 'choose_day'){
			$type_id = $_GPC['type_id'];
			$store_id = $_GPC['store_id'];
			$month = empty($_GPC['month'])?date('m',time()):$_GPC['month'];
			$year = empty($_GPC['year'])?date('Y',time()):$_GPC['year'];
			//求出今个月以及今年剩下月份
			$this_month = date('m',time());
			$start_month = intval($this_month);
			$month_array = array();
			for($i = $start_month;$i<=12;$i++){
				$month_array[] = $i;
			}
			//求所在月份的剩余天数
			$days = array();
			if($month == $this_month){
				$start_day = date('d',time());
			}else{
				$start_day = 1;
			}
			$end_day = $this->_get_month_by_month($month);
			for($i = $start_day; $i<=$end_day; $i++){
				$days[] = $i;
			}

			include $this->template('choose_day');
		}

		//选择时间
		if($op == 'choose_time'){
			$type_id = $_GPC['type_id']; //类型id
			$store_id = $_GPC['store_id']; //店铺id
			$month = $_GPC['month']; //月份
			$day = $_GPC['day']; //日期
			$year = empty($_GPC['year']) ? date('Y',time()) : $_GPC['year'];
			$choose_day = $year."-".$month."-".$day;
			$time = strtotime($choose_day);
			//根据店铺，查找店铺对应的时间
			$times = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_times')." WHERE weid =".$_W['uniacid']." AND store_id =".$store_id." ORDER BY listorder");

			//查看这个日期这个店铺是否有设置不能预约的时间
			$out_time = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_out_time')." WHERE app_day =".$time." AND weid=".$_W['uniacid']." AND store_id =".$store_id);
			$out_ids = array();
			foreach($out_time as $t){
				$out_ids[] = $t['time_id'];
			}
			//将不能预约的标注出来
			foreach($times as $key=>$tt){
				if(in_array($tt['id'],$out_ids)){
					$times[$key]['status'] = 2;
				}else{
					$times[$key]['status'] = 1;
				}
			}

//			print_r($times);exit;


			include $this->template('choose_times');
		}

		//选择套餐
		if($op == 'to_package'){
			$day = $_GPC['day'];
			$type_id = $_GPC['type_id'];
			$store_id = $_GPC['store_id'];
			$time_id = $_GPC['time_id'];
			//先查类型是否有套餐
//			$type = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_type')." WHERE id =".$type_id." AND weid=".$_W['uniacid']);
			$under_type = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_package')." WHERE type_id =".$type_id." AND weid =".$_W['uniacid']);
			if(empty($under_type)){
				message('正在帮您确认信息',$this->createMobileUrl('orders',array('op' => 'to_order','day' => $day, 'type_id' => $type_id, 'store_id' => $store_id, 'time_id' => $time_id)),'success');
			}else{
				include $this->template('to_package');
			}
		}

		//确认订单
		if($op == 'to_order'){
			$day = $_GPC['day'];
			$type_id = $_GPC['type_id'];
			$store_id = $_GPC['store_id'];
			$time_id = $_GPC['time_id'];
			//查店铺
			$store = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_store')." WHERE id =".$store_id." AND weid =".$_W['uniacid']);
			$one_region = pdo_fetch("SELECT * FROM ".tablename('lanyu_region')." WHERE weid=".$_W['uniacid']." AND id=".$store['region_id']);
			$region_name = $this->find_region($one_region['parent_id'],$one_region['name'],$one_region['weid']);
			$store['region_name'] = $region_name;
			//查时间
			$time = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_times')." WHERE id =".$time_id." AND weid=".$_W['uniacid']);
			//查选择类型
			$types = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_type')." WHERE id in(".$type_id.") AND weid =".$_W['uniacid']);
			include $this->template('order_post');
		}
	}

	//根据月份计算天数
	function _get_month_by_month($month){
		switch($month){
			case 1 : return 31;
			case 2 : return 28;
			case 3 : return 31;
			case 4 : return 30;
			case 5 : return 31;
			case 6 : return 30;
			case 7 : return 31;
			case 8 : return 31;
			case 9 : return 30;
			case 10 : return 31;
			case 11 : return 30;
			case 12 : return 31;
		}
	}

}
