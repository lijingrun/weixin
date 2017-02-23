<?php

/**
 * 砸蛋抽奖模块
 *
 * [WeiZan System] Copyright (c) 2013 012wz.com
 */
defined('IN_IA') or exit('Access Denied');

class Lanyu_signModuleSite extends WeModuleSite {
	//设置银行
	public function doWebBank() {
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
		if($op == 'index'){
			$banks = pdo_fetchall("SELECT * FROM ".tablename('lanyu_bank')." WHERE weid =".$_W['uniacid']);
			include $this->template('web/banks');
		}

		if($op == 'add'){
			$id = $_GPC['id'];
			if(!empty($id)) {
				$bank = pdo_fetch("SELECT * FROM " . tablename('lanyu_bank') . " WHERE weid =" . $_W['uniacid'] . " AND id =" . $id);
			}
			if(checksubmit('submit')){
				$bank_name = $_GPC['bank_name'];
				$bank_code = $_GPC['bank_code'];
				$id = $_GPC['bank_id'];
				if(empty($bank_name) || empty($bank_code)){
					message('资料不能为空！',$this->createWebUrl('bank',array('op' => 'add')),'error');
				}
				$insert = array(
					'bank_name' => $bank_name,
					'bank_code' => $bank_code,
					'weid' => $_W['uniacid'],
				);
				if(empty($id)) {
					if (pdo_insert('lanyu_bank', $insert) == 1) {
						message('添加成功！', $this->createWebUrl('bank'), 'success');
					} else {
						message('服务器繁忙，请稍后重试！', $this->createWebUrl('bank', array('op' => 'add')), 'error');
					}
				}else{
					pdo_update('lanyu_bank',$insert,array('id' => $id,'weid' => $_W['uniacid']));
					message('操作成功！', $this->createWebUrl('bank'), 'success');
				}
			}
			include $this->template('web/add_bank');
		}

		//删除
		if($op == 'del'){
			$id = $_GPC['id'];
			if(pdo_delete('lanyu_bank',array('id' => $id))){
				message('操作成功！',$this->createWebUrl('bank'),'success');
			}
		}
	}

