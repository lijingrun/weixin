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
			$coe = $_GPC['coe'];
			$c_type = $_GPC['c_type'];
			$insert = array(
					'weid' => $_W['uniacid'],
					'content' => $content,
					'title' => $title,
					'coe' => $coe,
					'c_type' => $c_type,
			);
			if (!empty($id)) {
				pdo_update('lanyu_appointment_welcome', $insert, array('id' => $id));
			} else {
				pdo_insert('lanyu_appointment_welcome', $insert);
			}
			message('操作成功！', $this->createWebUrl('welcome', array('op' => 'index')), 'success');
		}


	}

	//订单管理
	public function doWebOrder(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if($op == 'index'){
			$stores = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_store')." WHERE weid =".$_W['uniacid']);
			$sql = " FROM ".tablename('lanyu_appointment_data')." AS d,".tablename('lanyu_appointment_times')." AS t,".tablename('lanyu_appointment_store')." AS s WHERE d.weid =".$_W['uniacid']." AND d.store_id = s.id AND d.times_id = t.id";
			$store_id = $_GPC['store_id'];
			$day = $_GPC['day'];
			$phone = $_GPC['phone'];
			$status = $_GPC['status'];
			if( !empty($status) && $status >= 2){
				$sql .= " AND d.status =".($status-2);
			}
			if($store_id > 0){
				$sql .= " AND d.store_id =".$store_id;
			}
			if(!empty($day)){
				$sql .= " AND d.appointment_day =".strtotime($day);
			}
			if(!empty($phone)){
				$sql .= " AND d.phone like '%".$phone."%'";
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*)".$sql);
			$pager = pagination($total,$pindex,$psize);
			$sql .= " ORDER BY d.appointment_day desc,times_id LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
			$list = pdo_fetchall("SELECT d.*,t.time,s.name as store_name".$sql);
			foreach($list as $key=>$l){
				$types = pdo_fetchall("SELECT t.name FROM ".tablename('lanyu_appointment_to_type')." AS toa,".tablename('lanyu_appointment_type')." AS t WHERE toa.app_id = ".$l['id']." AND toa.type_id = t.id");
				$list[$key]['types'] = $types;
			}

			include $this->template('web/order_list');
		}

		if($op == 'detail'){
			$id = $_GPC['id'];
			$order = pdo_fetch("SELECT d.*,t.time,s.name as store_name FROM ".tablename('lanyu_appointment_data')." AS d,".tablename('lanyu_appointment_times')." AS t,".tablename('lanyu_appointment_store')." AS s WHERE d.id = ".$id." AND d.weid =".$_W['uniacid']." AND d.store_id = s.id AND d.times_id = t.id");
			$types = pdo_fetchall("SELECT tt.price AS p_price,t.* FROM ".tablename('lanyu_appointment_to_type')." AS tt,".tablename('lanyu_appointment_type')." AS t WHERE tt.app_id =".$id." AND tt.weid =".$_W['uniacid']." AND t.id=tt.type_id");
			$total_price = 0;
			foreach($types as $type){
				$total_price += $type['p_price'];
			}
			//先查有无评价了，有就不可以再评价
			$eva = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_eva')." WHERE order_id =".$id);
			$eva_types = pdo_fetchall("SELECT * FROM " . tablename('lanyu_appointment_eva_type') . " WHERE weid=" . $_W['uniacid'] . " ORDER BY list_order");
			if(!empty($eva)) {
				$eva_array = (ARRAY)json_decode($eva['eva']);
				$remark = $eva_array['remark'];
				$has_eva = true;
				foreach ($eva_types as $key2 => $type) {
					foreach ($eva_array as $key => $e_a) {
						if ($type['id'] == $key) {
							$eva_types[$key2]['value'] = $e_a;
						}
					}
				}
			}
			include $this->template('web/order_detail');
		}

		//取消订单
		if($op == 'cancel'){
			$id = $_GPC['id'];
			if(pdo_update('lanyu_appointment_data',array('status' => 0),array('id' => $id))){
				echo 111;
			}else{
				echo 222;
			}
			exit;
		}

		//完成订单
		if($op == 'finish'){
			$id = $_GPC['id'];
			if(pdo_update('lanyu_appointment_data',array('status' => 3,'finish_time' => time()),array('id' => $id))){
				echo 111;
			}else{
				echo 222;
			}
			exit;
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

		if($op == 'del_package'){
			$id = $_GPC['id'];
			if(pdo_delete('lanyu_appointment_package',array('id' => $id, 'weid' => $_W['uniacid']))){
				echo 111;
			}else{
				echo 123;
			}
			exit;
		}

		if($op == 'post'){
			$id = intval($_GPC['id']);
			if ($id > 0) {
				$theone = pdo_fetch('SELECT * FROM ' . tablename('lanyu_appointment_store') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));

				$one_region = pdo_fetch("SELECT * FROM ".tablename('lanyu_region')." WHERE id=".$theone['region_id']);
				$region_name = $this->find_region($one_region['parent_id'],$one_region['name'],$one_region['weid']);
			} else {
				$theone = array('status' => 1, 'listorder' => 0);
			}
			//店铺类型
			$region = pdo_fetchall("SELECT * FROM ".tablename('lanyu_region')." WHERE parent_id = 1");
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
			$under = pdo_fetchall("SELECT * FROM ".tablename('lanyu_region')." WHERE parent_id =".$parent_id);
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

		//删除时间段
		if($op == 'del'){
			$id = $_GET['id'];
			if(pdo_delete('lanyu_appointment_times',array('id' => $id))){
				message("操作成功！",$this->createWebUrl('times', array('op' => 'index')),'success');
			}

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

		//设置套餐
		if($op == 'package'){
			$id = $_GPC['type_id'];
			$type = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_type')." WHERE id =".$id." AND weid = ".$_W['uniacid']);

			$under = pdo_fetchall("SELECT t.*,p.price as p_price,p.down_type,p.id as p_id FROM ".tablename('lanyu_appointment_type')." AS t,".tablename('lanyu_appointment_package')." AS p WHERE p.type_id =".$id." AND p.weid=".$_W['uniacid']." AND p.down_type = t.id");
			if(!empty($under)){
				$has_choose = array();
				foreach($under as $u){
					$has_choose[] = $u['down_type'];
				}
				$has_choose = implode(',',$has_choose);
				$other_types = pdo_fetchall("SELECT * FROM " . tablename('lanyu_appointment_type') . " WHERE weid =" . $_W['uniacid']." AND id not in (".$has_choose.")");
			}else {
				$other_types = pdo_fetchall("SELECT * FROM " . tablename('lanyu_appointment_type') . " WHERE weid =" . $_W['uniacid']);
			}
			include $this->template('web/package');
		}

		//添加套餐
		if($op == 'add_package'){
			$type_id = $_GPC['type_id'];
			$under_type = $_GPC['under_type'];
			$under_price = $_GPC['under_price'];
			$insert = array(
				'weid' => $_W['uniacid'],
				'type_id' => $type_id,
				'down_type' => $under_type,
				'price' => $under_price,
			);
			if(pdo_insert('lanyu_appointment_package',$insert)){
				echo 111;
			}else{
				echo 222;
			}
			exit;
		}
	}

	//根据id递归出区域
	function find_region($parent_id,$region_name,$weid){
		if($parent_id>1){
			$region = pdo_fetch("SELECT * FROM ".tablename('lanyu_region')." WHERE id =".$parent_id);
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
				$one_region = pdo_fetch("SELECT * FROM ".tablename('lanyu_region')." WHERE id=".$store['region_id']);
				$region_name = $this->find_region($one_region['parent_id'],$one_region['name'],$one_region['weid']);
				$stores[$key]['region_name'] = $region_name;
			}
			$store_count = count($stores);
			include $this->template('store');
		}

		if($op == 'choose_day'){
			$type_id = $_GPC['type_id'];
			$store_id = $_GPC['store_id'];
			$month = intval(empty($_GPC['month'])?date('m',time()):$_GPC['month']);
			$year = empty($_GPC['year'])?date('Y',time()):$_GPC['year'];
			//只算出未来14天的预约,外加今天
			$today_time = strtotime(date('Y-m-d',time()));
			$days = array();
			for($i = 0; $i <= 15; $i++){
				$time = $today_time+(86400*$i);
				$the_one['year'] = date('Y',$time);
				$the_one['month'] = date('m',$time);
				$the_one['day'] = date('d',$time);
				$the_one['week'] = array('日','一','二','三','四','五','六')[date('w',$time)];
				$days[] = $the_one;
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
			//查所选日期里面已经满了预约的时间
			$had_app = pdo_fetchall("SELECT count(*) as count,times_id FROM ".tablename('lanyu_appointment_data')." WHERE appointment_day =".$time." AND status=2 AND store_id =".$store_id." AND weid =".$_W['uniacid']." GROUP BY times_id");
			//将不能预约的标注出来
			foreach($times as $key=>$tt){
				if(in_array($tt['id'],$out_ids)){
					$times[$key]['status'] = 2;
				}else{
					$times[$key]['status'] = 1;
					if(!empty($had_app)){
						foreach($had_app as $ha){
							if($tt['id'] == $ha['times_id'] && intval($tt['effective_times']) <= intval($ha['count'])){
								$times[$key]['status'] = 2;
							}
						}
					}
				}
			}
			//查是否选择了明天，是的话查时间，是当前时间之前的，直接不能选择
			if(strtotime($choose_day) == strtotime(date("Y-m-d",time()))){
				$tomorrow = time(); //明天的这个时间
				foreach($times as $key=>$tt){
					$time = strtotime($choose_day." ".$tt['time']);  //时间的时间戳
					if($tomorrow >= $time){
						$times[$key]['status'] = 2;
					}
				}
			}
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
			$under_type = pdo_fetchall("SELECT t.*,p.price as p_price FROM ".tablename('lanyu_appointment_package')." AS p,".tablename('lanyu_appointment_type')." AS t WHERE p.type_id =".$type_id." AND p.weid =".$_W['uniacid']." AND p.down_type = t.id");
			if(empty($under_type)){
				message('',$this->createMobileUrl('orders',array('op' => 'to_order','day' => $day, 'type_id' => $type_id, 'store_id' => $store_id, 'time_id' => $time_id)),'');
			}else{
				include $this->template('to_package');
			}
		}

		//确认订单
		if($op == 'to_order'){
			$day = $_GPC['day'];
			$type_id = $_GPC['type_id']; //主id
			$o_type_ids = $_GPC['ot'];
			$store_id = $_GPC['store_id'];
			$time_id = $_GPC['time_id'];
			//查店铺
			$store = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_store')." WHERE id =".$store_id." AND weid =".$_W['uniacid']);
			$one_region = pdo_fetch("SELECT * FROM ".tablename('lanyu_region')." WHERE id=".$store['region_id']);
			$region_name = $this->find_region($one_region['parent_id'],$one_region['name'],$one_region['weid']);
			$store['region_name'] = $region_name;
			//查时间
			$time = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_times')." WHERE id =".$time_id." AND weid=".$_W['uniacid']);
			//查选择主类型
			$type = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_type')." WHERE id =".$type_id." AND weid =".$_W['uniacid']);
			$total_price = $type['price'];
			//计算预约订金数
			$welcome = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_welcome')." WHERE weid =".$_W['uniacid']);
			$deposit = 0;
			switch($welcome['c_type']){
				case 1 : $deposit = $welcome['coe'];
					break;
				case 2 : $deposit = $welcome['coe']*$total_price;
					break;
			}
			//套餐类型
			if(!empty($o_type_ids)){
				$o_type_ids = implode(',',$o_type_ids);
				$other_types = pdo_fetchall("SELECT t.*,p.price as p_price FROM ".tablename('lanyu_appointment_type')." AS t,".tablename('lanyu_appointment_package')." AS p WHERE t.id in(".$o_type_ids.") AND t.weid =".$_W['uniacid']." AND p.down_type = t.id");
				foreach($other_types as $ots){
					$total_price += $ots['p_price'];
				}
			}
			//查历史订单
			$order = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_data')." WHERE weid =".$_W['uniacid']." AND openid like '".$_W['openid']."' ORDER BY id desc");
			include $this->template('order_post');
		}

		//生成订单
		if($op == 'order'){
			$type_id = $_GPC['type_id'];
			$time_id = $_GPC['time_id'];
			$store_id = $_GPC['store_id'];
			$day = $_GPC['day'];
			$name = $_GPC['name'];
			$phone = $_GPC['phone'];
			$email = $_GPC['email'];
			$o_type_ids = $_GPC['o_type_ids'];
			$now = TIMESTAMP;
			$insert = array(
				'weid' => $_W['uniacid'],
				'openid' => $_W['openid'],
				'phone' => $phone,
				'name' => $name,
				'email' => $email,
				'status' => 1,
				'create_time' => $now,
				'appointment_day' => strtotime($day),
				'times_id' => $time_id,
				'store_id' => $store_id,
			);
			if(pdo_insert('lanyu_appointment_data',$insert)){
				$uid = pdo_insertid();
				$app_id = $uid;
				$type = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_type')." WHERE id =".$type_id." AND weid =".$_W['uniacid']);
				$order_type = array();
				$type_array = array(
						'weid' => $_W['uniacid'],
						'app_id' => $app_id,
						'type_id' => $type['id'],
						'price' => $type['price'],
				);
				$order_type[] = $type_array;
				if(!empty($o_type_ids)){
					$o_types = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_package')." WHERE down_type in (".$o_type_ids.") AND weid =".$_W['uniacid']);
					foreach($o_types as $ot){
						$ot_arr = array(
							'weid' => $_W['uniacid'],
							'app_id' => $app_id,
							'type_id' => $ot['down_type'],
							'price' => $ot['price'],
						);
						$order_type[] = $ot_arr;
					}
				}
				foreach($order_type as $ort){
					pdo_insert('lanyu_appointment_to_type',$ort);
				}
			}
			$return = array(
				'error' => 0,
				'app_id' => $app_id,
			);
			$return = json_encode($return);
			echo $return;
			exit;
		}
	}

	//我的订单
	public function doMobileMy_orders(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'list';

		if($op == 'list'){
			if(empty($_W['openid'])){
				$_W['openid'] = 1;
			}
			$list = pdo_fetchall("SELECT d.*,t.time FROM ".tablename('lanyu_appointment_data')." as d,".tablename('lanyu_appointment_times')." as t WHERE d.openid ='".$_W['openid']."' AND d.weid =".$_W['uniacid']." AND d.times_id = t.id AND d.store_id = t.store_id ORDER BY create_time desc");
			foreach($list as $key=>$l){
				$total_price = 0;
				$types = pdo_fetchall("SELECT tt.price as p_price,t.* FROM ".tablename('lanyu_appointment_to_type')." AS tt,".tablename('lanyu_appointment_type')." AS t"." WHERE tt.app_id =".$l['id']." AND tt.weid =".$_W['uniacid']." AND tt.type_id = t.id");
				$list[$key]['types'] = $types;
				foreach($types as $type){
					$total_price += $type['p_price'];
					$list[$key]['total_price'] = $total_price;
				}
			}

			include $this->template('my_orders');
		}

		//订单详细
		if($op == 'detail'){
			$id = $_GPC['id'];
			$order = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_data')." WHERE id =".$id." AND weid =".$_W['uniacid']);
			$store = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_store')." WHERE id =".$order['store_id']." AND weid =".$_W['uniacid']);
			$time = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_times')." WHERE id =".$order['times_id']." AND weid =".$_W['uniacid']." AND store_id =".$order['store_id']);
			$types = pdo_fetchall("SELECT tt.price as p_price,t.* FROM ".tablename('lanyu_appointment_to_type')." AS tt,".tablename('lanyu_appointment_type')." AS t"." WHERE tt.app_id =".$order['id']." AND tt.weid =".$_W['uniacid']." AND tt.type_id = t.id");
			$total_price = 0;
			foreach($types as $type){
				$total_price += $type['price'];
			}
			//计算需要支付定金
			$welcome = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_welcome')." WHERE weid =".$_W['uniacid']);
			$deposit = 0;
			switch($welcome['c_type']){
				case 1 : $deposit = $welcome['coe'];
					break;
				case 2 : $deposit = $welcome['coe']*$total_price;
					break;
			}
			include $this->template('order_detail');
		}

		//去修改预约日期
		if($op == 'to_change_day'){
			$order_id = $_GPC['order_id'];
			$order = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_data')." WHERE id = ".$order_id." AND weid =".$_W['uniacid']);
			$time = pdo_fetch('SELECT * FROM '.tablename('lanyu_appointment_times')." WHERE id =".$order['times_id']." AND store_id =".$order['store_id']." AND weid =".$_W['uniacid']);
			$order_time = date("Y-m-d",$order['appointment_day'])." ".$time['time'];
			$order_time = strtotime($order_time);
			//距离预约时间大于48小时才可以修改

			if(($order_time - time()) > 172800){
				$month = intval(empty($_GPC['month'])?date('m',time()):$_GPC['month']);
				$year = empty($_GPC['year'])?date('Y',time()):$_GPC['year'];
				$today_time = strtotime(date('Y-m-d',time()));
				$days = array();
				for($i = 0; $i <= 15; $i++){
					$time = $today_time+(86400*$i);
					$the_one['year'] = date('Y',$time);
					$the_one['month'] = date('m',$time);
					$the_one['day'] = date('d',$time);
					$the_one['week'] = array('日','一','二','三','四','五','六')[date('w',$time)];
					$days[] = $the_one;
				}
				include $this->template('change_day');
			}else{
				message('距离预约时间大于48小时才能修改！','','error');
			}
		}

		//修改预约日期
		if($op == 'change_day'){
			$order_id = $_GPC['order_id'];
			$day = $_GPC['day'];
			$year = $_GPC['year'];
			$month = $_GPC['month'];
			$day_time = $year."-".$month."-".$day;
			$day_time = strtotime($day_time);
			if(pdo_update('lanyu_appointment_data',array('appointment_day' => $day_time),array('id' => $order_id,'weid' => $_W['uniacid'])) || 1 == 1) {
				$order = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_data')." WHERE id =".$order_id." AND weid =".$_W['uniacid']);
				$store_id = $order['store_id']; //店铺id
				$year = empty($_GPC['year']) ? date('Y', time()) : $_GPC['year'];
				$choose_day = $year . "-" . $month . "-" . $day;
				$time = strtotime($choose_day);
				//根据店铺，查找店铺对应的时间
				$times = pdo_fetchall("SELECT * FROM " . tablename('lanyu_appointment_times') . " WHERE weid =" . $_W['uniacid'] . " AND store_id =" . $store_id . " ORDER BY listorder");
				//查看这个日期这个店铺是否有设置不能预约的时间
				$out_time = pdo_fetchall("SELECT * FROM " . tablename('lanyu_appointment_out_time') . " WHERE app_day =" . $time . " AND weid=" . $_W['uniacid'] . " AND store_id =" . $store_id);
				$out_ids = array();
				foreach ($out_time as $t) {
					$out_ids[] = $t['time_id'];
				}
				//查所选日期里面已经满了预约的时间
				$had_app = pdo_fetchall("SELECT count(*) as count,times_id FROM " . tablename('lanyu_appointment_data') . " WHERE appointment_day =" . $time . " AND status=2 AND store_id =" . $store_id . " AND weid =" . $_W['uniacid'] . " GROUP BY times_id");
				//将不能预约的标注出来
				foreach ($times as $key => $tt) {
					if (in_array($tt['id'], $out_ids)) {
						$times[$key]['status'] = 2;
					} else {
						$times[$key]['status'] = 1;
						if (!empty($had_app)) {
							foreach ($had_app as $ha) {
								if ($tt['id'] == $ha['times_id'] && intval($tt['effective_times']) <= intval($ha['count'])) {
									$times[$key]['status'] = 2;
								}
							}
						}
					}
				}
				//查是否选择了明天，是的话查时间，是当前时间之前的，直接不能选择
				if(strtotime($choose_day) == strtotime(date("Y-m-d",time()+86400))){
					$tomorrow = time()+86400; //明天的这个时间
					foreach($times as $key=>$tt){
						$time = strtotime($choose_day." ".$tt['time']);  //时间的时间戳
						if($tomorrow >= $time){
							$times[$key]['status'] = 2;
						}
					}
				}

				include $this->template('change_times');
			}
		}

		//修改预约时间点
		if($op == 'change_times'){
			$order_id = $_GPC['order_id'];
			$time_id = $_GPC['time_id'];
			if(pdo_update("lanyu_appointment_data",array('times_id' => $time_id), array('id' => $order_id,'weid' => $_W['uniacid']))){
				echo 111;
			}else{
				echo 222;
			}
			exit;
		}

		//取消订单
		if($op == 'del'){
			$id = $_GPC['id'];
			if(pdo_update('lanyu_appointment_data',array('status' => 0),array('id' => $id))){
				echo 111;
			}
			exit;
		}

		//评价
		if($op == 'eva'){
			$order_id = $_GET['order_id'];
			//先查有无评价了，有就不可以再评价
			$eva = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_eva')." WHERE order_id =".$order_id);
			$eva_types = pdo_fetchall("SELECT * FROM " . tablename('lanyu_appointment_eva_type') . " WHERE weid=" . $_W['uniacid'] . " ORDER BY list_order");
			if(!empty($eva)) {
				$eva_array = (ARRAY)json_decode($eva['eva']);
				$remark = $eva_array['remark'];
				$has_eva = true;
				foreach ($eva_types as $key2 => $type) {
					foreach ($eva_array as $key => $e_a) {
						if ($type['id'] == $key) {
							$eva_types[$key2]['value'] = $e_a;
						}
					}
				}
			}
			include $this->template('eva');
		}

		if($op == 'input_eva'){
			$order_id = $_GPC['id'];
			$eva = $_GPC['eva'];
			$eva = json_encode($eva);
			$insert = array(
				'order_id' => $order_id,
				'weid' => $_W['uniacid'],
				'eva' => $eva,
				'create_time' => time(),
			);
			if(pdo_insert('lanyu_appointment_eva',$insert)){
				message('',$this->createMobileUrl('my_orders',array('op'=>'eva','order_id' => $order_id)),'');
			}
		}

		//支付
		if($op == 'pay'){
			$id = $_GPC['id'];
			$welcome = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_welcome')." WHERE weid =".$_W['uniacid']);
			$price = 0;
			switch($welcome['c_type']){
				case 1 : $price = $welcome['coe'];
					break;
				case 2 :
					$types = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_to_type')." WHERE app_id =".$id." AND weid =".$_W['uniacid']);
					foreach($types as $type){
						$price += $type['price'];
					}
				break;
				default : $price = 0;
			}
			if($price > 0) {
				$params = array(
						'tid' => $id,
						'ordersn' => '000000'.$id,
						'title' => '预约费用',
						'fee' => $price,
						'user' => $_W['member']['uid'],
				);
				$this->pay($params);
			}else{
				pdo_update('lanyu_appointment_data',array('status' => 2),array('id' => $id));
				message('操作成功！',$this->createMobileUrl('my_order',array('op' => 'detail','id' => $id)),'success');
			}
		}

	}

	//支付回调
	public function payResult($params){
		global $_GPC, $_W;
		$id = $params['tid'];
		//修改订单状态
		if(pdo_update('lanyu_appointment_data',array('status' => 2),array('id' => $id,'openid' => $_W['openid'], 'weid' => $_W['uniacid']))){
			message('支付成功！',$this->createMobileUrl('my_orders'));
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
