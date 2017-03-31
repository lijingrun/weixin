<?php

/**
 * 砸蛋抽奖模块
 *
 * [WeiZan System] Copyright (c) 2013 012wz.com
 */
defined('IN_IA') or exit('Access Denied');

class Ljr_business_orderModuleSite extends WeModuleSite {
	//部门管理
	public function doWebDepartment(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';

		if($op == 'index'){
			$departments = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_department')." WHERE weid =".$_W['uniacid']);
			include $this->template('web/department_list');
		}

		if($op == 'add') {

			$id = $_GPC['id'];
			$departments = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_department')." WHERE weid =".$_W['uniacid']);
			if(!empty($id)){
				$department = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_department')." WHERE id =".$id." AND weid =".$_W['uniacid']);
			}else{
				$department['list_order'] = 255;
				$department['status'] = 1;
			}
			if(checksubmit('submit')){
				$name = $_GPC['name'];
				$parent_id = $_GPC['parent_id'] ? $_GPC['parent_id'] : 0;
				$status = $_GPC['status'];
				$id = $_GPC['id'];
				$list_order = $_GPC['list_order'];
				if(empty($name)){
					message('请输入正确的信息！',$this->createWebUrl('department',array('op' => 'add','id' => $id)),'error');
				}
				$insert = array(
					'name' => $name,
					'parent_id' => $parent_id,
					'status' => $status,
					'list_order' => $list_order,
					'weid' => $_W['uniacid'],
				);
				if(empty($id)){
					if(pdo_insert('ljr_business_order_department',$insert)){
						message('操作成功！','success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('department',array('op' => 'add')),'error');
					}
				}else{
					if(pdo_update('ljr_business_order_department',$insert,array('id' => $id))){
						message('操作成功！',$this->createWebUrl('department'),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('department',array('op' => 'add','id' => $id)),'error');
					}
				}
			}



			include $this->template('web/department_add');
		}
	}

	//职位管理
	public function doWebPosition(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
		if($op == 'index'){
			$positions = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_position')." WHERE weid =".$_W['uniacid']." ORDER BY list_order");

			include $this->template('web/position_list');
		}

		//增加 职位
		if($op == 'add'){
			$id = $_GPC['id'];
			$departments = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_department')." WHERE weid =".$_W['uniacid']);
			if(!empty($id)){
				$the_one = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_position')." WHERE id =".$id." AND weid =".$_W['uniacid']);
			}else{
				$the_one['list_order'] = 255;
				$the_one['status'] = 1;
			}
			if(checksubmit('submit')){
				$name = $_GPC['name'];
				$department_id = $_GPC['department_id'];
				$status = $_GPC['status'];
				$id = $_GPC['id'];
				$list_order = $_GPC['list_order'];
				if(empty($name) || empty($department_id)){
					message('请输入正确的信息！',$this->createWebUrl('position',array('op' => 'add','id' => $id)),'error');
				}
				$insert = array(
					'name' => $name,
					'department_id' => $department_id,
					'status' => $status,
					'list_order' => $list_order,
					'weid' => $_W['uniacid'],
				);
				if(empty($id)){
					if(pdo_insert('ljr_business_order_position',$insert)){
						message('操作成功！',$this->createWebUrl('position'),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('position',array('op' => 'add')),'error');
					}
				}else{
					if(pdo_update('ljr_business_order_position',$insert,array('id' => $id))){
						message('操作成功！',$this->createWebUrl('position'),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('position',array('op' => 'add','id' => $id)),'error');
					}
				}
			}

			include $this->template('web/position_add');
		}

	}

	//人员
	public function doWebStaff(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
		$page = empty($_GPC['page'])? 1 : $_GPC['page'];
		if($op == 'index'){
			$staffs = pdo_fetchall("SELECT s.*,d.name as d_name,p.name as p_name FROM ".tablename('ljr_business_order_staff')." AS s,".tablename('ljr_business_order_position')." AS p,".tablename('ljr_business_order_department')." AS d WHERE s.weid = ".$_W['uniacid']." AND s.position_id = p.id AND s.department_id = d.id ORDER BY d.id ");
			include $this->template('web/staff_list');
		}

		//增加人员账号
		if($op == 'add'){
			$departments = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_department')." WHERE weid =".$_W['uniacid']);
			$id = $_GPC['id'];
			if($id > 0) {
				$the_one = pdo_fetch("SELECT * FROM " . tablename('ljr_business_order_staff') . " WHERE id =" . $id . " AND weid =" . $_W['uniacid']);
			}else{
				$the_one['sex'] = 1;
				$the_one['status'] = 1;
				$the_one['list_order'] = 255;
			}
			if(!empty($the_one['position_id'])){
				$position = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_position')." WHERE id =".$the_one['position_id']." AND weid =".$_W['uniacid']);
			}else{
				$position['name'] = '请选择职位';
				$position['id'] = 0;
			}
			if(checksubmit('submit')){
				$id = $_GPC['id'];
				$department_id = $_GPC['department_id'];
				$position_id = $_GPC['position_id'];
				$name = $_GPC['name'];
				$sex = $_GPC['sex'];
				$image = $_GPC['image'];
				$password = $_GPC['password'];
				$list_order = $_GPC['list_order'];
				$status = $_GPC['status'];
				$insert = array(
					'department_id' => $department_id,
					'position_id' => $position_id,
					'name' => $name,
					'sex' => $sex,
					'image' => $image,
					'list_order' => $list_order,
					'status' => $status,
					'weid' => $_W['uniacid'],
				);
				if(!empty($password)){
					$insert['password'] = md5($password);
				}
				if($id > 0){
					if(pdo_update('ljr_business_order_staff',$insert,array('id' => $id))){
						message('操作成功！',$this->createWebUrl('staff',array('op' => 'index')),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('staff',array('op' => 'add','id' => $id)),'error');
					}
				}else{
					if(pdo_insert('ljr_business_order_staff',$insert)){
						message('操作成功！',$this->createWebUrl('staff',array('op' => 'index')),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('staff',array('op' => 'add')),'error');
					}
				}
			}

			include $this->template('web/staff_add');
		}

		//根据部门id查职位
		if($op == 'find_positions'){
			$id = $_GPC['department_id'];
			$positions = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_position')." WHERE weid =".$_W['uniacid']." AND department_id =".$id." AND status = 1");
			if(!empty($positions)){
				foreach($positions as $position){
					echo "<option value='".$position['id']."'>".$position['name']."</option>";
				}
			}else{
				echo "<option>该部门没有职位信息</option>";
			}
			exit;
		}


	}


	//订餐设置
	public function doWebOrder_system(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';

		if($op == 'index'){
			$types = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_type')." WHERE weid = ".$_W['uniacid']);

			include $this->template('web/system');
		}

		if($op == 'add_type'){
			$id = $_GPC['id'];
			if(empty($id)){
				$the_one['status'] = 1;
				$the_one['list_order'] = 255;
			}else{
				$the_one = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_type')." WHERE weid = ".$_W['uniacid']." AND id =".$id);
			}
			if(checksubmit('submit')){
				$name = $_GPC['name'];
				$list_order = $_GPC['list_order'];
				$status = $_GPC['status'];
				$image = $_GPC['image'];
				$id = $_GPC['id'];
				$begin = $_GPC['begin'];
				$end = $_GPC['end'];
				$insert = array(
					'name' => $name,
					'list_order' => $list_order,
					'image' => $image,
					'status' => $status,
					'weid' => $_W['uniacid'],
					'begin_time' => $begin,
					'end_time' => $end,
				);
				if($id > 0){
					if(pdo_update('ljr_business_order_type',$insert,array('id' => $id))){
						message('操作成功！',$this->createWebUrl('order_system',array('op' => 'index')),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('order_system',array('op' => 'add_type','id' => $id)),'error');
					}
				}else{
					if(pdo_insert('ljr_business_order_type',$insert)){
						message('操作成功！',$this->createWebUrl('order_system',array('op' => 'index')),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('order_system',array('op' => 'add_type','id' => $id)),'error');
					}
				}
			}

			include $this->template('web/add_type');
		}

		//订餐周期设置（每个星期的星期几可以订餐)
		if($op == 'week'){
			$the_one = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_week')." WHERE weid =".$_W['uniacid']);
			if(!empty($the_one)) {
				$weeks = explode(',', $the_one['week']);
			}
			if(empty($the_one['time'])){
				$the_one['time'] = 0;
			}
			if(checksubmit('submit')){
				$id = $_GPC['id'];

				$insert = array(
					'week' => implode(',',$_GPC['week']),
					'time' => $_GPC['time'],
					'weid' => $_W['uniacid'],
				);
				if($id > 0){
					if(pdo_update('ljr_business_order_week',$insert,array('id' => $id))){
						message('操作成功！',$this->createWebUrl('order_system',array('op' => 'week')),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('order_system',array('op' => 'week')));
					}
				}else{
					if(pdo_insert('ljr_business_order_week',$insert)){
						message('操作成功！',$this->createWebUrl('order_system',array('op' => 'week')),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('order_system',array('op' => 'week')));
					}
				}
			}
			include $this->template('web/week');
		}

		//设置菜式
		if($op == 'dishes'){
			$dishes = pdo_fetchall("SELECT t.name as t_name,d.* FROM ".tablename('ljr_business_order_dishes')." AS d,".tablename('ljr_business_order_type')." AS t WHERE d.weid =".$_W['uniacid']." AND t.id = d.type_id");


			include $this->template('web/dishes');
		}

		//增加菜式
		if($op == 'dishes_add'){
			$types = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_type')." WHERE weid=".$_W['uniacid']." AND status = 1");

			$id = $_GPC['id'];
			if($id > 0) {
				$the_one = pdo_fetch("SELECT * FROM " . tablename('ljr_business_order_dishes') . " WHERE weid =" . $_W['uniacid'] . " AND id =" . $id);
				if(!empty($the_one['week'])){
					$the_one['status'] = 1;
				}else{
					$the_one['status'] = 0;
				}
			}else{
				$the_one['status'] = 1;
			}

			if(checksubmit('submit')){
				$name = $_GPC['name'];
				$day = strtotime($_GPC['day']);
				$image = $_GPC['image'];
				$type_id = $_GPC['type_id'];
				$status = $_GPC['status'];
				$id = $_GPC['id'];
				if($status == 1){
					$week = date('w',$day);
				}
				$insert = array(
					'name' => $name,
					'day' => $day,
					'weid' => $_W['uniacid'],
					'image' => $image,
					'type_id' => $type_id,
					'week' => $week,
				);
				if($id > 0){
					if(pdo_update('ljr_business_order_dishes',$insert,array('id' => $id))){
						message('操作成功！',$this->createWebUrl('order_system',array('op' => 'dishes')),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('order_system',array('op' => 'add_dishes')),'error');
					}
				}else{
					if(pdo_insert('ljr_business_order_dishes',$insert)){
						message('操作成功！',$this->createWebUrl('order_system',array('op' => 'dishes')),'success');
					}else{
						message('服务器繁忙，请稍后重试！',$this->createWebUrl('order_system',array('op' => 'add_dishes')),'error');
					}
				}
			}

			include $this->template('web/dishes_add');
		}

	}

	//订单查询
	public function doWebOrders(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'appointment';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		if($op == 'index'){
			$sql = " FROM ".tablename('ljr_business_order_orders')." AS o,".tablename('ljr_business_order_staff')." AS s,".tablename('ljr_business_order_department')." AS d,.".tablename('ljr_business_order_type')." AS t,".tablename('ljr_business_order_position')." AS p  WHERE o.weid = ".$_W['uniacid']." AND o.user_id = s.id AND s.department_id = d.id AND o.type_id = t.id AND s.position_id = p.id";
			$departments = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_department')." WHERE weid =".$_W['uniacid']." AND status = 1");
			$types = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_type')." WHERE weid =".$_W['uniacid']);

			$department_id = $_GPC['department_id'];
			$begin_day = $_GPC['begin_day'];
			$end_day = $_GPC['end_day'];
			$type_id = $_GPC['type_id'];
			if(!empty($department_id)){
				$sql .= " AND d.id =".$department_id;
			}
			if(!empty($begin_day)){
				$sql .= " AND o.create_time >=".strtotime($begin_day);
			}
			if(!empty($end_day)){
				$sql .= " AND o.create_time <=".strtotime($end_day);
			}
			if(!empty($type_id)){
				$sql .= " AND o.type_id =".$type_id;
			}

			$total = pdo_fetchcolumn("SELECT COUNT(*)".$sql);
			$pager = pagination($total,$pindex,$psize);
			$orders = pdo_fetchall("SELECT s.name as username,d.name as d_name,t.name as t_name,p.name as p_name,o.* ".$sql ." ORDER BY o.create_time LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			foreach($orders as $key=>$order){
				$appointment = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_appointment')." WHERE weid =".$_W['uniacid']." AND time =".$order['time']." AND user_id =".$order['user_id']." AND type_id =".$order['type_id']);
				if(!empty($appointment)){
					$orders[$key]['appointment'] = true;
				}else{
					$orders[$key]['appointment'] = false;
				}
			}
			include $this->template('web/orders');
		}

		//订餐情况
		if($op == 'appointment'){
			$sql = " FROM ".tablename('ljr_business_order_appointment')." AS o,".tablename('ljr_business_order_staff')." AS s,".tablename('ljr_business_order_department')." AS d,.".tablename('ljr_business_order_type')." AS t,".tablename('ljr_business_order_position')." AS p  WHERE o.weid = ".$_W['uniacid']." AND o.user_id = s.id AND s.department_id = d.id AND o.type_id = t.id AND s.position_id = p.id";
			$departments = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_department')." WHERE weid =".$_W['uniacid']." AND status = 1");
			$types = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_type')." WHERE weid =".$_W['uniacid']);
			$department_id = $_GPC['department_id'];
			$begin_day = $_GPC['begin_day'];
			$end_day = $_GPC['end_day'];
			$type_id = $_GPC['type_id'];
			if(!empty($department_id)){
				$sql .= " AND d.id =".$department_id;
			}
			if(!empty($begin_day)){
				$sql .= " AND o.time >=".strtotime($begin_day);
			}
			if(!empty($end_day)){
				$sql .= " AND o.time <=".strtotime($end_day);
			}
			if(!empty($type_id)){
				$sql .= " AND o.type_id =".$type_id;
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*)".$sql);
			$pager = pagination($total,$pindex,$psize);
			$orders = pdo_fetchall("SELECT s.name as username,d.name as d_name,t.name as t_name,p.name as p_name,o.* ".$sql ." ORDER BY o.time LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

			include $this->template('web/appointment');
		}

	}



	//手机前端
	public function doMobileAppointment(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';

		if($op == 'index'){
			$user_id = $_SESSION['user_id'];
			if(empty($user_id)){
				if(!empty($_W['openid'])){
					$staff = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_staff')." WHERE openid =".$_W['openid']." AND WHERE weid =".$_W['uniacid']);
					if(!empty($staff)){
						$_SESSION['user_id'] = $staff['id'];
						message('',$this->createMobileUrl('appointment',array('op' => 'index')),'');
					}else{
						message('',$this->createMobileUrl('appointment',array('op' => 'login')),'');
					}
				}else{
					message('',$this->createMobileUrl('appointment',array('op' => 'login')),'');
				}
			}else {
				include $this->template('index');
			}
		}

		if($op == 'login'){


			if(checksubmit('submit')){
				$name = $_GPC['name'];
				$password = $_GPC['password'];
				$staff = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_staff')." WHERE weid =".$_W['uniacid']." AND name ='".$name."' AND password ='".md5($password)."' AND status = 1");
				if(!empty($staff)){
					$_SESSION['user_id'] = $staff['id'];
					if(!empty($_W['openid'])){
						pdo_update('ljr_business_order_staff',array('openid' => $_W['openid']),array('id' => $staff['id']));
					}
					message('',$this->createMobileUrl('appointment',array('op' => 'index')),'');
				}else{
					message('账号/密码错误，请重新输入！','','error');
				}
			}


			include $this->template('login');
		}

		//点餐，提前一日预定,可预定未来30日的
		if($op == 'appointment'){
			$month = intval(empty($_GPC['month'])?date('m',time()):$_GPC['month']);
			$year = empty($_GPC['year'])?date('Y',time()):$_GPC['year'];
			$today_time = strtotime(date('Y-m-d',time()));
			$days = array();

			//查可以星期几可以订餐
			$week = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_week')." WHERE weid =".$_W['uniacid']);
			$weeks = explode(',',$week['week']);
			for($i = 0; $i <= 10; $i++){
				$time = $today_time+(86400*$i);
				$the_one['year'] = date('Y',$time);
				$the_one['month'] = date('m',$time);
				$the_one['day'] = date('d',$time);
				$the_one['week'] = array('日','一','二','三','四','五','六')[date('w',$time)];
				if(in_array(date('w',$time),$weeks)){
					$the_one['status'] = 1;
				}else{
					$the_one['status'] = 2;
				}
				$the_one['day_time'] = $time;
				$days[] = $the_one;
			}
			$types = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_type')." WHERE weid =".$_W['uniacid']);
			foreach($days as $key => $day){
				foreach($types as $t){
					$tid = $t['id'];
					$the_time = strtotime($day['year']."-".$day['month']."-".$day['day']);
					$appointment = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_appointment')." WHERE user_id =".$_SESSION['user_id']." AND time =".$the_time." AND type_id ='".$tid."'");
					if(!empty($appointment)){
						$days[$key]['type'][$t['id']] = 1;
					}else{
						$days[$key]['type'][$t['id']] = 2;

					}
				}

			}

			include $this->template('appointment');
		}


		//选择订餐类型
		if($op == 'choose_type'){
			$day = $_GPC['day'];
			$types = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_type')." WHERE weid =".$_W['uniacid']." AND status = 1");
//			if($day == strtotime(date('Y-m-d',time()))){
				//查设置不准订餐时间
				$week = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_week')." WHERE weid =".$_W['uniacid']);
				if(!empty($week['time'])){
					$time = time() + $week['time']*3600;
				}else{
					$time = time();
				}
				foreach($types as $key=>$type){
					//计算开始时间
					$the_begin_time = strtotime(date('Y-m-d',$day)." ".$type['begin_time']);

					if($the_begin_time < $time){
						$types[$key]['can_choose'] = 2;
					}
				}
//			}
			//查选择的时间是否可以订餐
			$weeks = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_week')." WHERE weid =".$_W['uniacid']);
			$weeks = explode(',',$weeks['week']);
			$the_week = date('w',$day);
			if(!in_array($the_week,$weeks)){
				message('选择日期不在可就餐范围内！',$this->createMobileUrl('appointment',array('op'=>'appointment')),'error');
			}
			$week = date('w',$day);
			//查菜式
			$dishes = pdo_fetchall("SELECT s.*,t.name as t_name FROM ".tablename('ljr_business_order_dishes')." AS s,".tablename('ljr_business_order_type')." AS t WHERE s.weid =".$_W['uniacid']." AND (s.day =".$day." OR s.week =".$week.") AND s.type_id = t.id ORDER BY type_id");


			$appointment = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_appointment')." WHERE time =".$day." AND weid =".$_W['uniacid']." AND user_id =".$_SESSION['user_id']);
			$has_choose = array();
			if(!empty($appointment)){
				foreach($appointment as $ap){
					$has_choose[] = $ap['type_id'];
				}
			}
			if(checksubmit('submit')){
				$day = $_GPC['day'];
				$type = $_GPC['type'];
				pdo_delete('ljr_business_order_appointment',array('time'=> $day,'eat' => null));
				foreach($type as $t){
					$insert = array(
						'weid' => $_W['uniacid'],
						'user_id' => $_SESSION['user_id'],
						'type_id' => $t,
						'time' => $day,
						'create_time' => time(),
					);
					pdo_insert('ljr_business_order_appointment',$insert);
				}
				message('',$this->createMobileUrl('appointment',array('op' => 'appointment')),'');
			}

			include $this->template('choose_type');
		}

		//就餐（无论有无订餐都可以就餐，只是会显示是否有订餐）
		if($op == 'order'){
			$user_id = $_SESSION['user_id'];
			if(empty($user_id)){
				if(!empty($_W['openid'])){
					$staff = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_staff')." WHERE openid =".$_W['openid']." AND WHERE weid =".$_W['uniacid']);
					if(!empty($staff)){
						$_SESSION['user_id'] = $staff['id'];
						message('',$this->createMobileUrl('appointment',array('op' => 'order')),'');
					}else{
						message('',$this->createMobileUrl('appointment',array('op' => 'login')),'');
					}
				}else{
					message('',$this->createMobileUrl('appointment',array('op' => 'login')),'');
				}
			}else {
				//查现在的时间
				$time = strtotime(date('Y-m-d',time()));
//				 $time = date('H:m:s',time());
//				$now = strtotime($time);
//				$now = date('Y-m-d',$now);
//				echo $now;
//				exit;
				//查所有的类型，通过类型查时间
				$types = pdo_fetchall("SELECT * FROM ".tablename('ljr_business_order_type')." WHERE weid =".$_W['uniacid']." AND status = 1");
				foreach($types as $key=>$type){
					$begin = strtotime($type['begin_time']);
					$end = strtotime($type['end_time']);
					if($begin < time() && $end > time()){
						$the_type = $type;
						break;
					}
				}
				if(empty($the_type)){
					message('不在就餐时间内，不能进行该操作！',$this->createMobileUrl('appointment'),'error');
				}
				$check = pdo_fetch("SELECT * FROM ".tablename('ljr_business_order_orders')." WHERE user_id =".$user_id." AND weid = ".$_W['uniacid']." AND type_id =".$the_type['id']." AND time =".$time);
				if(!empty($check)){
					message('您已就餐，不需要重复操作！',$this->createMobileUrl('appointment'),'error');
				}
				$insert = array(
					'user_id' => $user_id,
					'weid' => $_W['uniacid'],
					'type_id' => $the_type['id'],
					'time' => $time,
					'create_time' => time(),
				);
				if(pdo_insert('ljr_business_order_orders',$insert)){
					message('操作成功，请就餐！',$this->createMobileUrl('appointment'),'success');
				}else{
					message('操作失败，请重新操作！',$this->createMobileUrl('appointment'),'error');

				}
			}
		}



	}



}
