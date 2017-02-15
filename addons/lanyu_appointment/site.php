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
			$times = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_times')." WHERE weid=".$_W['uniacid']." ORDER BY listorder");

			include $this->template('web/times');
		}

		if($op == 'post'){
			$id = intval($_GPC['id']);
			if ($id > 0) {
				$theone = pdo_fetch('SELECT * FROM ' . tablename('lanyu_appointment_times') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));

			} else {
				$theone = array( 'listorder' => 10);
			}
			if(checksubmit('submit')){
				$listorder = $_GPC['listorder'];
				$time = $_GPC['time'];
				$effective_times = $_GPC['effective_times'];
				$insert = array(
					'listorder' => $listorder,
					'time' => $time,
					'effective_times' => $effective_times,
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
			//查找时间
			$times = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_times')." WHERE weid =".$_W['uniacid']);
			$the_day = strtotime($day);
			$out_times = pdo_fetchall("SELECT * FROM ".tablename('lanyu_appointment_out_time')." WHERE weid =".$_W['uniacid']." AND app_day =".$the_day);
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
			$day = strtotime($day);
			switch($type){
				case 1 : //增加
					$insert = array(
						'weid' => $_W['uniacid'],
						'create_time' => TIMESTAMP,
						'app_day' => $day,
						'time_id' => $time_id
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
					if(pdo_delete('lanyu_appointment_out_time',array('weid' => $_W['uniacid'], 'app_day' => $day, 'time_id' => $time_id)) != false){
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
				message('更新数据成功！', $this->createWebUrl('times', array('op' => 'type')), 'success');
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


	//手机端
	public function doMobileWelcome(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
		$open_id = $_W['openid'];

		if($op == 'index'){
			$welcome = pdo_fetch("SELECT * FROM ".tablename('lanyu_appointment_welcome')." WHERE weid =".$_W['uniacid']);


			include $this->template('welcome');
		}

	}

}