	//财务录入
	public function doWebSign() {
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if($op == 'index'){
			$banks = pdo_fetchall("SELECT * FROM ".tablename('lanyu_bank')." WHERE weid =".$_W['uniacid']);
			$status = $_GPC['status'];
			$day = $_GPC['day'];
			$bank_id = $_GPC['bank_id'];
			$begin_time = $_GPC['begin_time'];
			$end_time = $_GPC['end_time'];
			$sql = " WHERE s.weid =".$_W['uniacid'];
			if(!empty($status)){
				$sql .= " AND s.status =".$status;
			}
			if(!empty($day)){
				$time = strtotime($day);
				$sql .= " AND s.create_time =".$time;
			}
			if(!empty($bank_id)){
				$sql .= " AND s.bank_id =".$bank_id;
			}
			if(!empty($begin_time)){
				$begin = strtotime($begin_time);
				$sql .= " AND s.examine_time >=".$begin;
			}
			if(!empty($end_time)){
				$end = strtotime($end_time);
				$sql .= " AND s.examine_time <=".$end;
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('lanyu_sign')." AS s".$sql);
			$pager = pagination($total,$pindex,$psize);
			$page_sql = " ORDER BY s.create_time desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
			$sql .= " AND s.bank_id = b.id";
			$sql .= $page_sql;
			$signs = pdo_fetchall("SELECT s.*,b.bank_name,b.bank_code FROM ".tablename('lanyu_sign')." AS s,".tablename('lanyu_bank')." AS b".$sql);
			foreach($signs as $key=>$sign){
				if($sign['sign_id'] > 0){
					$user = pdo_fetch("SELECT username FROM ".tablename('users')." WHERE uid =".$sign['sign_id']);
					$signs[$key]['username'] = $user['username'];
				}
				$sign_users = pdo_fetchall("SELECT * FROM ".tablename('lanyu_sign_user')." WHERE sign_id =".$sign['id']);
				$count = count($sign_users);
				$first_u = current($sign_users);
				unset($sign_users[0]);
				$signs[$key]['first_u'] = $first_u;
				$signs[$key]['other'] = $sign_users;
				$signs[$key]['count'] = $count;
			}
			include $this->template('web/sign_list');
		}

		//详细
		if($op == 'detail'){
			$id = $_GPC['id'];
			$sign = pdo_fetch("SELECT * FROM ".tablename('lanyu_sign')." WHERE id =".$id." AND weid =".$_W['uniacid']);
			$bank = pdo_fetch("SELECT * FROM ".tablename('lanyu_bank')." WHERE id =".$sign['bank_id']." AND weid =".$_W['uniacid']);
			$s_user = pdo_fetchall("SELECT * FROM ".tablename('lanyu_sign_user')." WHERE sign_id =".$id);
			if(empty($sign['examine_time'])){
				$sign['examine_time'] = date('Y-m-d',time());
			}
			include $this->template('web/sign_detail');
		}

		//ajax审核
		if($op == 'examine_ajax'){
			$id = $_GPC['id'];
			$examine_time = empty($_GPC['examine']) ? time() : strtotime($_GPC['examine']);
			$sign = pdo_fetch("SELECT * FROM ".tablename('lanyu_sign')." WHERE id = ".$id);
			$sign_user = pdo_fetchall("SELECT * FROM ".tablename('lanyu_sign_user')." WHERE sign_id =".$id." weid =".$_W['uniacid']);
			if(empty($sign_user)){
				message('未填写客户名称，不能审核',$this->createWebUrl('sign'),'error');
			}
			if(pdo_update('lanyu_sign',array('status' => 2, 'examine_time' => $examine_time),array('id' => $id)) == 1){
				echo 111;
			}else{
				echo 222;
			}
		}

		//录入
		if($op == 'add'){
			$banks = pdo_fetchall("SELECT * FROM ".tablename('lanyu_bank')." WHERE weid =".$_W['uniacid']);
			$sign['create_time'] = time();
			if(checksubmit('submit')){
				$bank_user = $_GPC['bank_user'];
				$amount = $_GPC['amount'];
				$day = $_GPC['day'];
				$bank_id = $_GPC['bank_id'];
				$insert = array(
					'bank_id' => $bank_id,
					'weid' => $_W['uniacid'],
					'amount' => $amount,
					'input_id' => $_W['user']['uid'],
					'bank_user' => $bank_user,
					'create_time' => strtotime($day)
				);
				if(pdo_insert('lanyu_sign',$insert)){
					message('新增成功！',$this->createWebUrl('sign'),'success');
				}else{
					message('服务器繁忙，请稍后重试！',$this->createWebUrl('sign'),'error');
				}
			}


			include $this->template('web/sign_add');
		}

		//修改
		if($op == 'edit'){
			$id = $_GPC['id'];
			$banks = pdo_fetchall("SELECT * FROM ".tablename('lanyu_bank')." WHERE weid =".$_W['uniacid']);
			$sign = pdo_fetch("SELECT s.*,b.bank_name,b.bank_code FROM ".tablename('lanyu_sign')." AS s,".tablename('lanyu_bank')." AS b WHERE s.id =".$id." AND s.weid =".$_W['uniacid']);
			if(checksubmit('submit')) {
				$id = $_GPC['id'];
				$bank_user = $_GPC['bank_user'];
				$amount = $_GPC['amount'];
				$day = $_GPC['day'];
				$bank_id = $_GPC['bank_id'];
				$insert = array(
						'bank_id' => $bank_id,
						'weid' => $_W['uniacid'],
						'amount' => $amount,
						'input_id' => $_W['user']['uid'],
						'bank_user' => $bank_user,
						'create_time' => strtotime($day)
				);
				if (pdo_update('lanyu_sign', $insert, array('id' => $id))) {
					message('操作成功！', $this->createWebUrl('sign'), 'success');
				} else {
					message('服务器繁忙，请稍后重试！', $this->createWebUrl('sign'), 'error');
				}
			}
			include $this->template('web/sign_add');
		}

		//删除
		if($op == 'del'){
			$id = $_GPC['id'];
			if(pdo_delete('lanyu_sign',array('id' => $id))){
				message('操作成功！',$this->createWebUrl('sign'),'success');
			}else{
				message('操作失败！',$this->createWebUrl('sign'),'error');
			}
		}

		//返审
		if($op == 'return'){
			$id = $_GPC['id'];
			if(pdo_update('lanyu_sign',array('status' => 1,'sign_id' => 0),array('id' => $id))){
				pdo_delete('lanyu_sign_user',array('sign_id' => $id));
				message('操作成功！',$this->createWebUrl('sign'),'success');
			}else{
				message('操作失败！',$this->createWebUrl('sign'),'error');
			}
		}

		//审核
		if($op == 'examine'){
			$id = $_GPC['id'];
			$examine_time = empty($_GPC['examine']) ? time() : strtotime($_GPC['examine']);
			$sign = pdo_fetch("SELECT * FROM ".tablename('lanyu_sign')." WHERE id = ".$id);
			$sign_user = pdo_fetchall("SELECT * FROM ".tablename('lanyu_sign_user')." WHERE sign_id =".$id." AND weid =".$_W['uniacid']);
			if(empty($sign_user)){
				message('未填写客户名称，不能审核',$this->createWebUrl('sign'),'error');
			}
			if(pdo_update('lanyu_sign',array('status' => 2, 'examine_time' => $examine_time),array('id' => $id)) == 1){
				message('审核成功！',$this->createWebUrl('sign'),'success');
			}else{
				message('操作失败，请重新操作！',$this->createWebUrl('sign'),'error');
			}
		}

		//全部删除
		if($op == 'del_all'){
			$ids = $_GPC['ids'];
			if(!empty($ids)){
				foreach($ids as $id){
					pdo_delete('lanyu_sign_user',array('sign_id' => $id));
					pdo_delete('lanyu_sign',array('id' => $id));
				}
				echo 111;
				exit;
			}
		}

		//全部审核
		if($op == 'examine_all'){
			$ids = $_GPC['ids'];
			$examine = strtotime(date('Y-m-d',time()));
			if(!empty($ids)){
				foreach($ids as $id){
					$sign_user = pdo_fetchall("SELECT * FROM ".tablename('lanyu_sign_user')." WHERE sign_id =".$id." AND weid =".$_W['uniacid']);
					if(!empty($sign_user)) {
						pdo_update('lanyu_sign', array('status' => 2, 'examine_time' => $examine), array('id' => $id));
					}
				}
				echo 111;
				exit;
			}
		}

		//全部开票
		if($op == 'v_all'){
			$ids = $_GPC['ids'];
			if(!empty($ids)){
				foreach($ids as $id){
					pdo_update('lanyu_sign',array('invoice' => 1),array('id' => $id));
				}
				echo 111;
				exit;
			}
		}

		//开票
		if($op == 'invoice'){
			$id = $_GPC['id'];
			$sign = pdo_fetch("SELECT * FROM ".tablename('lanyu_sign')." WHERE id = ".$id);
			if($sign['status'] == 1){
				message('还未审核，不能开票',$this->createWebUrl('sign'),'error');
			}
			if(pdo_update('lanyu_sign',array('invoice' => 1),array('id' => $id)) == 1){
				message('开票成功！',$this->createWebUrl('sign'),'success');
			}else{
				message('操作失败，请重新操作！',$this->createWebUrl('sign'),'error');
			}
		}

		//添加备注
		if($op == 'remark'){
			$id = $_GPC['id'];
			$remark = $_GPC['remark'];
			if(pdo_update('lanyu_sign',array('remark' => $remark),array('id' => $id))){
				echo 111;
				exit;
			}
		}

		//导入
		if($op == 'import'){
			$data = $_GPC['data'];
			if (checksubmit('submit')) {
				$force = $_GPC['force'];
				$file = $_FILES['file'];
				if( $file['name'] && $file['error'] == 0){
					$type = @end( explode('.', $file['name']));
					$type = strtolower($type);
					if( !in_array($type, array('xls','xlsx')) ){
						message('文件类型错误！',  '', 'error');
					}
					//开始导入
					set_time_limit(0);
					include_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
					/** PHPExcel_IOFactory */
					include_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/IOFactory.php';
					if( $type == 'xls' ){
						$inputFileType = 'Excel5';    //这个是读 xls的
					}else{
						$inputFileType = 'Excel2007';//这个是计xlsx的
					}
					$objReader = PHPExcel_IOFactory::createReader($inputFileType);
					$objPHPExcel = $objReader->load($file['tmp_name']);

					$objWorksheet = $objPHPExcel->getActiveSheet();//取得总行数
					$highestRow = $objWorksheet->getHighestRow();//取得总列数
					$names = array();
					for ($row = 2;$row <= $highestRow;$row++){
						$bank_name = trim($objWorksheet->getCellByColumnAndRow(0, $row)->getValue());
						$bank_code = trim($objWorksheet->getCellByColumnAndRow(1, $row)->getValue());
						$user_name = trim($objWorksheet->getCellByColumnAndRow(3, $row)->getValue());
						$amount = floatval($objWorksheet->getCellByColumnAndRow(4, $row)->getValue());
						$day = trim($objWorksheet->getCellByColumnAndRow(2, $row)->getValue());
						if(empty($bank_name) || empty($bank_code) || empty($user_name) || empty($amount) || empty($day)){
							$names[] = array(
								'name' => $bank_name,
								'bank_code' => $bank_code,
								'user_name' => $user_name,
								'amount' => $amount,
								'day' => $day,
								'status' => 0,
								'data' => '资料不齐全'
							);
							continue;
						}
						$bank = pdo_fetch("SELECT * FROM ".tablename('lanyu_bank')." WHERE bank_name ='".$bank_name."' AND bank_code ='".$bank_code."'");
						if(empty($bank)){
							$names[] = array(
									'name' => $bank_name,
									'bank_code' => $bank_code,
									'user_name' => $user_name,
									'amount' => $amount,
									'day' => $day,
									'status' => 0,
									'data' => '银行资料不正确'
							);
							continue;
						}
						$insert = array(
							'weid' => $_W['uniacid'],
							'bank_id' => $bank['id'],
							'amount' => $amount,
							'input_id' => $_W['user']['uid'],
							'status' => 1,
							'bank_user' => $user_name,
							'create_time' => strtotime($day),
						);
						if(pdo_insert('lanyu_sign',$insert)){
							$names[] = array(
									'name' => $bank_name,
									'status' => 1,
							);
						}
					}
					$str = "收款银行,银行代码,汇款日期,汇款账号,汇款金额,错误原因\n";
					$str = iconv('utf-8','gb2312',$str);
					foreach( $names as $v ) {
						$bank_name = iconv('utf-8','gb2312',$v['name']);
						$user_name = iconv('utf-8','gb2312',$v['user_name']);
						$bank_code = iconv('utf-8','gb2312',$v['bank_code']);
						$data = iconv('utf-8','gb2312',$v['data']);
						if($v['status'] != 1) {
							$str .= $bank_name . ',' . $bank_code . ',' .$v['day'].','.$user_name.','.$v['amount'].','.$data. "\n";
						}
					}
					$filename = '导入问题列表.csv';
					$this->export_csv($filename,$str);
					exit;
//					message('数据导入完毕',  $this->createWebUrl('sign',array('op' => 'import','data' => $names)),'success');
				}
				message('文件上传失败！',  '', 'error');
			}
				include $this->template('web/import');
		}

		//导出
		if($op == 'export'){
			$banks = pdo_fetchall("SELECT * FROM ".tablename('lanyu_bank')." WHERE weid =".$_W['uniacid']);
			$status = $_GPC['status'];
			$day = $_GPC['day'];
			$bank_id = $_GPC['bank_id'];
			$sql = " WHERE s.weid =".$_W['uniacid'];
			if(!empty($status)){
				$sql .= " AND s.status =".$status;
			}
			if(!empty($day)){
				$time = strtotime($day);
				$sql .= " AND s.create_time =".$time;
			}
			if(!empty($bank_id)){
				$sql .= " AND s.bank_id =".$bank_id;
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('lanyu_sign')." AS s".$sql);
			$pager = pagination($total,$pindex,$psize);
//			$page_sql = " ORDER BY s.create_time desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
			$sql .= " AND s.bank_id = b.id AND u.sign_id = s.id";
//			$sql .= $page_sql;
			$signs = pdo_fetchall("SELECT s.*,b.bank_name,b.bank_code,u.* FROM ".tablename('lanyu_sign')." AS s,".tablename('lanyu_bank')." AS b,".tablename('lanyu_sign_user')." AS u".$sql);
			foreach($signs as $key=>$sign){
				if($sign['sign_id'] > 0){
					$user = pdo_fetch("SELECT username FROM ".tablename('users')." WHERE uid =".$sign['sign_id']);
					$signs[$key]['username'] = $user['username'];
				}
			}
			$str = "单据日期,财务日期,收款金额, , ,收款客户,现金类科目,收款银行,收款单号,币别代码,汇率,收款摘要\n";
			$str = iconv('utf-8','gb2312',$str);
			foreach( $signs as $sign ) {
				$data_day = date("Y-m-d",$sign['create_time']);
				$examine_day = date('Y-m-d',$sign['examine_time']);
				$member_name = iconv('utf-8','gb2312',$sign['member_name']);
				$bank_name = iconv('utf-8','gb2312',$sign['bank_name']);
				$str .= $data_day.','.$examine_day.' ,'.$sign['member_amount'].', , ,'.$member_name.','.$sign['bank_code'].','.$bank_name.', ,RMB,1.00000'."\n";
			}
			$filename = '线下签款明细.'.date('Ymd').'.csv';
			$this->export_csv($filename,$str);
		}
	}

	function export_csv($filename,$data)
	{
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		echo $data;
	}

	public function doWebBusiness(){
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		if($op == 'index'){
			$banks = pdo_fetchall("SELECT * FROM ".tablename('lanyu_bank')." WHERE weid =".$_W['uniacid']);
			$status = empty($_GPC['status']) ? 1 : $_GPC['status'];
			$day = $_GPC['day'];
			$bank_id = $_GPC['bank_id'];
			$sql = " WHERE s.weid =".$_W['uniacid'];
			if(!empty($status)){
				$sql .= " AND s.status =".$status;
			}
			if(!empty($day)){
				$time = strtotime($day);
				$sql .= " AND s.create_time =".$time;
			}
			if(!empty($bank_id)){
				$sql .= " AND s.bank_id =".$bank_id;
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('lanyu_sign')." AS s".$sql);
			$pager = pagination($total,$pindex,$psize);
			$page_sql = " ORDER BY s.create_time desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
			$sql .= " AND s.bank_id = b.id";
			$sql .= $page_sql;
			$signs = pdo_fetchall("SELECT s.*,b.bank_name,b.bank_code FROM ".tablename('lanyu_sign')." AS s,".tablename('lanyu_bank')." AS b".$sql);
			foreach($signs as $key=>$sign){
				if($sign['sign_id'] > 0){
					$user = pdo_fetch("SELECT username FROM ".tablename('users')." WHERE uid =".$sign['sign_id']);
					$signs[$key]['username'] = $user['username'];
				}
				$sign_users = pdo_fetchall("SELECT * FROM ".tablename('lanyu_sign_user')." WHERE sign_id =".$sign['id']);
				$count = count($sign_users);
				$first_u = current($sign_users);
				unset($sign_users[0]);
				$signs[$key]['first_u'] = $first_u;
				$signs[$key]['other'] = $sign_users;
				$signs[$key]['count'] = $count;
			}
			include $this->template('web/b_sign_list');
		}

		//签款
		if($op == 'sign'){
			$id = $_GPC['id'];
			$sign = pdo_fetch("SELECT s.*,b.bank_name,b.bank_code FROM ".tablename('lanyu_sign')." AS s,".tablename('lanyu_bank')." AS b WHERE s.id =".$id." AND s.bank_id = b.id");
			if(checksubmit('submit')){
				$member_name_arr = $_GPC['member_name'];
				$member_code_arr = $_GPC['member_code'];
				$member_amount_arr = $_GPC['member_amount'];
				if($sign['sign_id'] == 0 || $sign['sign_id'] == $_W['user']['uid']) {
					//无论之前有无，都先删除这个sign下面的所有签款记录，重新记录过
					pdo_delete('lanyu_sign_user',array('sign_id' => $sign['id']));
					foreach($member_amount_arr as $key=>$m){
						$insert = array(
							'sign_id' => $sign['id'],
							'weid' => $_W['uniacid'],
							'member_amount' => $m,
							'member_name' => $member_name_arr[$key],
							'member_code' => $member_code_arr[$key],
						);
						pdo_insert('lanyu_sign_user',$insert);
					}
					pdo_update('lanyu_sign',array('sign_id' => $_W['user']['uid']),array('id' => $sign['id']));
					message('签款/修改成功！',$this->createWebUrl('business'),'success');
				}else{
					message('签款失败，已经被其他人签了！',$this->createWebUrl('business'),'error');
				}
			}

			include $this->template('web/b_sign');
		}
	}

}