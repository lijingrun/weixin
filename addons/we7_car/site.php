<?php

/**
 * 微汽车模块定义
 *
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class We7_carModuleSite extends WeModuleSite {

    public function doWebIndex() {
        global $_GPC, $_W;
        $theone = pdo_fetch('SELECT * FROM ' . tablename('we7car_set') . " WHERE `weid` = :weid ", array(':weid' => $_W['uniacid']));
        if (!empty($theone['thumbArr'])) {
            $theone['thumb_url'] = explode('|', $theone['thumbArr']);
        }

        if (checksubmit('submit')) {
            $title = !empty($_GPC['title']) ? trim($_GPC['title']) : message('请填写公司名称');
            $shoplogo = !empty($_GPC['shop_logo']) ? $_GPC['shop_logo'] : message('请上传首页店标');
            $description = !empty($_GPC['description']) ? trim($_GPC['description']) : message('请填写公司简介');
            $address = !empty($_GPC['address']) ? trim($_GPC['address']) : message('请填写公司地址');
            $opentime = !empty($_GPC['opentime']) ? trim($_GPC['opentime']) : message('请填写营业时间');
            $pre_consult = !empty($_GPC['pre_consult']) ? trim($_GPC['pre_consult']) : message('请填写新车销售、咨询热线');
            $aft_consult = !empty($_GPC['aft_consult']) ? trim($_GPC['aft_consult']) : message('请填写售后预约热线');
            $thumb_url = !empty($_GPC['thumb_url']) ? $_GPC['thumb_url'] : message('请上传频道首页幻灯片');
            $typethumb = !empty($_GPC['typethumb']) ? $_GPC['typethumb'] : message('请上车型背景图');
            $yuyue1thumb = !empty($_GPC['yuyue1thumb']) ? $_GPC['yuyue1thumb'] : message('请上传预约试驾背景图');
            $yuyue2thumb = !empty($_GPC['yuyue2thumb']) ? $_GPC['yuyue2thumb'] : message('请上传预约保养背景图');
            $kefuthumb = !empty($_GPC['kefuthumb']) ? $_GPC['kefuthumb'] : message('请上传客服背景图');
            $messagethumb = !empty($_GPC['messagethumb']) ? $_GPC['messagethumb'] : message('请上传意见反馈背景图');
            $carethumb = !empty($_GPC['carethumb']) ? $_GPC['carethumb'] : message('请上传车主关怀背景图');
            $data = array(
                'weid' => $_W['uniacid'],
                'title' => $title,
                'shop_logo' => $shoplogo,
                'description' => $description,
                'address' => $address,
                'opentime' => $opentime,
                'pre_consult' => $pre_consult,
                'aft_consult' => $aft_consult,
                'typethumb' => $typethumb,
                'yuyue1thumb' => $yuyue1thumb,
                'yuyue2thumb' => $yuyue2thumb,
                'kefuthumb' => $kefuthumb,
                'messagethumb' => $messagethumb,
                'carethumb' => $carethumb
            );
         
            if (!empty($_GPC['thumb_url'])) {
                $data['weicar_logo'] = $_GPC['thumb_url'][0];
                $data['thumbArr'] = implode('|', $_GPC['thumb_url']);
            }

            if (!empty($theone)) {
                $temp = pdo_update('we7car_set', $data, array('weid' => $_W['uniacid'], 'id' => $theone['id']));
            } else {
                $temp = pdo_insert('we7car_set', $data);
            }
            if ($temp === false) {
                message('更新首页设置失败！', '', 'error');
            } else {
                message('更新首页设置成功！', '', 'success');
            }
        }
        include $this->template('web/index');
    }

    public function doMobileIndex() {
        global $_GPC, $_W;
        $op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
        $theone = pdo_fetch('SELECT * FROM ' . tablename('we7car_set') . " WHERE `weid` = :weid ", array(':weid' => $_W['uniacid']));
        // 设置分享信息
        $shareDesc = $theone['description'];
        $shareThumb = tomedia($theone['weicar_logo']);
        if ($op == 'index') {
            $news_category = pdo_fetchall('SELECT * FROM ' . tablename('we7car_news_category') . " WHERE `weid` = :weid AND status = 1", array(':weid' => $_W['uniacid']));
            if (!empty($theone['thumbArr'])) {
                $theone['thumb_url'] = explode('|', $theone['thumbArr']);
            }
            $shareTitle = $title = $theone['title'];
            include $this->template('index');
        }
        if ($op == 'about') {
            $shareTitle = $title = '关于我们';
            include $this->template('about');
        }
    }

    //维修系统
    public function doWebSystem(){
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';
        //列表
        if ($op == 'list'){
            $list = pdo_fetchall('SELECT * FROM ' . tablename('we7car_system') . " WHERE weid = :weid ORDER BY listorder", array(':weid' => $_W['uniacid']));
            if(checksubmit('submit')){
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_system', array('listorder' => intval($val)), array('system_id' => intval($key)));
                }
                message('更新品牌排序成功！', $this->createWebUrl('system', array('op' => 'list')), 'success');
            }
            include $this->template('web/system');
        }
        //添加/修改
        if($op == 'post'){
            $id = intval($_GPC['id']);
            if ($id > 0) {
                $theone = pdo_fetch('SELECT * FROM ' . tablename('we7car_system') . " WHERE  weid = :weid  AND system_id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));
            } else {
                $theone = array('status' => 1, 'listorder' => 0);
            }

            if(checksubmit('submit')){
                $name = trim($_GPC['name']) ? trim($_GPC['name']) : message('请填写类型名称！');
                $description = trim($_GPC['description']) ? trim($_GPC['description']) : message('请填写类型简介！');
                $officialweb = trim($_GPC['officialweb']);
                $listorder = intval($_GPC['listorder']);
                $status = intval($_GPC['status']);
                $insert = array(
                    'name' => $name,
                    'description' => $description,
                    'officialweb' => $officialweb,
                    'listorder' => $listorder,
                    'status' => $status,
                    'weid' => $_W['uniacid'],
                    'createtime' => TIMESTAMP
                );
                if (empty($id)) {
                    pdo_insert('we7car_system', $insert);
                    !pdo_insertid() ? message('保存品牌数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_system', $insert, array('id' => $id)) === false) {
                        message('更新品牌数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新品牌数据成功！', $this->createWebUrl('system', array('op' => 'list')), 'success');
            }
            include $this->template('web/system_post');
        }

        if ($op == 'del'){
            $id = intval($_GPC['id']);
            $temp = pdo_delete("we7car_system", array("weid" => $_W['uniacid'], 'system_id' => $id));
            if ($temp == false) {
                message('抱歉，删除数据失败！', '', 'error');
            } else {
                message('删除数据成功！', $this->createWebUrl('system', array('op' => 'list')), 'success');
            }
        }

    }

    //服务类型
    public function doWebServices(){
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';

        if($op == 'list'){
            $list = pdo_fetchall("SELECT s.*, t.name as system_name FROM ". tablename('we7car_car_services') . " as s,". tablename('we7car_system')." as t WHERE s.weid = ".$_W['uniacid']." AND s.system_id = t.system_id");

            if (checksubmit('submit')) {
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_car_services', array('listorder' => intval($val)), array('id' => intval($key)));
                }
                message('更新品牌排序成功！', $this->createWebUrl('services', array('op' => 'list')), 'success');
            }

            include $this->template('web/services');
        }

        if($op == 'post'){
            $id = intval($_GPC['id']);
            $system = pdo_fetchall('SELECT * FROM ' . tablename('we7car_system') . " WHERE weid = :weid", array('weid' => $_W['uniacid']));
            if(!empty($id)){
                $theone = pdo_fetch('SELECT * FROM ' . tablename('we7car_car_services') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));
            } else {
                $theone = array('status' => 1, 'listorder' => 0);
            }

            if(checksubmit('submit')){
                $name = trim($_GPC['name']) ? trim($_GPC['name']) : message('请填写服务名称！');
                $description = trim($_GPC['description']) ;
                $listorder = intval($_GPC['listorder']);
                $status = intval($_GPC['status']);
                $system_id = $_GPC['system_id'];
                $spacing_km = $_GPC['spacing_km'];
                $spacing_day = $_GPC['spacing_day'];
                $insert = array(
                    'name' => $name,
                    'description' => $description,
                    'listorder' => $listorder,
                    'status' => $status,
                    'system_id' => $system_id,
                    'spacing_day' => $spacing_day,
                    'spacing_km' => $spacing_km,
                    'user_time' => $_GPC['user_time'],
                    'weid' => $_W['uniacid'],
                    'createtime' => TIMESTAMP,
                );
                if(empty($id)){
                    pdo_insert('we7car_car_services',$insert);
                    !pdo_insertid() ? message('保存服务数据失败, 请稍后重试.', 'error') : '';
                }else{
                    if (pdo_update('we7car_car_services', $insert, array('id' => $id)) === false) {
                        message('更新服务数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新服务数据成功！', $this->createWebUrl('services', array('op' => 'list')), 'success');
            }

            include $this->template('web/services_post');
        }

        if($op == 'del'){
            $id = intval($_GPC['id']);
            $temp = pdo_delete("we7car_car_services", array("weid" => $_W['uniacid'], 'id' => $id));
            if ($temp == false) {
                message('抱歉，删除数据失败！', '', 'error');
            } else {
                message('删除数据成功！', $this->createWebUrl('services', array('op' => 'list')), 'success');
            }
        }
    }

    //商品
    public function doWebGoods(){
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        //产品列表
        if($op == 'list'){
            $key_word = $_GPC['key_word'];
            $type_id = empty($_GPC['type_id'])? 0 : $_GPC['type_id'];
            $sql = "SELECT g.*, t.name as type_name FROM ". tablename('we7car_goods')." AS g, ".tablename('we7car_goods_type')." AS t WHERE g.weid = ".$_W['uniacid']." AND g.type_id = t.id";
            $total = pdo_fetchcolumn("SELECT COUNt(*) FROM ". tablename('we7car_goods')." AS g, ".tablename('we7car_goods_type')." AS t WHERE g.weid = ".$_W['uniacid']." AND g.type_id = t.id");
            $pager = pagination($total,$pindex,$psize);
            $page_sql = " LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            if(!empty($key_word)){
                $sql .= " AND (g.name like '%".$key_word."%' OR g.goods_sn like '%".$key_word."%')";
            }
            if($type_id != 0){
                $sql .= " AND type_id =".$type_id;
            }
            //查所属店铺
            $store_user = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$_W['user']['uid']." AND uniacid =".$_W['uniacid']);
            if(!empty($store_user)) {
                $store_id = $store_user['store_id'];
            }else{
                $store_id = 0;
            }
            $sql .= $page_sql;
            $list = pdo_fetchall($sql);
            $types = pdo_fetchall("SELECT * FROM ".tablename('we7car_goods_type')." WHERE weid = :weid",array('weid' => $_W['uniacid']));
            if(checksubmit('submit')){
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_goods_type', array('listorder' => intval($val)), array('id' => intval($key)));
                }
                message('更新排序成功！', $this->createWebUrl('services', array('op' => 'list')), 'success');
            }

            include $this->template('web/goods_list');
        }

        //添加产品
        if($op == 'post'){
            $types = pdo_fetchall("SELECT * FROM ".tablename('we7car_goods_type'). " WHERE weid = :weid", array('weid' => $_W['uniacid']));
            $brands = pdo_fetchall("SELECT * FROM ".tablename('we7car_goods_brand')." WHERE weid =".$_W['uniacid']);
            $id = intval($_GPC['id']);
            if ($id > 0) {
                $theone = pdo_fetch('SELECT * FROM ' . tablename('we7car_goods') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));
            } else {
                $theone = array('status' => 1, 'listorder' => 0);
            }
            if(checksubmit('submit')){
                $image = $_GPC['image'];
                $listorder = $_GPC['listorder'];
                $name = $_GPC['name'];
                $type_id = $_GPC['type_id'];
                $spec = $_GPC['spec'];
                $price = $_GPC['price'];
                $description = $_GPC['description'];
                $status = $_GPC['status'];
                $goods_sn = $_GPC['goods_sn'];
                //查所属店铺
                $store_user = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$_W['user']['uid']." AND uniacid =".$_W['uniacid']);
                if(!empty($store_user)) {
                    $store_id = $store_user['store_id'];
                }else{
                    $store_id = 0;
                }
                $goods_brand = $_GPC['goods_brand'];
                $insert = array(
                    'image' => $image,
                    'listorder' => $listorder,
                    'name' => $name,
                    'type_id' => $type_id,
                    'spec' => $spec,
                    'price' => $price,
                    'store_id' => $store_id,
                    'description' => $description,
                    'status' => $status,
                    'goods_sn' => $goods_sn,
                    'goods_brand' => $goods_brand,
                    'weid' => $_W['uniacid'],
                    'createtime' => TIMESTAMP,
                );
                if (empty($id)) {
                    pdo_insert('we7car_goods', $insert);
                    !pdo_insertid() ? message('保存品牌数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_goods', $insert, array('id' => $id)) === false) {
                        message('更新商品数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新商品数据成功！', $this->createWebUrl('goods', array('op' => 'list')), 'success');
            }

            include $this->template('web/goods_post');
        }

        //产品类型
        if($op == 'goods_type'){
            $list = pdo_fetchall("SELECT t1.*, t2.name as `top_name` FROM ".tablename('we7car_goods_type'). " as T1, ".tablename('we7car_goods_type')." as T2 WHERE T1.weid = :weid and T1.top_id = T2.id UNION SELECT *, '无' FROM ".tablename('we7car_goods_type')." where top_id = 0 ORDER BY listorder", array('weid' => $_W['uniacid']));
            //查所属店铺
            $store_user = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$_W['user']['uid']." AND uniacid =".$_W['uniacid']);
            if(!empty($store_user)) {
                $store_id = $store_user['store_id'];
            }else{
                $store_id = 0;
            }
            if(checksubmit('submit')){
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_goods_type', array('listorder' => intval($val)), array('id' => intval($key)));
                }
                message('更新排序成功！', $this->createWebUrl('goods', array('op' => 'goods_type')), 'success');
            }

            include $this->template('web/goods_type');
        }

        //添加商品类型
        if($op == 'add_type'){
            $name = trim($_POST['name']);
            $top_id = empty($_POST['top_id']) ? 0 : $_POST['top_id'];
            //查所属店铺
            $store_user = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$_W['user']['uid']." AND uniacid =".$_W['uniacid']);
            if(!empty($store_user)) {
                $store_id = $store_user['store_id'];
            }else{
                $store_id = 0;
            }
            $insert = array(
                'name' => $name,
                'top_id' => $top_id,
                'createtime' => TIMESTAMP,
                'listorder' => 255,
                'store_id' => $store_id,
                'status' => 1,
                'weid' => $_W['uniacid'],
            );
            if(pdo_insert('we7car_goods_type', $insert) == 1){
                echo 111;
            }else{
                echo 222;
            }
            exit;
        }

        //删除产品类型
        if($op == 'del_type'){
            $id = $_GPC['id'];
            $temp = pdo_delete("we7car_goods_type", array("weid" => $_W['uniacid'], 'id' => $id));
            if ($temp == false) {
                message('抱歉，删除数据失败！', '', 'error');
            } else {
                pdo_delete("we7car_goods_type", array("weid" => $_W['uniacid'], 'top_id' => $id));
                message('删除数据成功！', $this->createWebUrl('goods', array('op' => 'goods_type')), 'success');
            }
        }

        //删除产品
        if($op == 'del'){
            $id = $_GPC['id'];
            $temp = pdo_delete("we7car_goods", array("weid" => $_W['uniacid'], 'id' => $id));
            if($temp == false){
                message('抱歉，删除数据失败', '', 'error');
            } else {
                message('删除数据成功！', $this->createWebUrl('goods', array('op' => 'list')), 'success');
            }
        }

        //产品品牌
        if($op == 'goods_brand'){
            $list = pdo_fetchall("SELECT * FROM ".tablename('we7car_goods_brand')." WHERE weid =".$_W['uniacid']);
            //查所属店铺
            $store_user = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$_W['user']['uid']." AND uniacid =".$_W['uniacid']);
            if(!empty($store_user)) {
                $store_id = $store_user['store_id'];
            }else{
                $store_id = 0;
            }
            if (checksubmit('submit')) {
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_goods_brand', array('listorder' => intval($val)), array('id' => intval($key)));
                }
                message('更新品牌排序成功！', $this->createWebUrl('goods', array('op' => 'goods_brand')), 'success');
            }
            include $this->template('web/goods_brand');
        }

        //添加产品品牌
        if($op == 'add_brand'){
            $id = $_GPC['id'];
            if($id > 0){
                $theone = pdo_fetch('SELECT * FROM ' . tablename('we7car_goods_brand') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));
            } else {
                $theone = array('status' => 1, 'listorder' => 0);
            }
            if(checksubmit('submit')){
                $name = $_GPC['name'];
                $logo = $_GPC['logo'];
                $listorder = $_GPC['listorder'];
                $description = $_GPC['description'];
                $status = intval($_GPC['status']);
                //查所属店铺
                $store_user = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$_W['user']['uid']." AND uniacid =".$_W['uniacid']);
                if(!empty($store_user)) {
                    $store_id = $store_user['store_id'];
                }else{
                    $store_id = 0;
                }
                $insert = array(
                    'name' => $name,
                    'logo' => $logo,
                    'listorder' => $listorder,
                    'store_id' => $store_id,
                    'description' => $description,
                    'status' => $status,
                    'createtime' => TIMESTAMP,
                    'weid' => $_W['uniacid'],
                );
                if (empty($id)) {
                    pdo_insert('we7car_goods_brand', $insert);
                    !pdo_insertid() ? message('保存数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_goods_brand', $insert, array('id' => $id)) === false) {
                        message('更新数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新数据成功！', $this->createWebUrl('goods', array('op' => 'goods_brand')), 'success');
            }
            include $this->template('web/add_brand');
        }
    }

    //店铺管理
    public function doWebStore(){
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        if($op == 'list'){

            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_stores')." WHERE weid=".$_W['uniacid']);
            $sql = "SELECT * FROM ".tablename('we7car_stores')." WHERE weid=".$_W['uniacid'];

            $pager = pagination($total,$pindex,$psize);
            $page_sql = " LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

            $sql .= $page_sql;
            $list = pdo_fetchall($sql);

            foreach($list as $key=>$l){
                $region = pdo_fetch("SELECT * FROM ".tablename('we7car_region')." WHERE id =".$l['region_id']);
                $region_name = $this->find_region($region['parent_id'],$region['name'],$region['weid']);
                $list[$key]['region_name'] = $region_name;
            }
            include $this->template('web/store_list');
        }

        if($op == 'post'){
            $id = intval($_GPC['id']);
            if ($id > 0) {
                $theone = pdo_fetch('SELECT * FROM ' . tablename('we7car_stores') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));

                $one_region = pdo_fetch("SELECT * FROM ".tablename('we7car_region')." WHERE weid=".$_W['uniacid']." AND id=".$theone['region_id']);
                $region_name = $this->find_region($one_region['parent_id'],$one_region['name'],$one_region['weid']);
            } else {
                $theone = array('status' => 1, 'listorder' => 0);
            }
            //店铺类型
            $types = pdo_fetchall("SELECT * FROM ".tablename('we7car_store_type')." WHERE weid =".$_W['uniacid']);
            $region = pdo_fetchall("SELECT * FROM ".tablename('we7car_region')." WHERE weid =".$_W['uniacid']." AND parent_id = 1");
            if(checksubmit('submit')){
                $store_name = trim($_GPC['store_name']) ? trim($_GPC['store_name']) : message('请填写店铺名称！');
                $address = trim($_GPC['address']) ? trim($_GPC['address']) : message('请填写店铺地址！');
                $contacts = trim($_GPC['contacts']) ? trim($_GPC['contacts']) : message('请填写联系人！');
                $phone = trim($_GPC['phone']) ? trim($_GPC['phone']) : message('请填写联系电话！');
                $description = $_GPC['description'];
                $status = intval($_GPC['status']);
                $type_id = $_GPC['type'];
                $region_id = $_GPC['region_id'] != 0 ? $_GPC['region_id'] : message('请选择区域');
                $insert = array(
                    'store_name' => $store_name,
                    'address' => $address,
                    'contacts' => $contacts,
                    'description' => $description,
                    'phone' => $phone,
                    'type_id' => $type_id,
                    'region_id' => $region_id,
                    'status' => $status,
                    'weid' => $_W['uniacid'],
                    'createtime' => TIMESTAMP
                );
                if (empty($id)) {
                    pdo_insert('we7car_stores', $insert);
                    !pdo_insertid() ? message('保存数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_stores', $insert, array('id' => $id)) === false) {
                        message('更新店铺数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新店铺数据成功！', $this->createWebUrl('store', array('op' => 'list')), 'success');
            }

            include $this->template('web/store_post');
        }

        if($op == 'store_user'){
            $store_id = $_GPC['id'];
            $store = pdo_fetch("SELECT * From ".tablename('we7car_stores')." WHERE id = ".$store_id);
            //查同一个uniacid下面的所有用户
            $users = pdo_fetchall("SELECT u.* FROM ".tablename('users')." as u, ".tablename('uni_account_users')." as cu WHERE u.uid = cu.uid AND cu.uniacid =".$_W['uniacid'] );
            foreach($users as $key=>$user){
                $has_store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$user['uid']);
                if(!empty($has_store)){
                    unset($users[$key]);
                }
            }
            $store_users = pdo_fetchall("SELECT u.*, su.id as id FROM ".tablename('we7car_store_user')." as su, ".tablename('users')." as u WHERE su.store_id =".$store_id." AND u.uid=su.uid");

            include $this->template('web/store_user');
        }

        //删除操作员
        if($op == 'del_store_user'){
            $id = $_GPC['id'];
            $store_id = $_GPC['store_id'];
            $temp = pdo_delete("we7car_store_user", array("uniacid" => $_W['uniacid'], 'id' => $id, 'store_id' => $store_id));
            if ($temp == false) {
                message('抱歉，删除数据失败！', '', 'error');
            } else {
                message('删除数据成功！', $this->createWebUrl('store', array('op' => 'store_user' , 'id' => $store_id)), 'success');
            }
        }

        //添加店铺操作员
        if($op == 'store_add_user'){
            $uid = $_GPC['uid'];
            $store_id = $_GPC['store_id'];
            if(!empty($uid)){
                $check = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$uid);
                if(empty($check)) {
                    $insert = array(
                        'uid' => $uid,
                        'store_id' => $store_id,
                        'uniacid' => $_W['uniacid'],
                    );
                    pdo_insert('we7car_store_user', $insert);
                    !pdo_insertid() ? message('操作员添加失败, 请稍后重试.', 'error') : '';
                    message('添加操作员成功！', $this->createWebUrl('store', array('op' => 'store_user', 'id' => $store_id)), 'success');
                }else{
                     message('会员已经绑定了店铺，不能重复绑定.',$this->createWebUrl('store', array('op' => 'store_user', 'id' => $store_id)), 'error') ;
                }
            }
        }

        //店铺类型管理
        if($op == 'type'){
            $list = pdo_fetchall("SELECT t1.*, t2.type_name as `top_name` FROM ".tablename('we7car_store_type'). " as T1, ".tablename('we7car_store_type')." as T2 WHERE T1.weid = :weid and T1.parent_id = T2.id UNION SELECT *, '无' FROM ".tablename('we7car_store_type')." where parent_id = 0 ORDER BY listorder", array('weid' => $_W['uniacid']));
            include $this->template('web/store_type');
        }

        //增加店铺类型
        if($op == 'add_type'){
            $name = trim($_POST['name']);
            $top_id = empty($_POST['top_id']) ? 0 : $_POST['top_id'];
            $insert = array(
                'type_name' => $name,
                'parent_id' => $top_id,
                'createtime' => TIMESTAMP,
                'listorder' => 255,
                'status' => 1,
                'weid' => $_W['uniacid'],
            );
            if(pdo_insert('we7car_store_type', $insert) == 1){
                echo 111;
            }else{
                echo 222;
            }
            exit;
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

        //摄像枪设置
        if($op == 'camera'){
            $store_id = $_GPC['id'];
            $camera = pdo_fetchall("SELECT * FROM ".tablename('we7car_camera')." WHERE store_id =".$store_id." AND weid =".$_W['uniacid']);
            include $this->template('web/camera');
        }

        //添加摄像枪
        if($op == 'add_camera'){
            $store_id = $_GPC['store_id'];
            $name = $_GPC['name'];
            $url = $_GPC['url'];
            if(empty($store_id) || empty($name) || empty($url)){
                message('录入信息错误，请重新录入',$this->createWebUrl('store', array('op' => 'camera', 'id' => $store_id)),'error');
            }
            $insert = array(
                'name' => $name,
                'url' => $url,
                'store_id' => $store_id,
                'weid' => $_W['uniacid']
            );
            if(pdo_insert('we7car_camera',$insert) == 1) {
                message('操作成功！', $this->createWebUrl('store', array('op' => 'camera', 'id' => $store_id)), 'success');
            }else{
                message('系统繁忙，请稍后重试',$this->createWebUrl('store', array('op' => 'camera', 'id' => $store_id)),'error');
            }
        }

        //删除摄像枪
        if($op == 'cancel_camera'){
            $id = $_GPC['id'];
            $camera = pdo_fetch("SELECT * FROM ".tablename('we7car_camera')." WHERE id =".$id." AND weid =".$_W['uniacid']);
            pdo_delete('we7car_camera',array('id' => $id,'weid' => $_W['uniacid']));
            message('操作成功！', $this->createWebUrl('store', array('op' => 'camera', 'id' => $camera['store_id'])), 'success');
        }

    }

    //施工管理
    public function doWebWorker(){
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';

        if($op == 'list'){
            $uid = $_W['user']['uid'];
            $store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid=".$uid." AND uniacid =".$_W['uniacid']);
            //找等待开工的订单
            $list = pdo_fetchall("SELECT o.*,c.car_no FROM ".tablename('we7car_orders')." AS o,".tablename('we7car_care')." AS c WHERE o.status = 11 AND o.worker_id = 0 AND o.car_id=c.id AND o.weid =".$_W['uniacid']." ORDER BY createtime");
            include $this->template('web/worker_orders');
        }

        //工人接单
        if($op == 'get'){
            $id = $_GPC['id'];
            $uid = $_W['user']['uid'];
            $store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid = ".$uid." AND uniacid=".$_W['uniacid']);
            $check = pdo_fetchcolumn("SELECT count(*) FROM ".tablename('we7car_orders')." WHERE worker_id =".$uid." AND status <= 20 AND store_id =".$store['store_id']);
            if($check > 0){
                message('您还有未完成的工单，不能接单！!',$this->createWebUrl('worker',array('op'=>'list')),'error');
            }
            $insert = array(
                'worker_id' => $uid,
                'get_time' => TIMESTAMP,
                'status' => 20,
            );
            if(pdo_update('we7car_orders',$insert,array('id' => $id,'weid' => $_W['uniacid'],'worker_id' => 0)) == false){
                message('接单失败，请稍后重试!',$this->createWebUrl('worker',array('op'=>'list')),'error');
            }else{
                message('接单成功!',$this->createWebUrl('worker',array('op'=>'worker_order')),'success');
            }
        }

        //工人工单
        if($op == 'worker_order'){
            $uid = $_W['user']['uid'];
            $store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid = ".$uid." AND uniacid=".$_W['uniacid']);
            $order_data = pdo_fetch("SELECT * FROM ".tablename('we7car_orders')." WHERE worker_id =".$uid." AND weid =".$_W['uniacid']." AND store_id =".$store['store_id']." AND status < 30");
            //店铺摄像头
            $camera = pdo_fetchall("SELECT * FROM ".tablename('we7car_camera')." WHERE store_id =".$store['store_id']." AND weid =".$_W['uniacid']);
            if(!empty($order_data)) {
                $order_id = $order_data['id'];
                $car = pdo_fetch("SELECT * FROM " . tablename('we7car_care') . " WHERE id =" . $order_data['car_id']);
                $check = pdo_fetchall("SELECT *,c.name,c.title1,c.title2 FROM " . tablename('we7car_car_check') . " AS oc," . tablename('we7car_check') . " AS c WHERE oc.weid =" . $_W['uniacid'] . " AND oc.order_id =" . $order_id . " AND oc.check_id = c.id");
                //服务类型
//            $all_system = pdo_fetchall("SELECT * FROM ".tablename('we7car_system'));
                $services = pdo_fetchall("SELECT s.*,sy.name FROM " . tablename('we7car_orders_services') . " AS s," . tablename('we7car_car_services') . " AS sy WHERE order_id =" . $order_id . " AND s.service_id = sy.id");

                $total_price = 0;
                $worker_price = 0;
                foreach ($services as $key => $service) {
                    $worker_price += $service['worker_price'];
                    $services_goods = pdo_fetchall("SELECT sg.*,g.name,g.goods_sn FROM " . tablename('we7car_order_goods') . " as sg," . tablename('we7car_goods') . " as g WHERE sg.order_service_id =" . $service['id'] . " AND sg.weid =" . $_W['uniacid'] . " AND sg.goods_id = g.id");
                    if (!empty($services_goods)) {
                        foreach ($services_goods as $key2 => $g) {
                            $goods_price = $g['quantity'] * $g['goods_price'];
                            $services_goods[$key2]['total_price'] = $goods_price;
                            $total_price += $goods_price;
                        }
                        $services[$key]['goods'] = $services_goods;
                        $services[$key]['count'] = count($services_goods);
                    } else {
                        $services[$key]['count'] = 1;
                    }
                }
                $order_price = $total_price + $worker_price;
                include $this->template('web/worker_order_detail');
            }else{
                message('请先接单！！', $this->createWebUrl('worker', array('op' => 'list')), 'error');
            }
        }

        //选择摄像头
        if($op == 'change_camera'){
            $order_id = $_GPC['order_id'];
            $camera_id = $_GPC['camera_id'];
            $camera = pdo_fetch("SELECT * FROM ".tablename('we7car_camera')." WHERE id =".$camera_id);
            //如果摄像头有施工中的订单，就不能修改
            if(!empty($camera['order_id'])){
                $order = pdo_fetch("SELECT * FROM ".tablename('we7car_orders')." WHERE id =".$camera['order_id']);
                if($order['status'] == 20){
                    echo 222;
                    exit;
                }
            }
            $camera['order_id'] = $order_id;
            //修改之前，将之前选择的摄像头order_id清了
            $cameras = pdo_fetchall("SELECT * FROM ".tablename('we7car_camera')." WHERE order_id =".$order_id);
            foreach($cameras as $c){
                $c['order_id'] = 0;
                pdo_update('we7car_camera',$c,array('id' => $c['id']));
            }
            if(pdo_update('we7car_camera',$camera,array('id' => $camera['id'])) == false){
                echo 333;
                exit;
            }else{
                echo 111;
                exit;
            }
        }

        //修改维修里程
        if($op == 'change_mileage'){
            $mileage = $_GPC['mileage'];
            $order_id = $_GPC['order_id'];
            pdo_update('we7car_orders',array('mileage' => intval($mileage)), array('id' => intval($order_id)));
            echo 111;
            exit;
        }

        //填写检测报告
        if($op == 'input_check'){
            $check = $_GPC['check'];
            $remark = $_GPC['remark'];
            $order_id = $_GPC['order_id'];
            $worker_id = $_W['user']['uid'];
            foreach($check as $key=>$c){
                pdo_update('we7car_car_check',array('worker_id' => $worker_id,'check_result' => $c,'remark' => $remark[$key],'check_time' => TIMESTAMP),array('check_id' => $key,'order_id' => $order_id));
            }
            message('操作成功！！', $this->createWebUrl('worker', array('op' => 'worker_order')), 'success');
        }

        //开始施工
        if($op == 'begin_work'){
            $service_id = $_GPC['service_id'];
            $order_id = $_GPC['order_id'];
            $worker_id = $_W['user']['uid'];
            pdo_update('we7car_orders_services',array('begin_time' => TIMESTAMP,'worker_id' => $worker_id),array('order_id' => $order_id,'service_id' => $service_id,'weid' => $_W['uniacid']));
            echo 111;
            exit;
        }

        //完工
        if($op == 'finish'){
            $service_id = $_GPC['service_id'];
            $order_id = $_GPC['order_id'];
            $worker_id = $_W['user']['uid'];
            pdo_update('we7car_orders_services',array('end_time' => TIMESTAMP),array('order_id' => $order_id,'service_id' => $service_id,'weid' => $_W['uniacid']));
            echo 111;
            exit;
        }

        //申请验收
        if($op == 'finish_order'){
            $order_id = $_GPC['order_id'];
            $order_services = pdo_fetchall("SELECT end_time FROM ".tablename('we7car_orders_services')." WHERE order_id =".$order_id." AND weid =".$_W['uniacid']);
            $can_finish = true;
            //先检查所有工作是否已经完成
            foreach($order_services as $s){
                if(empty($s['end_time'])){
                    $can_finish = false;
                    break;
                }
            }
            if($can_finish){
                pdo_update('we7car_orders',array('status' => 30),array('id' => $order_id,'weid' => $_W['uniacid']));
                echo 111;
            }else{
                echo 222;
            }
            exit;
        }
    }

    //检测项管理
    public function doWebCheck(){
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';

        if($op == 'list'){
            $check = pdo_fetchall("SELECT * FROM ".tablename('we7car_check')." WHERE weid =".$_W['uniacid']." ORDER BY listorder");
            include $this->template('web/check');
        }

        if($op == 'change'){
            $list = pdo_fetchall("SELECT * FROM ".tablename('we7car_check')." WHERE weid =".$_W['uniacid']." ORDER BY listorder");

            if (checksubmit('submit')) {
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_check', array('listorder' => intval($val)), array('id' => intval($key)));
                }
                message('更新排序成功！', $this->createWebUrl('check', array('op' => 'change')), 'success');
            }

            include $this->template('web/change_check');
        }

        if($op == 'del'){
            $id = $_GPC['id'];
            pdo_delete('we7car_check',array('id'=>$id,'weid'=>$_W['uniacid']));
            message("删除成功！",$this->createWebUrl('check',array('op'=>'change')),'success');
        }

        if($op == 'add'){
            $name = $_GPC['name'];
            $description = $_GPC['description'];
            if(empty($name)){
                echo 222;
                exit;
            }else{
                $insert = array(
                    'name' => $name,
                    'description' => $description,
                    'listorder' => 255,
                    'status' => 1,
                    'weid' => $_W['uniacid'],
                    'title1' => $_GPC['title1'],
                    'title2' => $_GPC['title2'],
                );
                pdo_insert('we7car_check',$insert);
                if(pdo_insertid()){
                    echo 111;
                }else{
                    echo 222;
                }
                exit;
            }
        }

    }

    //订单管理
    /*
     * 订单管理需要用到多店铺，但是里面的用户是分不开，而且登录都是用user表，暂时直接在user表中数据库添加店铺id，如果id为空的话，
     * 可以访问所有的订单数据，如果是有的，仅访问自己所属店铺的订单数据，以后再想办法开展多店铺版
     * 店铺id $_W['user']['car_store'];
     */
    public function doWebOrders(){
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        if($op == 'list'){
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_orders')." WHERE weid=".$_W['uniacid']);
            $sql = "SELECT o.*,c.car_no,st.store_name FROM ".tablename('we7car_orders')." AS o,".tablename('we7car_care')." AS c,".tablename('we7car_stores')." as st WHERE o.weid = ".$_W['uniacid']." AND o.car_id=c.id AND o.store_id=st.id";
            $pager = pagination($total,$pindex,$psize);
            $page_sql = " ORDER BY o.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            //查是否某个店铺的操作员，是的话，就只查这个店铺，不是的话，查所有
            $store_user = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$_W['user']['uid']);
            if(!empty($store_user)){
                $sql .= " AND o.store_id =".$store_user['store_id'];
            }
            $sql .= $page_sql;
            $list = pdo_fetchall($sql);
            include $this->template('web/order_list');
        }

        if($op == 'cancle'){
            $id = $_GPC['id'];
            if(!empty($id)){
                $order = pdo_fetch("SELECT * FROM ".tablename('we7car_orders')." WHERE id =".$id." AND weid=".$_W['uniacid']);
                $order['status'] = 0;
                if (pdo_update('we7car_orders', $order, array('id' => $id)) === false) {
                    message('更新数据失败, 请稍后重试.', 'error');
                }
                message('更新数据成功！', $this->createWebUrl('orders', array('op' => 'list')), 'success');
            }
        }

        if($op == 'post'){
            $system = pdo_fetchall("SELECT * from ".tablename('we7car_system')." where weid =".$_W['uniacid']);
//查所属店铺
            $user_store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uniacid=".$_W['uniacid']." AND uid=".$_W['user']['uid']);
            if(empty($user_store)){
                message('对不起，你还没有关联到任何的店铺！',$this->createWebUrl('orders', array('op' => 'list')), 'error') ;
            }
            if(checksubmit('submit')){
                $car_id = $_GPC['car_id'];
                $mileage = $_GPC['mileage'];
                $order_sn = $this->get_order_sn($_W['uniacid']);
                $insert = array(
                    'order_sn' => $order_sn,
                    'weid' => $_W['uniacid'],
                    'createtime' => TIMESTAMP,
                    'status' => 11,
                    'store_id' => $user_store['store_id'],
                    'car_id' => $car_id,
                    'mileage' => $mileage,
                );
                if (empty($id)) {
                    pdo_insert('we7car_orders', $insert);
                    !pdo_insertid() ? message('保存数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_orders', $insert, array('id' => $id)) === false) {
                        message('更新数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新数据成功！', $this->createWebUrl('orders', array('op' => 'list')), 'success');
            }
            include $this->template('web/order_post');
        }

        //根据系统寻找服务
        if($op == 'find_service'){
            $system_id = $_GPC['system_id'];
            if(!empty($system_id)){
                $services = pdo_fetchall("SELECT * FROM ".tablename('we7car_car_services')." WHERE system_id =".$system_id);
                echo "<option value='0'>请选择服务</option>";
                foreach($services as $service){
                    echo "<option value='".$service['id']."'>".$service['name']."</option>";
                }
            }
        }

        //查找车牌
        if($op == 'get_car'){
            $car_no = $_GPC['car_no'];
            $cars = pdo_fetchall("SELECT * FROM ".tablename('we7car_care')." WHERE car_no like '%".$car_no."%' AND weid =".$_W['uniacid']);
            echo "<option value='0'>请选择客户</option>";
            foreach($cars as $car){
                echo "<option value='".$car['id']."'>".$car['car_no']."</option>";
            }
            exit;
        }

        //查找车辆信息
        if($op == 'get_car_data'){
            $car_id = $_GPC['car_id'];
            $car = pdo_fetch("SELECT * FROM ".tablename('we7car_care')." WHERE id =".$car_id." AND weid =".$_W['uniacid']);
            $last_order = pdo_fetch("SELECT * FROM ".tablename('we7car_orders')." WHERE car_id =".$car_id." AND weid =".$_W['uniacid']." AND status = 50 ORDER BY 'createtime'");
            if(!empty($last_order)){
                $car['car_care_lastDate'] = date("Y-m-d",$last_order['finish_time']);
                $car['car_care_mileage'] = $last_order['mileage'];
            }
            $car = json_encode($car);
            echo $car;
            exit;
        }

        //查看订单详情
        if($op == 'order_detail'){
            $order_id = $_GPC['id'];
            $order_data = pdo_fetch("SELECT * FROM ".tablename('we7car_orders')." WHERE weid=".$_W['uniacid']." AND id =".$order_id);
            $car = pdo_fetch("SELECT * FROM ".tablename('we7car_care')." WHERE id =".$order_data['car_id']);
            $check = pdo_fetchall("SELECT *,c.name,c.title1,c.title2 FROM ".tablename('we7car_car_check')." AS oc,".tablename('we7car_check')." AS c WHERE oc.weid =".$_W['uniacid']." AND oc.order_id =".$order_id." AND oc.check_id = c.id");
            //服务类型
            $all_system = pdo_fetchall("SELECT * FROM ".tablename('we7car_system'));
            $services = pdo_fetchall("SELECT s.*,sy.name FROM ".tablename('we7car_orders_services')." AS s,".tablename('we7car_car_services')." AS sy WHERE order_id =".$order_id." AND s.service_id = sy.id");

            $total_price = 0;
            $worker_price = 0;
            foreach($services as $key=>$service){
                $worker_price += $service['worker_price'];
                $services_goods = pdo_fetchall("SELECT sg.*,g.name,g.goods_sn FROM ".tablename('we7car_order_goods')." as sg,".tablename('we7car_goods')." as g WHERE sg.order_service_id =".$service['id']." AND sg.weid =".$_W['uniacid']." AND sg.goods_id = g.id");
                if(!empty($services_goods)) {
                    foreach ($services_goods as $key2 => $g) {
                        $goods_price = $g['quantity'] * $g['goods_price'];
                        $services_goods[$key2]['total_price'] = $goods_price;
                        $total_price += $goods_price;
                    }
                    $services[$key]['goods'] = $services_goods;
                    $services[$key]['count'] = count($services_goods);
                }else{
                    $services[$key]['count'] = 1;
                }
            }
            $order_price = $total_price+$worker_price;
            include $this->template('web/order_detail');
        }

        //增加服务
        if($op == 'add_service'){
            $order_id = $_GPC['order_id'];
            $service_id = $_GPC['service_id'];
            if($order_id > 0 && $service_id > 0){
                $check = pdo_fetch("SELECT * FROM ".tablename('we7car_orders_services')." WHERE order_id=".$order_id." AND service_id =".$service_id);
                if(empty($check)){
                    $insert = array(
                        'order_id' => $order_id,
                        'weid' => $_W['uniacid'],
                        'createtime' => TIMESTAMP,
                        'service_id' => $service_id,
                    );
                    pdo_insert('we7car_orders_services',$insert);
                    echo 111;
                }else{
                    echo 222;
                }
                exit;
            }
        }

        //删除产品
        if($op == 'del_goods'){
            $id = $_GPC['id'];
            pdo_delete('we7car_order_goods',array('id' => $id,'weid' => $_W['uniacid']));
            echo 111;
            exit;
        }

        //增加检测项目
        if($op == 'add_check'){
            $order_id = $_GPC['order_id'];
            $order = pdo_fetch("SELECT * FROM ".tablename('we7car_orders')." WHERE weid =".$_W['uniacid']." AND id =".$order_id);
            $car_check = pdo_fetch("SELECT * FROM ".tablename('we7car_car_check')." WHERE order_id =".$order_id." AND weid =".$_W['uniacid']);
            if(!empty($car_check)){
                echo 333;
                exit;
            }
            $check = pdo_fetchall("SELECT * FROM ".tablename('we7car_check')." WHERE weid =".$_W['uniacid']." AND status = 1 ORDER BY listorder");
            foreach($check as $c){
                $insert = array(
                    'weid' => $_W['uniacid'],
                    'check_id' => $c['id'],
                    'car_id' => $order['car_id'],
                    'order_id' => $order_id,
                );
                pdo_insert('we7car_car_check',$insert);
            }
            echo 111;
            exit;
        }

        //搜索产品
        if($op == 'find_goods'){
            $key_word = $_GPC['key_word'];
            $goods = pdo_fetchall("SELECT * FROM ".tablename('we7car_goods')." WHERE name like '%".$key_word."%' "." OR goods_sn like '%".$key_word."%'"." AND weid =".$_W['uniacid']);
            echo "<option value='0'>请选择产品</option>";
            foreach($goods as $good){
                echo "<option value='".$good['id']."'>".$good['name']."(".$good['goods_sn'].")"."</option>";
            }
            exit;
        }

        //添加产品 20161225
        if($op == 'add_goods'){
            $service_id = $_GPC['service_id'];
            $order_id = $_GPC['order_id'];
            if(empty($service_id) || empty($order_id)){
                message('参数错误',$this->createWebUrl('add_goods', array('op' => 'list')), 'error') ;
            }
            if(checksubmit('submit')){
                $goods_id = $_GPC['goods_id'];
                if($goods_id > 0){
                    $goods = pdo_fetch("SELECT * FROM ".tablename('we7car_goods')." WHERE id =".$goods_id);
                    $check = pdo_fetch("SELECT * FROM ".tablename('we7car_order_goods')." WHERE goods_id =".$goods_id." AND order_service_id =".$service_id);
                    if(!empty($check)){
                        message('您已经添加过产品了',$this->createWebUrl('orders', array('op' => 'add_goods','service_id' => $service_id,'order_id' => $order_id)), 'error') ;
                    }else{
                        $insert = array(
//                            'order_id' => $order_id,
                            'goods_id' => $goods_id,
                            'order_service_id' => $service_id,
                            'goods_price' => $goods['price'],
                            'quantity' => 1,
                            'weid' => $_W['uniacid'],
                            'createtime' => TIMESTAMP,
                        );
                        pdo_insert('we7car_order_goods',$insert);
                        message('添加成功！',$this->createWebUrl('orders', array('op' => 'order_detail','id' => $order_id)), 'success') ;
                    }
                }
            }
            include $this->template('web/add_goods');
        }

        //修改工时费
        if($op == 'worker_price_change'){
            $id = $_GPC['id'];
            $price = $_GPC['price'];
            if($price >= 0){
                $insert = array(
                    'worker_price' => $price,
                );
                if(pdo_update('we7car_orders_services',$insert,array('id' => $id,'weid' => $_W['uniacid'])) == false){
                    echo 222;
                }else{
                    echo 111;
                }
            }
            exit;
        }

        //修改产品价格
        if($op == 'goods_price_change'){
            $id = $_GPC['id'];
            $price = $_GPC['price'];
            if($price >= 0){
                $insert = array(
                    'goods_price' => $price,
                );
                if(pdo_update('we7car_order_goods',$insert,array('id' => $id,'weid' => $_W['uniacid'])) == false){
                    echo 222;
                }else{
                    echo 111;
                }
            }
            exit;
        }

        //修改产品数量
        if($op == 'goods_quantity_change'){
            $id = $_GPC['id'];
            $quantity = $_GPC['quantity'];
            if($quantity >= 0){
                $insert = array(
                    'quantity' => $quantity,
                );
                if(pdo_update('we7car_order_goods',$insert,array('id' => $id,'weid' => $_W['uniacid'])) == false){
                    echo 222;
                }else{
                    echo 111;
                }
            }
            exit;
        }

        //添加产品
        if($op == 'goods_add'){
            //查所属店铺
            $store_user = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uid =".$_W['user']['uid']." AND uniacid =".$_W['uniacid']);
            if(!empty($store_user)) {
                $store_id = $store_user['store_id'];
            }else{
                $store_id = 0;
            }
            if($store_id == 0){
                message('对不起，你还没有关联到任何的店铺！',$this->createWebUrl('orders', array('op' => 'list')), 'error') ;
            }
            $order_id = $_GPC['order_id'];
            $key_word = $_GPC['key_word'];
            $type_id = empty($_GPC['type_id'])? 0 : $_GPC['type_id'];
            $sql = "SELECT g.*, t.name as type_name FROM ". tablename('we7car_goods')." AS g, ".tablename('we7car_goods_type')." AS t WHERE g.weid = ".$_W['uniacid']." AND g.type_id = t.id";
            $total = pdo_fetchcolumn("SELECT COUNt(*) FROM ". tablename('we7car_goods')." AS g, ".tablename('we7car_goods_type')." AS t WHERE g.weid = ".$_W['uniacid']." AND g.type_id = t.id");
            $pager = pagination($total,$pindex,$psize);
            $page_sql = " LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            if(!empty($key_word)){
                $sql .= " AND (g.name like '%".$key_word."%' OR g.goods_sn like '%".$key_word."%')";
            }
            if($type_id != 0){
                $sql .= " AND type_id =".$type_id;
            }
            $sql .= $page_sql;
            $list = pdo_fetchall($sql);
            foreach($list as $key=>$l){
                $stock = pdo_fetch("SELECT * FROM ".tablename('we7car_stock')." WHERE store_id =".$store_id." AND weid=".$_W['uniacid']." AND goods_id =".$l['id']);
                $list[$key]['stock'] = $stock['quantity'] == 0 ? 0 : $stock['quantity'];
            }
            $types = pdo_fetchall("SELECT * FROM ".tablename('we7car_goods_type')."  WHERE weid = :weid",array('weid' => $_W['uniacid']));
            $order_goods = pdo_fetchall("SELECT og.*,g.name,g.image FROM ".tablename('we7car_order_goods')." as og,".tablename('we7car_goods')." as g WHERE og.order_id =".$order_id." AND og.weid =".$_W['uniacid']." AND og.goods_id=g.id");
            $goods_total = 0;
            foreach($order_goods as $key=>$val){
                $total = $val['quantity']*$val['goods_price'];
                $goods_total += $total;
                $order_goods[$key]['total'] = number_format($total, 2, '.', '');
            }
            $goods_total = round($goods_total,2);
            $goods_total = number_format($goods_total, 2, '.', '');
            include $this->template('web/goods_add');
        }

        if($op == 'add') {
            $quantity = $_GPC['quantity'];
            $price = $_GPC['price'];
            $goods_id = $_GPC['goods_id'];
            $order_id = $_GPC['order_id'];
            $return = array();
            if ($quantity <= 0 || $price <= 0 || $goods_id <= 0 || $order_id <= 0) {
                $return['error'] = '输入参数不正确';
            } else {
                //查所属店铺
                $store_user = pdo_fetch("SELECT * FROM " . tablename('we7car_store_user') . " WHERE uid =" . $_W['user']['uid'] . " AND uniacid =" . $_W['uniacid']);
                if (!empty($store_user)) {
                    $store_id = $store_user['store_id'];
                } else {
                    $store_id = 0;
                }
                //先查是否有足够的库存
                $stock = pdo_fetch("SELECT * FROM " . tablename('we7car_stock') . " WHERE weid=" . $_W['uniacid'] . " AND goods_id =" . $goods_id . " AND store_id =" . $store_id);
                    if (empty($stock) || $stock['quantity'] == 0) {
                        $return['error'] = '库存不足！';
                    } else {
                        $check = pdo_fetch("SELECT * FROM " . tablename('we7car_order_goods') . " WHERE weid=" . $_W['uniacid'] . " AND order_id =" . $order_id . " AND goods_id =" . $goods_id);
                        if (!empty($check)) {
                            $return['error'] = '该产品已经添加了';
                        } else {
                            $insert = array(
                                'weid' => $_W['uniacid'],
                                'createtime' => TIMESTAMP,
                                'order_id' => $order_id,
                                'goods_id' => $goods_id,
                                'goods_price' => $price,
                                'quantity' => $quantity,
                                'status' => 1,
                            );
                            pdo_insert('we7car_order_goods', $insert);
                            if (!pdo_insertid()) {
                                $return['error'] = '保存失败，请稍后重试！';
                            } else {
                                $return['error'] = 0;
                            }
                        }
                    }
                }
                $return = json_encode($return);
                echo $return;
                exit;
            }

        //删除产品
        if($op == 'del_goods'){
            $id = $_GPC['id'];
            $order_id = $_GPC['order_id'];
            pdo_delete('we7car_order_goods',array('id'=>$id,'order_id'=>$order_id,'weid'=>$_W['uniacid']));
            message("删除成功！",$this->createWebUrl('orders',array('op'=>'goods_add','order_id'=>$order_id)),'success');
        }

        //修改商品价格/数量
        if($op == 'change_goods'){
            $price = $_GPC['price'];
            $quantity = $_GPC['quantity'];
            $order_id = $_GPC['order_id'];
            $id = $_GPC['id'];
            $return = array();
            //审核订单状态，只有未付款的订单才能进行该类操作
            $order = pdo_fetch("SELECT * FROM ".tablename('we7car_orders')." WHERE id=".$order_id." AND weid=".$_W['uniacid']);
            if($order['status'] < 11 || $order['status'] > 30){
                $return['error'] = '订单当前状态不允许修改！';
            }else {
                if ($price < 0 || $quantity <= 0 || $order_id <= 0 || $id <= 0) {
                    $return['error'] = '参数不正确！';
                } else {
                    $insert = array(
                        'quantity' => $quantity,
                        'goods_price' => $price
                    );
                    if (pdo_update('we7car_order_goods', $insert, array('order_id' => $order_id, 'id' => $id, 'weid' => $_W['uniacid'])) == false) {
                        $return['error'] = '服务器繁忙，请稍后重试！';
                    } else {
                        $return['error'] = 0;
                    }
                }
            }
            $return = json_encode($return);
            echo $return;
            exit;
        }

        //确认收钱
        if($op == 'get_money'){
            $id = $_GPC['id'];
            $insert = array(
                'pay_time' => TIMESTAMP,
                'pay_type' => '现金支付',
                'status' => 40,
            );
            //扣除库存
            $this->change_stock($id,'reduce',$_W['uniacid'],$_W['user']['uid']);
            //先查所有的服务
            if (pdo_update('we7car_orders', $insert, array('id' => $id, 'weid' => $_W['uniacid'])) == false) {
                message('操作失败，请稍后重试！', $this->createWebUrl('orders', array('op' => 'list')), 'error');
            } else {
                message('操作成功！！', $this->createWebUrl('orders', array('op' => 'list')), 'success');
            }
        }

        //确认交车
        if($op == 'leave'){
            $id = $_GPC['id'];
            $insert = array(
                'finish_time' => TIMESTAMP,
                'status' => 50,
            );
            //更新汽车信息
            $order = pdo_fetch("SELECT * FROM ".tablename('we7car_orders')." WHERE id =".$id);
            pdo_update('we7car_care',array('car_care_lastDate' => TIMESTAMP,'car_care_mileage' => $order['mileage']),array('id' => $order['car_id'],'weid' => $_W['uniacid']));
            if (pdo_update('we7car_orders', $insert, array('id' => $id, 'weid' => $_W['uniacid'])) == false) {
                message('操作失败，请稍后重试！', $this->createWebUrl('orders', array('op' => 'list')), 'error');
            } else {
                message('操作成功！！', $this->createWebUrl('orders', array('op' => 'list')), 'success');
            }
        }
    }


    //改变库存  $type为改变类型，reduce为减，add为加
    public function change_stock($order_id,$type,$weid,$user_id){
        $order_services = pdo_fetchall("SELECT * FROM ".tablename('we7car_orders_services')." WHERE order_id =".$order_id);
        $service_ids = array();
        foreach($order_services as $service){
            $service_ids[] = $service['id'];
        }
        $service_id = implode(',',$service_ids);
        $goods = pdo_fetchall("SELECT * FROM ".tablename('we7car_order_goods')." WHERE order_service_id in (".$service_id.") AND weid=".$weid);
        //查所属店铺
        $store_user = pdo_fetch("SELECT * FROM " . tablename('we7car_store_user') . " WHERE uid =" . $user_id . " AND uniacid =" . $weid);
        foreach($goods as $good){
            $stock = pdo_fetch("SELECT * FROM ".tablename('we7car_stock')." WHERE weid=".$weid." AND store_id =".$store_user['store_id']." AND goods_id =".$good['goods_id']);
            switch($type){
                case 'reduce':$stock['quantity'] -= $good['quantity'];
                    break;
                case 'add' : $stock['quantity'] += $good['quantity'];
                    break;
            }
            pdo_update('we7car_stock',$stock,array('id' => $stock['id']));
            $new_log = array(
                'weid' => $weid,
                'store_id' => $store_user['store_id'],
                'goods_id' => $good['goods_id'],
                'quantity' => $good['quantity'],
                'user_id' => $user_id,
                'remark' => '客户汽车保养',
                'price' => $good['goods_price'],
                'status' => 1,
                'createtime' => TIMESTAMP,
                'order_id' => $order_id,
                'type' => 1
            );
            pdo_insert('we7car_stock_log',$new_log);
        }
    }

    //地域管理
    public function doWebRegion(){
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';

        if($op == 'list'){
            $all_regions = pdo_fetchall("SELECT * FROM ".tablename('we7car_region')." WHERE weid =".$_W['uniacid']);


            if(checksubmit('submit')){
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_region', array('listorder' => intval($val)), array('id' => intval($key)));
                }
                message('更新地区排序成功！', $this->createWebUrl('region', array('op' => 'list')), 'success');
            }

            include $this->template('web/region_list');
        }

        if($op == 'post'){
            $id = $_GPC['id'];
            if(!empty($id)){
                $region = pdo_fetch("SELECT * FROM ".tablename('we7car_region')." WHERE id =".$id." AND weid =".$_W['uniacid']);
            }else{
                $region['listorder'] = 255;
                $region['status'] = 1;
            }
            $all_regions = pdo_fetchall("SELECT * FROM ".tablename('we7car_region')." WHERE weid =".$_W['uniacid']);
            if(checksubmit('submit')){
                $name = $_GPC['name'];
                if(empty($name)){
                    message("请填写地区名称！",$this->createWebUrl('region',array('op' => 'post')), 'error');
                }
                $check = pdo_fetch("SELECT * from ".tablename('we7car_region')." WHERE weid =".$_W['uniacid']." AND name like '".$name."'");
                if(!empty($check)){
                    message("地区已经存在！",$this->createWebUrl('region',array('op' => 'post')), 'error');
                }
                $listorder = $_GPC['listorder'];
                $parent_id = $_GPC['parent_id'];
                $status = $_GPC['status'];
                $insert = array(
                    'name' => $name,
                    'weid' => $_W['uniacid'],
                    'listorder' => $listorder,
                    'parent_id' => $parent_id,
                    'status' => $status,
                );
                if (empty($id)) {
                    pdo_insert('we7car_region', $insert);
                    !pdo_insertid() ? message('保存数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_region', $insert, array('id' => $id)) === false) {
                        message('更新数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新数据成功！', $this->createWebUrl('region', array('op' => 'list')), 'success');
            }
            include $this->template('web/region_post');
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

    //自动生成订单号
    function get_order_sn($weid){
        $date = date("Ymd",time());
        $rand = rand(100000,999999);
        $order_sn = $date.$rand;
        $check_sn = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_orders')." WHERE weid =".$weid." AND order_sn =".$order_sn);
        if($check_sn > 0){
            return $this->get_order_sn($weid);
        }else{
            return $order_sn;
        }
    }


    //库存管理
    public function doWebStock(){
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        if($op == 'list'){

            //查所属店铺
            $user_store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uniacid=".$_W['uniacid']." AND uid=".$_W['user']['uid']);
            if(empty($user_store)){
                message('对不起，你还没有关联到任何的店铺！',$this->createWebUrl('orders', array('op' => 'list')), 'error') ;
            }
            $type = $_GPC['type'];
            if($type == 'under_safe'){
                $under_safe_goods = pdo_fetchall("SELECT * FROM " . tablename('we7car_stock') . " WHERE weid = " . $_W['uniacid'] . " AND store_id =" . $user_store['store_id'] . " AND quantity < safety_sock");
                $ids = array();
                foreach($under_safe_goods as $un){
                    $ids[] = $un['goods_id'];
                }
                $ids = implode(',',$ids);
                $all_goods = pdo_fetchall("SELECT * FROM " . tablename('we7car_goods') . " WHERE weid =" . $_W['uniacid']." AND id in (".$ids.")");
            }else {
                //查已经低于安全库存的产品
                $under_safe = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('we7car_stock') . " WHERE weid = " . $_W['uniacid'] . " AND store_id =" . $user_store['store_id'] . " AND quantity < safety_sock");
                $sql = "SELECT * FROM " . tablename('we7car_goods') . " WHERE weid =" . $_W['uniacid'];
                $total = pdo_fetchcolumn("SELECT COUNt(*) FROM " . tablename('we7car_goods') . " WHERE weid =" . $_W['uniacid']);
                $pager = pagination($total, $pindex, $psize);
                $page_sql = " LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
                $key_word = $_GPC['key_word'];
                $type_id = $_GPC['type_id'];
                if (!empty($key_word)) {
                    $sql .= " AND (name like '%" . $key_word . "%' OR goods_sn like '%" . $key_word . "%')";
                }
                if (!empty($type_id) || $type_id != 0) {
                    $sql .= " AND type_id =" . $type_id;
                }
                $sql .= $page_sql;
                $all_goods = pdo_fetchall($sql);
                //搜索待审核的订单
                $order_count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_stock_log')." WHERE status = 0 AND weid =".$_W['uniacid']." AND store_id =".$user_store['store_id']);


                $types = pdo_fetchall("SELECT * FROM " . tablename('we7car_goods_type') . " WHERE weid =" . $_W['uniacid']);
            }

            $store_id = $user_store['store_id'];
            $list = array();
            foreach($all_goods as $goods){
                $stock = pdo_fetch("SELECT * FROM ".tablename('we7car_stock')." WHERE weid =".$_W['uniacid']." AND store_id =".$store_id." AND goods_id =".$goods['id']);
                if(!empty($stock)){
                    $goods['quantity'] = $stock['quantity'];
                    $goods['safety_sock'] = $stock['safety_sock'];
                    $goods['stock_id'] = $stock['id'];
                }else{
                    $goods['quantity'] = 0;
                    $goods['safety_sock'] = '未设置';
                    $goods['stock_id'] = 0;
                }
                $list[] = $goods;
            }

            include $this->template('web/stock_list');
        }

        //设置安全库存
        if($op == 'save_safety_sock'){
            $stock_id = $_GPC['stock_id'];
            $goods_id = $_GPC['goods_id'];
            $goods = pdo_fetch("SELECT * FROM ".tablename('we7car_goods')." WHERE weid =".$_W['uniacid']." AND id =".$goods_id);
            if(checksubmit('submit')){
                $goods_id = $_GPC['goods_id'];
                $safety_sock = $_GPC['safety_sock'];
                //查所属店铺
                $user_store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uniacid=".$_W['uniacid']." AND uid=".$_W['user']['uid']);
                if(empty($user_store)){
                    message('对不起，你还没有关联到任何的店铺！',$this->createWebUrl('orders', array('op' => 'list')), 'error') ;
                }
                $store_id = $user_store['store_id'];
                //查对应店铺对应产品是否已经存在库存记录
                $find_stock = pdo_fetch("SELECT * FROM ".tablename('we7car_stock')." WHERE store_id =".$store_id." AND goods_id =".$goods_id." AND weid=".$_W['uniacid']);

                $insert = array(
                    'goods_id' => $goods_id,
                    'safety_sock' => $safety_sock,
                    'store_id' => $store_id,
                    'weid' => $_W['uniacid'],
                );
                if(!empty($find_stock)){
                    if (pdo_update('we7car_stock', $insert, array('id' => $find_stock['id'])) === false) {
                        message('更新品牌数据失败, 请稍后重试.', 'error');
                    }
                } else {
                    pdo_insert('we7car_stock', $insert);
                    !pdo_insertid() ? message('保存品牌数据失败, 请稍后重试.', 'error') : '';
                }
                message('更新安全库存成功！', $this->createWebUrl('stock', array('op' => 'list')), 'success');
            }
            include $this->template('web/save_safety_sock');
        }

        //采购入库
        if($op == 'stock_in'){
            $user_store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uniacid=".$_W['uniacid']." AND uid=".$_W['user']['uid']);
            if(empty($user_store)){
                message('对不起，你还没有关联到任何的店铺！',$this->createWebUrl('orders', array('op' => 'list')), 'error') ;
            }
            $goods_id = $_GPC['goods_id'];
            //产品
            $goods = pdo_fetch("SELECT * FROM ".tablename('we7car_goods')." WHERE weid=".$_W['uniacid']." AND id = ".$goods_id);
            if(checksubmit('submit')){
                $store_id = $user_store['store_id'];
                $price = $_GPC['price'];
                $quantity = $_GPC['quantity'];
                $remark = $_GPC['remark'];
                $insert = array(
                    'weid' => $_W['uniacid'],
                    'store_id' => $store_id,
                    'remark' => $remark,
                    'price' => $price,
                    'quantity' => $quantity,
                    'goods_id' => $goods_id,
                    'user_id' => $_W['user']['uid'],
                    'status' => 0,
                    'createtime' => TIMESTAMP,
                    'type' => 2,
                );
                pdo_insert('we7car_stock_log', $insert);
                !pdo_insertid() ? message('采购入库失败, 请稍后重试.', 'error') : '';
                message('更新品牌数据成功！', $this->createWebUrl('stock', array('op' => 'orders')), 'success');
            }
//            $goods = pdo_fetch("SELECT * FROM ".tablename('we7car_goods')." WHERE weid=".$_W['uniacid']." AND ");
            include $this->template('web/stock_in');
        }

        //销售出库
        if($op == 'stock_out'){
            $user_store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uniacid=".$_W['uniacid']." AND uid=".$_W['user']['uid']);
            if(empty($user_store)){
                message('对不起，你还没有关联到任何的店铺！',$this->createWebUrl('orders', array('op' => 'list')), 'error') ;
            }
            $goods_id = $_GPC['goods_id'];
            //产品
            $goods = pdo_fetch("SELECT * FROM ".tablename('we7car_goods')." WHERE weid=".$_W['uniacid']." AND id = ".$goods_id);
            $store_id = $user_store['store_id'];
            $stock = pdo_fetch("SELECT * FROM ".tablename('we7car_stock')." WHERE goods_id =".$goods_id." AND weid =".$_W['uniacid']." AND store_id =".$store_id);
            if($stock['quantity'] == 0 || empty($stock['quantity'])){
                message("库存不足，不能出库！",$this->createWebUrl('stock',array('op' => 'list')), 'error');
            }
            if(checksubmit('submit')){
                $store_id = $user_store['store_id'];
                $price = $_GPC['price'];
                $quantity = $_GPC['quantity'];
                $remark = $_GPC['remark'];
                $insert = array(
                    'weid' => $_W['uniacid'],
                    'store_id' => $store_id,
                    'remark' => $remark,
                    'price' => $price,
                    'quantity' => $quantity,
                    'goods_id' => $goods_id,
                    'user_id' => $_W['user']['uid'],
                    'status' => 0,
                    'createtime' => TIMESTAMP,
                    'type' => 1,
                );
                pdo_insert('we7car_stock_log', $insert);
                !pdo_insertid() ? message('销售出库失败, 请稍后重试.', 'error') : '';
                message('更新品牌数据成功！', $this->createWebUrl('stock', array('op' => 'orders')), 'success');
            }


            include $this->template("web/stock_out");
        }

        if($op == 'goods_detail'){
            $goods_id = $_GPC['goods_id'];
            $goods = pdo_fetch("SELECT g.*,b.name as brand_name,t.name as type_name FROM ".tablename('we7car_goods')." AS g,".tablename('we7car_goods_brand')." as b,".tablename('we7car_goods_type')." as t WHERE g.id=".$goods_id." AND g.weid=".$_W['uniacid']." AND g.goods_brand = b.id AND g.type_id = t.id");
            include $this->template('web/goods_detail');
        }

        //出入库记录
        if($op == 'orders'){
            $user_store = pdo_fetch("SELECT * FROM ".tablename('we7car_store_user')." WHERE uniacid=".$_W['uniacid']." AND uid=".$_W['user']['uid']);
            if(empty($user_store)){
                message('对不起，你还没有关联到任何的店铺！',$this->createWebUrl('orders', array('op' => 'list')), 'error') ;
            }
            $store_id = $user_store['store_id'];

            $sql = "SELECT g.image,g.name,g.spec,s.* FROM ".tablename('we7car_stock_log')." AS s,".tablename('we7car_goods')." AS g WHERE s.store_id =".$store_id." AND s.weid =".$_W['uniacid']." AND g.id = s.goods_id";

            $total = pdo_fetchcolumn("SELECT COUNt(*) FROM ".tablename('we7car_stock_log')." AS s,".tablename('we7car_goods')." AS g WHERE s.store_id =".$store_id." AND s.weid =".$_W['uniacid']." AND g.id = s.goods_id");
            $pager = pagination($total, $pindex, $psize);
            $page_sql = " ORDER BY s.status LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $status = empty($_GPC['status'])? 1 : $_GPC['status'] ;
            $order_count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('we7car_stock_log')." WHERE status = 0 AND weid =".$_W['uniacid']." AND store_id =".$user_store['store_id']);
            if(!empty($status) || $status != 0){
                $sql .= " AND s.status =".($status-1);
            }
            $sql .= $page_sql;
            $list = pdo_fetchall($sql);

            include $this->template('web/stock_orders');
        }


        //审核出入库
        if($op == 'examine'){
            $id = $_GPC['id'];
            $type = $_GPC['type'];
            $insert = array(
                'status' => $type,
            );
            //如果审核通过，直接添加对应的库存数
            if (pdo_update('we7car_stock_log', $insert, array('id' => $id)) === false) {
                message('更新品牌数据失败, 请稍后重试.', 'error');
            }else{
                if($type == 1 ){
                    $stock_log = pdo_fetch("SELECT * FROM ".tablename('we7car_stock_log')." WHERE id =".$id);
                    $stock = pdo_fetch("SELECT * FROM ".tablename('we7car_stock')." WHERE weid =".$stock_log['weid']." AND store_id =".$stock_log['store_id']." AND goods_id =".$stock_log['goods_id']);
                    if(empty($stock)){
                        $insert = array(
                            'weid' => $stock_log['weid'],
                            'store_id' => $stock_log['store_id'],
                            'goods_id' => $stock_log['goods_id'],
                            'quantity' => $stock_log['quantity'],
                        );
                        pdo_insert('we7car_stock', $insert);
                        !pdo_insertid() ? message('保存数据失败, 请稍后重试.', 'error') : '';
                    }else{
                        if($stock_log['type'] == 2) { //入库加库存
                            $insert = array(
                                'quantity' => $stock['quantity'] + $stock_log['quantity'],
                            );
                            if (pdo_update('we7car_stock', $insert, array('id' => $stock['id'])) === false) {
                                message('更新品牌数据失败, 请稍后重试.', 'error');
                            }
                        }else if($stock_log['type'] == 1){ //出库减库存
                            $quantity = $stock['quantity'] - $stock_log['quantity'];
                            if($quantity < 0){
                                message('库存不足，不能出库.', 'error');
                            }
                            $insert = array(
                                'quantity' => $quantity,
                            );
                            if (pdo_update('we7car_stock', $insert, array('id' => $stock['id'])) === false) {
                                message('更新品牌数据失败, 请稍后重试.', 'error');
                            }
                        }
                    }
                }
                message('审核成功！！', $this->createWebUrl('stock', array('op' => 'orders')), 'success');
            }

        }

        //门店添加自己的产品，如果客户输入的产品名称已经存在
        if($op == 'goods_add'){

        }

    }


    //汽车品牌管理
    public function doWebBrand() {
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';

        if ($op == 'list') {
            $list = pdo_fetchall('SELECT * FROM ' . tablename('we7car_brand') . " WHERE `weid` = :weid ORDER BY listorder DESC", array(':weid' => $_W['uniacid']));
            if (checksubmit('submit')) {
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_brand', array('listorder' => intval($val)), array('id' => intval($key)));
                }
                message('更新品牌排序成功！', $this->createWebUrl('brand', array('op' => 'list')), 'success');
            }
            include $this->template('web/brand_list');
        }

        if ($op == 'post') {
            $id = intval($_GPC['id']);
            if ($id > 0) {
                $theone = pdo_fetch('SELECT * FROM ' . tablename('we7car_brand') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $id));
            } else {
                $theone = array('status' => 1, 'listorder' => 0);
            }

            if (checksubmit('submit')) {
                $title = trim($_GPC['title']) ? trim($_GPC['title']) : message('请填写品牌名称！');
                $logo = trim($_GPC['logo']) ? trim($_GPC['logo']) : message('请上传品牌logo！');
                $description = trim($_GPC['description']) ? trim($_GPC['description']) : message('请填写品牌简介！');
                $officialweb = trim($_GPC['officialweb']);
                $listorder = intval($_GPC['listorder']);
                $status = intval($_GPC['status']);
                $insert = array(
                    'title' => $title,
                    'logo' => $logo,
                    'description' => $description,
                    'officialweb' => $officialweb,
                    'listorder' => $listorder,
                    'status' => $status,
                    'weid' => $_W['uniacid'],
                    'createtime' => TIMESTAMP
                );
                if (empty($id)) {
                    pdo_insert('we7car_brand', $insert);
                    !pdo_insertid() ? message('保存品牌数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_brand', $insert, array('id' => $id)) === false) {
                        message('更新品牌数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新品牌数据成功！', $this->createWebUrl('brand', array('op' => 'list')), 'success');
            }
            include $this->template('web/brand_post');
        }

        if ($op == 'del') {
            $id = intval($_GPC['id']);
            $thumb = pdo_fetchcolumn("SELECT logo FROM " . tablename('we7car_brand') . " WHERE id = :id", array(':id' => $id));
            load()->func('file');
            file_delete($thumb);
            $temp = pdo_delete("we7car_brand", array("weid" => $_W['uniacid'], 'id' => $id));
            if ($temp == false) {
                message('抱歉，删除数据失败！', '', 'error');
            } else {
                pdo_delete("we7car_series", array("weid" => $_W['uniacid'], 'bid' => $id));
                pdo_delete("we7car_type", array("weid" => $_W['uniacid'], 'bid' => $id));
                message('删除数据成功！', $this->createWebUrl('brand', array('op' => 'list')), 'success');
            }
        }
    }

    //汽车车系管理
    public function doWebSeries() {
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';

        if ($op == 'list') {
            if (checksubmit('submit')) {
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_series', array('listorder' => intval($val)), array('id' => intval($key)));
                }
                message('更新车系排序成功！', $this->createWebUrl('series', array('op' => 'list')), 'success');
            }
            $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_series') . " WHERE `weid` = :weid  ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
            $series = pdo_fetchall("SELECT * FROM " . tablename('we7car_brand') . " WHERE `weid` = :weid  ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
            $seriesArr = array();
            foreach ($series as $v) {
                $seriesArr[$v['id']] = $v['title'];
            }
            include $this->template('web/series_list');
        }

        if ($op == 'post') {
            $id = intval($_GPC['id']);
            if ($id > 0) {
                $theone = pdo_fetch("SELECT * FROM " . tablename('we7car_series') . " WHERE  `weid` = :weid  AND `id` = :id AND `status` = 1  LIMIT 1", array(':weid' => $_W['uniacid'], ':id' => $id));
            } else {
                $theone = array('status' => 1, 'listorder' => 0);
            }
            $brand = pdo_fetchall("SELECT title,id FROM " . tablename('we7car_brand') . " WHERE `weid` = :weid AND `status` = 1  ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
            if (checksubmit('submit')) {
                $bid = intval($_GPC['bid']) ? intval($_GPC['bid']) : message('请选择所属品牌！');
                $title = trim($_GPC['title']) ? trim($_GPC['title']) : message('请填写车系名称！');
                $subtitle = trim($_GPC['subtitle']) ? trim($_GPC['subtitle']) : message('请填写车系简称！');
                $thumb = trim($_GPC['thumb']) ? trim($_GPC['thumb']) : message('请上传车系图片！');
                $description = trim($_GPC['description']) ? trim($_GPC['description']) : message('请填写品牌简介！');
                $listorder = intval($_GPC['listorder']);
                $status = intval($_GPC['status']);
                $insert = array(
                    'bid' => $bid,
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'thumb' => $thumb,
                    'description' => $description,
                    'listorder' => $listorder,
                    'status' => $status,
                    'weid' => $_W['uniacid'],
                    'createtime' => TIMESTAMP
                );
                if (empty($id)) {
                    pdo_insert('we7car_series', $insert);
                    !pdo_insertid() ? message('保存车系数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_series', $insert, array('id' => $id)) === false) {
                        message('更新车系数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新车系数据成功！', $this->createWebUrl('series', array('op' => 'list')), 'success');
            }
            include $this->template('web/series_post');
        }

        if ($op == 'del') {
            $id = intval($_GPC['id']);
            $temp = pdo_delete("we7car_series", array("weid" => $_W['uniacid'], 'id' => $id));
            if ($temp == false) {
                message('抱歉，删除数据失败！', '', 'error');
            } else {
                pdo_delete("we7car_type", array("weid" => $_W['uniacid'], 'sid' => $id));
                message('删除数据成功！', $this->createWebUrl('series', array('op' => 'list')), 'success');
            }
        }
    }

    //汽车车型
    public function doWebType() {
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';

        if ($op == 'list') {
            $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_type') . " WHERE `weid` = :weid  ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));

            foreach ($list as &$li) {
                $val = pdo_fetch("SELECT id,title FROM " . tablename('we7car_album') . " WHERE `weid` = :weid AND `type_id` = :type_id ORDER BY `displayorder` DESC", array(':weid' => $_W['uniacid'], ':type_id' => $li['id']));

                if (!empty($val)) {
                    $li['album_id'] = $val['id'];
                    $li['album_title'] = $val['title'];
                }
            }

            $brandArr = pdo_fetchall("SELECT id,title FROM " . tablename('we7car_brand') . " WHERE `weid` = :weid   ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
            foreach ($brandArr as $brand) {
                $branddata[$brand['id']] = $brand;
            }
            $seriesArr = pdo_fetchall("SELECT id,title FROM " . tablename('we7car_series') . " WHERE  `weid` = :weid ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
            foreach ($seriesArr as $series) {
                $seriesdata[$series['id']] = $series;
            }
            include $this->template('web/type_list');
        }

        if ($op == 'post') {
            $id = intval($_GPC['id']);
            if ($id > 0) {
                $theone = pdo_fetch("SELECT * FROM " . tablename('we7car_type') . " WHERE  `weid` = :weid  AND `id` = :id", array(':weid' => $_W['uniacid'], ':id' => $id));
                if (!empty($theone['thumbArr'])) {
                    $theone['thumb_url'] = explode('|', $theone['thumbArr']);
                }
                $series = pdo_fetchall("SELECT title,id FROM " . tablename('we7car_series') . " WHERE  `weid` = :weid  AND `bid` = :bid  ", array(':weid' => $_W['uniacid'], ':bid' => $theone['bid']));
            }
            if (empty($theone)) {
                $theone = array(
                    'listorder' => 0,
                    'status' => 1,
                );
            }
            $brand = pdo_fetchall("SELECT id,title FROM " . tablename('we7car_brand') . " WHERE `weid` = :weid  ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
            $seriesArr = pdo_fetchall("SELECT id,bid,title FROM " . tablename('we7car_series') . " WHERE  `weid` = :weid  ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
            if (checksubmit('submit')) {
                $title = trim($_GPC['title']) ? trim($_GPC['title']) : message('请填写车型名称！');
                $bid = intval($_GPC['bid']) ? intval($_GPC['bid']) : message('请选择所属品牌！');
                $sid = intval($_GPC['sid']) ? intval($_GPC['sid']) : message('请选择所属车系！');
                $pyear = trim($_GPC['pyear']) ? trim($_GPC['pyear']) : message('请选择年款！');
                $price1 = trim($_GPC['price1']) ? trim($_GPC['price1']) : message('请填写车型指导价！');
                $price2 = trim($_GPC['price2']) ? trim($_GPC['price2']) : message('请填写车型经销商价！');
                $output = trim($_GPC['output']) ? trim($_GPC['output']) : message('请填写车型排量！');
                $gearnum = trim($_GPC['gearnum']) ? trim($_GPC['gearnum']) : message('请填写车型档位个数！');
                $gear_box = trim($_GPC['gear_box']) ? trim($_GPC['gear_box']) : message('请填写车型档位箱！');
                $thumb = $_GPC['thumb_url'] ? $_GPC['thumb_url'] : message('请上传车型图片！');
                $listorder = intval($_GPC['listorder']);
                $status = intval($_GPC['status']);
                $insert = array(
                    'bid' => $bid,
                    'sid' => $sid,
                    'title' => $title,
                    'pyear' => $pyear,
                    'price1' => $price1,
                    'price2' => $price2,
                    'output' => $output,
                    'gearnum' => $gearnum,
                    'gear_box' => $gear_box,
                    'output' => $output,
                    'xiangceid' => intval($_GPC['xiangceid']),
                    'listorder' => $listorder,
                    'status' => $status,
                    'weid' => $_W['uniacid'],
                    'description' => $_GPC['description'],
                    'createtime' => TIMESTAMP
                );
                if (!empty($_GPC['thumb_url'])) {
                    $insert['thumb'] = $_GPC['thumb_url'][0];
                    $insert['thumbArr'] = implode('|', $_GPC['thumb_url']);
                }
                if (empty($id)) {
                    pdo_insert('we7car_type', $insert);
                    !pdo_insertid() ? message('保存车型数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_type', $insert, array('id' => $id)) === false) {
                        message('更新车型数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新车型数据成功！', $this->createWebUrl('type', array('op' => 'list')), 'success');
            }
            include $this->template('web/type_post');
        }

        if ($op == 'getseries') {
            $bid = intval($_GPC['bid']);
            if ($bid) {
                $series = pdo_fetchall("SELECT id,bid,title FROM " . tablename('we7car_series') . " WHERE `weid` = :weid  AND `bid` = :bid ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid'], ':bid' => $bid));
            }
            $html = "<option value='0'>请选择车系</option>";
            foreach ($series as $val) {
                $html.="<option value='{$val['id']}'>{$val['title']}</option>";
            }
            exit($html);
        }

        if ($op == 'del') {
            $id = intval($_GPC['id']);
            $thumb = pdo_fetch("SELECT thumb,thumbArr FROM " . tablename('we7car_type') . " WHERE id = :id", array(':id' => $id));
            load()->func('file');file_delete($thumb['thumb']);
            $thumbarr = explode('|', $thumb['thumbArr']);
            foreach ($thumbarr as $list) {
                file_delete($list);
            }
            $temp = pdo_delete("we7car_type", array("weid" => $_W['uniacid'], 'id' => $id));
            if ($temp == false) {
                message('抱歉，删除数据失败！', '', 'error');
            } else {
                message('删除数据成功！', $this->createWebUrl('series', array('op' => 'list')), 'success');
            }
        }
    }

    //客服管理
    public function doWebKefu() {
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';
        $id = intval($_GPC['id']);
        if ($op == 'list') {
            $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_services') . " WHERE `weid` = :weid ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
            if (checksubmit('submit')) {
                foreach ($_GPC['listorder'] as $key => $val) {
                    pdo_update('we7car_services', array('listorder' => intval($val)), array('id' => intval($key)));
                }
                message('更新客服排序成功！', $this->createWebUrl('kefu', array('op' => 'list')), 'success');
            }
            include $this->template('web/kefu_list');
        }

        if ($op == 'post') {
            if ($id) {
                $theone = pdo_fetch("SELECT * FROM " . tablename('we7car_services') . " WHERE `weid` = :weid AND `id` = :id ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid'], ':id' => $id));
            } else {
                $theone = array('status' => 1, 'listorder' => 0);
            }
            if (checksubmit('submit')) {
                $kefuname = trim($_GPC['kefuname']) ? trim($_GPC['kefuname']) : message('请填写客服姓名！');
                $headthumb = trim($_GPC['headthumb']) ? trim($_GPC['headthumb']) : message('请上传客服图片！');
                $kefutel = trim($_GPC['kefutel']) ? trim($_GPC['kefutel']) : message('请填写客服电话！');
                (!intval($_GPC['pre_sales']) && !intval($_GPC['aft_sales'])) ? message('请选择客服类型！') : '';
                $description = trim($_GPC['description']) ? trim($_GPC['description']) : message('请填写客服简介！');
                $status = intval($_GPC['status']);
                $listorder = intval($_GPC['listorder']);
                $pre_sales = intval($_GPC['pre_sales']);
                $aft_sales = intval($_GPC['aft_sales']);

                $insert = array(
                    'weid' => $_W['uniacid'],
                    'kefuname' => $kefuname,
                    'headthumb' => $headthumb,
                    'kefutel' => $kefutel,
                    'description' => $description,
                    'status' => $status,
                    'listorder' => $listorder,
                    'pre_sales' => $pre_sales,
                    'aft_sales' => $aft_sales,
                );
                if (empty($id)) {
                    pdo_insert('we7car_services', $insert);
                    !pdo_insertid() ? message('保存客服数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_services', $insert, array('id' => $id)) === false) {
                        message('更新客服数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新客服数据成功！', $this->createWebUrl('kefu', array('op' => 'list')), 'success');
            }
            include $this->template('web/kefu_post');
        }
        if ($op == 'del') {
            $kefu = pdo_fetch("SELECT headthumb FROM " . tablename('we7car_services') . " WHERE `weid` = :weid AND `id` = :id LIMIT 1", array(':weid' => $_W['uniacid'], ':id' => $id));
            if (!empty($kefu['headthumb'])) {
                load()->func('file');file_delete($kefu['headthumb']);
            }
            unset($kefu);
            $temp = pdo_delete("we7car_services", array("weid" => $_W['uniacid'], 'id' => $id));
            if ($temp == false) {
                message('抱歉，删除数据失败！', '', 'error');
            } else {
                message('删除数据成功！', $this->createWebUrl('kefu', array('op' => 'list')), 'success');
            }
        }
    }

    //预约试驾
    public function doWebYuyue() {
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'list';
        load()->func('tpl');

        if ($op == 'post') {
            $id = intval($_GPC['id']); //预约id
            if ($id) {
                $item = pdo_fetch("SELECT * FROM " . tablename('we7car_order_set') . " WHERE `weid` = :weid AND `id` = :id LIMIT 1", array(':weid' => $_W['uniacid'], ':id' => $id));
            } else {
                $item = array('isshow' => 1, 'yytype' => 1, 'pertotal' => 1, 'start_time' => TIMESTAMP, 'end_time' => TIMESTAMP + 86400 * 7);
            }
            if ($item) {
                $sql = 'SELECT * FROM ' . tablename('we7car_order_fields') . ' WHERE `sid` = :id ORDER BY fid ASC';
                $params = array();
                $params[':id'] = $id;
                $ds = pdo_fetchall($sql, $params);
            }

            if (checksubmit('submit')) {
                $title = trim($_GPC['title']) ? trim($_GPC['title']) : message('请填写预约标题！');
                $description = trim($_GPC['description']) ? trim($_GPC['description']) : message('请填写预约说明！');
                $mobile = trim($_GPC['mobile']) ? trim($_GPC['mobile']) : message('请填写预约电话！');
                $topbanner = trim($_GPC['topbanner']) ? trim($_GPC['topbanner']) : message('请上传订单头部图片！');
                $address = trim($_GPC['address']) ? trim($_GPC['address']) : message('请填写预约地址！');
                $isshow = intval($_GPC['isshow']);
                $yytype = intval($_GPC['yytype']);
                $location_x = trim($_GPC['baidumap']['lng']);
                $location_y = trim($_GPC['baidumap']['lat']);
                $starttime = empty($_GPC['starttime']) ? TIMESTAMP : strtotime($_GPC['starttime']);
                $endtime = empty($_GPC['endtime']) ? TIMESTAMP : strtotime($_GPC['endtime']);
                $insert = array(
                    'weid' => $_W['uniacid'],
                    'title' => $title,
                    'yytype' => $yytype,
                    'description' => $description,
                    'start_time' => $starttime,
                    'end_time' => $endtime,
                    'address' => $address,
                    'mobile' => $mobile,
                    'location_x' => $location_x,
                    'location_y' => $location_y,
                    'topbanner' => $topbanner,
                    'note' => trim($_GPC['note']),
                    'pertotal' => intval($_GPC['pertotal']),
                    'createtime' => TIMESTAMP,
                    'isshow' => intval($_GPC['isshow'])
                );
                //开启某个预约后，同类型的预约将被系统自动关闭
                if ($isshow == '1') {
                    pdo_update('we7car_order_set', array('isshow' => 0), array('weid' => $_W['uniacid'], 'yytype' => $yytype));
                }

                if (empty($id)) {
                    pdo_insert('we7car_order_set', $insert);
                    !($id = pdo_insertid()) ? message('保存预约数据失败, 请稍后重试.', '', 'error') : '';
                    //自定义字段
                    if ($_GPC['titles']) {
                        $sql = 'DELETE FROM ' . tablename('we7car_order_fields') . ' WHERE `sid`=:sid';
                        $params = array();
                        $params[':sid'] = $id;
                        pdo_query($sql, $params);
                        foreach ($_GPC['titles'] as $k => $v) {
                            $field = array();
                            $field['sid'] = $id;
                            $field['title'] = trim($v);
                            $field['type'] = $_GPC['types'][$k];
                            $field['value'] = trim($_GPC['values'][$k]);
                            $field['value'] = urldecode($field['value']);
                            pdo_insert('we7car_order_fields', $field);
                        }
                    }
                } else {
                    if (pdo_update('we7car_order_set', $insert, array('id' => $id)) === false) {
                        message('更新预约数据失败, 请稍后重试.', '', 'error');
                    }
                    //自定义字段
                    if ($_GPC['titles']) {
                        foreach ($_GPC['titles'] as $k => $v) {
                            $field = array();
                            $field['sid'] = $id;
                            $field['title'] = trim($v);
                            $field['type'] = $_GPC['types'][$k];
                            $field['value'] = trim($_GPC['values'][$k]);
                            $field['value'] = urldecode($field['value']);
                            pdo_update('we7car_order_fields', $field, array('fid' => $k));
                        }
                    }
                }
                message('更新预约数据成功！', $this->createWebUrl('yuyue', array('op' => 'list')), 'success');
            }
            include $this->template('web/yuyue_post');
        }

        if ($op == 'list') {
            $sql = 'SELECT * FROM ' . tablename('we7car_order_set') . ' WHERE `weid` = :weid ORDER BY `isshow` DESC,
                    `id` DESC';
            $list = pdo_fetchall($sql, array(':weid' => $_W['uniacid']));
            include $this->template('web/yuyue_list');
        }

        if ($op == 'show') {
            $sid = intval($_GPC['id']);
            $pindex = max(1, intval($_GPC['page']));
            $psize = 50;
            if (intval($_GPC['so']) == 1) {
                $ystarttime = empty($_GPC['date']['start']) ? strtotime('-1 month') : strtotime($_GPC['date']['start']);
                $yendtime = empty($_GPC['date']['end']) ? TIMESTAMP : strtotime($_GPC['date']['end']) + 86399;
                $starttime = empty($_GPC['date1']['start']) ? strtotime('-1 month') : strtotime($_GPC['date1']['start']);
                $endtime = empty($_GPC['date1']['end']) ? TIMESTAMP : strtotime($_GPC['date1']['end']) + 86399;
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('we7car_order_list') . " WHERE sid = :sid AND `dateline` > {$ystarttime} AND `dateline` <{$yendtime} AND `createtime` > {$starttime} AND `createtime` <{$endtime}", array(':sid' => $sid));
                $pager = pagination($total, $pindex, $psize);
                $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_order_list') . "  WHERE `sid` = :sid AND `dateline` > {$ystarttime} AND `dateline` <{$yendtime} AND `createtime` > {$starttime} AND `createtime` <{$endtime} ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':sid' => $sid));
            } else {
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('we7car_order_list') . " WHERE sid = :sid", array(':sid' => $sid));
                $pager = pagination($total, $pindex, $psize);
                $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_order_list') . "  WHERE `sid` = :sid ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':sid' => $sid));
                $ystarttime = empty($_GPC['date']['start']) ? strtotime('-1 month') : strtotime($_GPC['date']['start']);
                $yendtime = empty($_GPC['date']['end']) ? TIMESTAMP : strtotime($_GPC['date']['end']) + 86399;
                $starttime = empty($_GPC['date1']['start']) ? strtotime('-1 month') : strtotime($_GPC['date1']['start']);
                $endtime = empty($_GPC['date1']['end']) ? TIMESTAMP : strtotime($_GPC['date1']['end']) + 86399;
            }

            if (!empty($_GPC['export'])) {
                /* 输入到CSV文件 */
                $html = "\xEF\xBB\xBF";

                /* 输出表头 */
                $filter = array(
                    'from_user' => '用户',
                    'username' => '真实姓名',
                    'mobile' => '电话',
                    'brand_cn' => '品牌',
                    'serie_cn' => '车系',
                    'type_cn' => '车型',
                    'dateline' => '试车时间',
                    'createtime' => '提交时间',
                    'note' => '备注'
                );

                foreach ($filter as $key => $value) {
                    $html .= $value . "\t,";
                }
                $html .= "\n";
                $key_array = array_keys($filter);

                foreach ($list as $k => $v) {
                    foreach ($v as $k1 => $v1) {
                        if (in_array($k1, $key_array)) {
                            if ($k1 == 'createtime' || $k1 == 'dateline'){
                                $html .= date('Y-m-d H:i:s', $v1) . "\t ,";
                            } else {
                                $html .= $v1 . "\t,";
                            }
                        }
                    }
                    $html .= "\n";
                }

                /* 输出CSV文件 */
                header("Content-type:text/csv");
                header("Content-Disposition:attachment; filename=全部数据.csv");
                echo $html;
                exit();

            }

            include $this->template('web/yuyue_show');
        }

        if ($op == 'del') {
            $id = intval($_GPC['id']);
            $sid = intval($_GPC['sid']);
            $temp = pdo_delete("we7car_order_list", array('id' => $id));
            if ($temp == false) {
                message('抱歉，删除订单数据失败！', '', 'error');
            } else {
                pdo_delete("we7car_order_data", array('srid' => $id)); //删除自定义数据
                message('删除数据成功！', $this->createWebUrl('yuyue', array('op' => 'show', 'id' => $sid, 'so' => 0)), 'success');
            }
        }

        if ($op == 'showdetail') {
            $id = intval($_GPC['id']);
            $orderone = pdo_fetch("SELECT * FROM" . tablename('we7car_order_list') . " WHERE `id` = :id LIMIT 1", array(':id' => $id));
            //获取自定义字段
            $fields = pdo_fetchall("SELECT * FROM " . tablename('we7car_order_fields') . " WHERE `sid` = :sid ORDER BY `fid` ASC", array(':sid' => $orderone['sid']));
            if (!empty($fields)) {
                //获取自定义字段的数据
                $fieldsdata = pdo_fetchall("SELECT * FROM " . tablename('we7car_order_data') . " WHERE `sid` = :sid AND `srid` = :srid ", array(':sid' => $orderone['sid'], ':srid' => $id));
                if ($fieldsdata) {
                    foreach ($fieldsdata as $fielddata) {
                        $orderone['data'][$fielddata['sfid']] = $fielddata['data'];
                    }
                }
            }
            //获取预约的信息
            $reply = pdo_fetch("SELECT * FROM" . tablename('we7car_order_set') . " WHERE `id` = :id AND `weid` = :weid LIMIT 1", array(':id' => $orderone['sid'], ':weid' => $_W['uniacid']));
            include $this->template('web/yuyue_showdetail');
        }

        if ($op == 'status') {
            $id = intval($_GPC['id']);
            pdo_update('we7car_order_list', array('status' => intval($_GPC['status'])), array('id' => $id));
            message('更新订单状态成功.', $this->createWebUrl('yuyue', array('op' => 'show', 'id' => intval($_GPC['sid']))), 'success');
        }

        if ($op == 'yuyuedel') {
            $id = intval($_GPC['id']);
            $thumb = pdo_fetchcolumn("SELECT topbanner FROM " . tablename('we7car_order_set') . " WHERE id = :id", array(':id' => $id));
            load()->func('file');file_delete($thumb);
            $temp = pdo_delete("we7car_order_set", array('id' => $id, 'weid' => $_W['uniacid']));
            if ($temp == false) {
                message('抱歉，删除预约数据失败！', '', 'error');
            } else {
                pdo_delete("we7car_order_fields", array('sid' => $id));
                pdo_delete("we7car_order_data", array('sid' => $id));
                pdo_delete("we7car_order_list", array('sid' => $id));
                message('删除预约成功！', $this->createWebUrl('yuyue', array('op' => 'list')), 'success');
            }
        }
    }

    // 车系列
    public function doMobileSeries() {
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'brand';
        $main_off = 1;
        $bid = intval($_GPC['bid']);
        $company = pdo_fetch('SELECT * FROM ' . tablename('we7car_set') . " WHERE  weid = :weid LIMIT 1", array(':weid' => $_W['uniacid']));

        if (!$bid) {
            $brand = pdo_fetchall('SELECT * FROM ' . tablename('we7car_brand') . " WHERE  weid = :weid AND `status` = 1 ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
        } else {
            $brand = pdo_fetch('SELECT * FROM ' . tablename('we7car_brand') . " WHERE  weid = :weid  AND id = :id", array(':weid' => $_W['uniacid'], ':id' => $bid));
        }
        // 设置分享信息
        $shareDesc = $shareTitle = $title = '全部车型';
        $shareThumb = tomedia($company['shop_logo']);

        if ($op == 'series') {
            if ($bid) {
                $series = pdo_fetchall("SELECT * FROM " . tablename('we7car_series') . " WHERE weid = :weid AND `status` = 1 AND bid = :bid ORDER BY listorder DESC", array(':weid' => $_W['uniacid'], ':bid' => $bid));
            } else {
                $series = pdo_fetchall("SELECT * FROM " . tablename('we7car_series') . " WHERE weid = :weid AND `status` = 1  ORDER BY listorder DESC", array(':weid' => $_W['uniacid']));
            }

            // 设置分享信息
            $shareTitle = $title = $brand['title'];
            $shareDesc = $brand['description'];
            $shareThumb = tomedia($brand['logo']);
        }

        if ($op == 'type') {
            $sid = intval($_GPC['sid']);
            $serieone = pdo_fetch("SELECT * FROM " . tablename('we7car_series') . " WHERE `weid` = :weid AND `id` = :sid ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid'], ':sid' => $sid));
            $types = pdo_fetchall("SELECT * FROM " . tablename('we7car_type') . " WHERE `weid` = :weid AND `status` = 1  AND `sid` = :sid ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid'], ':sid' => $sid));

            // 设置分享信息
            $shareTitle = $title = $serieone['title'];
            $shareDesc = $serieone['description'];
            $shareThumb = tomedia($serieone['thumb']);
        }

        if ($op == 'typedetail') {
            $id = intval($_GPC['id']);
            $typeone = pdo_fetch("SELECT * FROM " . tablename('we7car_type') . " WHERE `weid` = :weid AND `id` = :id ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid'], ':id' => $id));
            $album_id = pdo_fetch("SELECT id FROM " . tablename('we7car_album') . " WHERE `weid` = :weid AND `type_id` = :type_id", array(':weid' => $_W['uniacid'], ':type_id' => $id));
            if (!empty($album_id)) {
                $typeone['album_id'] = $album_id['id'];
            }
            if (!empty($typeone['thumbArr'])) {
                $typeone['thumb_url'] = explode('|', $typeone['thumbArr']);
            }

            // 设置分享信息
            $shareTitle = $title = $typeone['title'];
            $shareDesc = $typeone['description'];
            $shareThumb = tomedia($typeone['thumb']);
        }

        include $this->template('series_series');
    }

    //联系客服
    public function doMobileKefu() {
        global $_GPC, $_W;
        $op = $_GPC['op'] ? $_GPC['op'] : 'series';
        $list1 = pdo_fetchall("SELECT * FROM " . tablename('we7car_services') . " WHERE `weid` = :weid AND `status` = 1  AND `pre_sales` = 1 ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
        $list2 = pdo_fetchall("SELECT * FROM " . tablename('we7car_services') . " WHERE `weid` = :weid AND `status` = 1  AND `aft_sales` = 1 ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid']));
        $thumbs = array();
        if (!empty($list1)) {
            foreach ($list1 as $list) {
                $thumbs[] = tomedia($list['headthumb']);
            }
        }
        if (!empty($list2)) {
            foreach ($list2 as $list) {
                $thumbs[] = tomedia($list['headthumb']);
            }
        }

        // 设置分享信息
        $shareDesc = $shareTitle = $title = '联系销售';
        $shareThumb = $thumbs[mt_rand(0, count($thumbs) - 1)];

        include $this->template('kefu_index');
    }

    /**
     * 试驾预约/预约保养
    */
    public function doMobileYuyue() {
        global $_GPC, $_W;
        $op = trim($_GPC['op']);

        $where = ' WHERE `weid` = :weid';
        $params = array(':weid' => $_W['uniacid']);

        // 获取汽车品牌
        $sql = 'SELECT * FROM ' . tablename('we7car_brand') . $where . ' AND `status` = :status ORDER BY `listorder` DESC';
        $params[':status'] = 1;
        $brands = pdo_fetchall($sql, $params);

        // 获取汽车车系
        $sql = 'SELECT `id`, `title` FROM ' . tablename('we7car_series') . $where . ' AND `status` = :status ORDER BY
                `listorder` DESC';
        $eseries = pdo_fetchall($sql, $params);

        // 获取汽车车型
        $sql = 'SELECT `id`, `title` FROM ' . tablename('we7car_type') . $where . ' AND `status` = :status ORDER BY
                `listorder` DESC';
        $etypes = pdo_fetchall($sql, $params);

        if ($op == 'getseries') {
            $bid = intval($_GPC['bid']);
            $ty = trim($_GPC['ty']);
            if ($bid) {
                if ($ty == 'series') {
                    $datas = pdo_fetchall("SELECT id,bid,title FROM " . tablename('we7car_series') . " WHERE `weid` = :weid AND `status` = 1 AND `bid` = :bid ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid'], ':bid' => $bid));
                    $html = "<option value='0'>请选择车系</option>";
                    foreach ($datas as $val) {
                        $val['val'] = $val['id'] . '=' . $val['title'];
                        $html.="<option value='{$val['val']}'>{$val['title']}</option>";
                    }
                } elseif ($ty == 'types') {
                    $datas = pdo_fetchall("SELECT id,title FROM " . tablename('we7car_type') . " WHERE `weid` = :weid AND `status` = 1 AND `sid` = :bid ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid'], ':bid' => $bid));
                    $html = "<option value='0'>请选择车型</option>";
                    foreach ($datas as $val) {
                        $val['val'] = $val['id'] . '=' . $val['title'];
                        $html.="<option value='{$val['val']}'>{$val['title']}</option>";
                    }
                }
            }
            exit($html);
        }

        $lid = intval($_GPC['id']); //订单id
        $yytype = intval($_GPC['yytype']) ? intval($_GPC['yytype']) : intval($_GPC['__state']); //预约类型

        // 获取预约信息
        $sql = 'SELECT * FROM ' . tablename('we7car_order_set') . $where . ' AND `yytype` = :yytype AND `isshow` =
                :status ORDER BY `id` DESC';
        $params[':yytype'] = $yytype;
        $reply = pdo_fetch($sql, $params);

        if (!$reply) {
            message('抱歉，暂无预约信息.');
        }
        if ($reply['start_time'] > TIMESTAMP) {
            message('当前预约活动还未开始！');
        }
        if ($reply['end_time'] < TIMESTAMP) {
            message('当前预约活动已经结束！');
        }

        // 设置分享信息
        $shareTitle = $title = $reply['title'];
        $shareDesc = $reply['description'];
        $shareThumb = tomedia($reply['topbanner']);

        // 获取预约的自定义字段
        if (!empty($reply)) {
            $sql = 'SELECT * FROM ' . tablename('we7car_order_fields') . ' WHERE `sid` = :sid ORDER BY fid ASC';
            $ds = pdo_fetchall($sql, array(':sid' => $reply['id']));
        }
        if (!empty($ds)) {
            foreach ($ds as &$d) {
                if ($d['type'] == 'select') {
                    $d['option'] = explode('|', $d['value']);
                }
            }
            foreach ($ds as $r) {
                $fields[$r['fid']] = $r;
            }
        }

        // 获取某用户的预约次数
        $sql = 'SELECT COUNT(*) FROM ' . tablename('we7car_order_list') . ' WHERE `sid` = :sid AND `from_user` =
                :openid AND `yytype` = :yytype';
        $pertotal = pdo_fetchcolumn($sql, array(':sid' => $reply['id'], ':openid' => $_W['fans']['from_user'], ':yytype' => $yytype));
        if ($pertotal >= $reply['pertotal'] && $reply['pertotal'] != 0) {
            $pererror = 1;
        }

        if ($lid) {
            //得到某个订单
            $order = pdo_fetch("SELECT * FROM " . tablename('we7car_order_list') . " WHERE `id` = :id  AND `yytype` = :yytype LIMIT 1", array(':id' => $lid, ':yytype' => $yytype));
            $order['brand_val'] = $order['brand'] . '=' . $order['brand_cn'];
            $order['series_val'] = $order['serie'] . '=' . $order['serie_cn'];
            $order['type_val'] = $order['type'] . '=' . $order['type_cn'];
            $order['dateline'] = $order['dateline'] ? date('Y-m-d', $order['dateline']) : date('Y-m-d');
            //初始化车系和车型
            $eseries = pdo_fetchall('SELECT id,title FROM ' . tablename('we7car_series') . " WHERE `weid` = :weid AND `bid` = :bid AND `status` = 1 ORDER BY listorder DESC", array(':weid' => $_W['uniacid'], ':bid' => $order['brand']));
            $etypes = pdo_fetchall('SELECT id,title FROM ' . tablename('we7car_type') . " WHERE `weid` = :weid AND `sid` = :sid AND `status` = 1 ORDER BY listorder DESC", array(':weid' => $_W['uniacid'], ':sid' => $order['serie']));

            if (!empty($ds)) {
                //如果有自定义字段
                $fieldsdata = pdo_fetchall("SELECT * FROM " . tablename('we7car_order_data') . " WHERE `srid` = :srid ", array(':srid' => $lid));
                if ($fieldsdata) {
                    foreach ($fieldsdata as $fielddata) {
                        $order['data'][$fielddata['sfid']] = $fielddata['data'];
                    }
                }
            }
        } else {
            $order['dateline'] = date('Y-m-d');
        }

        // 自动获取车主信息
        $sql = 'SELECT * FROM ' . tablename('we7car_care') . ' WHERE `weid` = :weid AND `from_user` = :from_user';
        $carOwnerInfo = pdo_fetch($sql, array(':weid' => $_W['uniacid'], ':from_user' => $_W['openid']));

        if (checksubmit('submit')) {
            $sid = intval($reply['id']);
            if ($pererror == 1 && !$lid) {
                message("没人可预约{$reply['pertotal']}次.");
            }
            if (!$sid) {
                message('预约信息获取失败.');
            }
            $barr = explode('=', trim($_GPC['brand']));
            $sarr = explode('=', trim($_GPC['serie']));
            $tarr = explode('=', trim($_GPC['types']));
            $insert = array(
                'sid' => $sid,
                'from_user' => $_W['fans']['from_user'],
                'username' => trim($_GPC['realname']),
                'mobile' => trim($_GPC['tel']),
                'dateline' => strtotime($_GPC['dateline']),
                'yytype' => intval($_GPC['yytype']),
                'brand' => $barr[0],
                'brand_cn' => $barr[1],
                'serie' => $sarr[0],
                'serie_cn' => $sarr[1],
                'type' => $tarr[0],
                'type_cn' => $tarr[1],
                'note' => trim($_GPC['note']),
                'createtime' => TIMESTAMP
            );
            foreach ($_GPC as $key => $value) {
                if (strexists($key, 'field_')) {
                    $sfid = intval(str_replace('field_', '', $key));
                    $field = $fields[$sfid];
                    if ($sfid && $field) {
                        $entry = array();
                        $entry['sid'] = $sid;
                        $entry['srid'] = 0;
                        $entry['sfid'] = $sfid;
                        $entry['createtime'] = TIMESTAMP;
                        $entry['data'] = strval($value);
                        $datas[] = $entry;
                    }
                }
            }
            if (!$lid) {
                if (pdo_insert('we7car_order_list', $insert) != 1) {
                    message('保存失败.');
                }
                $rid = pdo_insertid();
                if (empty($rid)) {
                    message('保存失败.');
                }
                if (!empty($datas)) {
                    foreach ($datas as &$r) {
                        $r['srid'] = $rid;
                        pdo_insert('we7car_order_data', $r);
                    }
                }
            } else {
                if (pdo_update('we7car_order_list', $insert, array('id' => $lid)) != 1) {
                    message('更新订单失败.');
                }
                if (!empty($datas)) {
                    foreach ($datas as &$r) {
                        $r['srid'] = $lid;
                        pdo_update('we7car_order_data', $r, array('sfid' => $r['sfid'], 'srid' => $lid));
                    }
                }
            }
            message('预约成功', $this->createMobileUrl('mybook', array('yytype' => $insert['yytype'])), 'success');
        }
        include $this->template('yuyue');
    }

    public function doMobileMybook() {
        global $_GPC, $_W;
        $op = trim($_GPC['op']);
        $yytype = intval($_GPC['yytype']);
        $reply = pdo_fetch("SELECT * FROM " . tablename('we7car_order_set') . " WHERE `weid` = :weid AND `yytype` = :yytype AND `isshow` = 1 ORDER BY `id` DESC LIMIT 1", array(':weid' => $_W['uniacid'], ':yytype' => $yytype));
        $results = pdo_fetchall("SELECT * FROM " . tablename('we7car_order_list') . " WHERE `from_user` = :from_user AND `sid` = :sid ORDER BY `createtime` DESC", array(':sid' => $reply['id'], ':from_user' => $_W['fans']['from_user']));
        //获取自定义字段
        $fields = pdo_fetchall("SELECT * FROM " . tablename('we7car_order_fields') . " WHERE `sid` = :sid ORDER BY `fid` ASC", array(':sid' => $reply['id']));

        if (!empty($fields)) {
            foreach ($results as &$result) {
                //获取自定义字段的数据
                $fieldsdata = pdo_fetchall("SELECT * FROM " . tablename('we7car_order_data') . " WHERE `sid` = :sid AND `srid` = :srid ", array(':sid' => $reply['id'], ':srid' => $result['id']));
                if ($fieldsdata) {
                    foreach ($fieldsdata as $fielddata) {
                        $result['data'][$fielddata['sfid']] = $fielddata['data'];
                    }
                }
            }
        }

        $id = intval($_GPC['id']);
        if ($op == 'del' && $id) {
            $temp = pdo_delete("we7car_order_list", array('id' => $id, 'sid' => intval($_GPC['sid'])));
            if ($temp == false) {
                message('抱歉，删除数据失败！', $this->createMobileUrl('mybook', array('yytype' => $yytype)), 'error');
            } else {
                pdo_delete("we7car_order_data", array('srid' => $id)); //删除自定义数据
                message('删除数据成功！', $this->createMobileUrl('mybook', array('yytype' => $yytype)), 'success');
            }
        }

        // 设置分享信息
        $shareTitle = $title = $reply['title'];
        $shareDesc = $reply['description'];
        $shareThumb = tomedia($reply['topbanner']);

        include $this->template('yuyue_mybook');
    }

    public function doWebGuanhuai() {
        global $_GPC, $_W;
        $op = trim($_GPC['op']) ? trim($_GPC['op']) : 'set';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 50;
        if ($op == 'save') {
            
            $insert = array(
                'weid' => $_W['uniacid'],
                'guanhuai_thumb' => $_GPC['guanhuai_thumb'],
                'create_time' => TIMESTAMP,
            );
            $temp = pdo_update('we7car_set', $insert, array('weid' => $_W['uniacid']));
            if ($temp == false) {
                $temp = pdo_insert('we7car_set', $insert);
            }
            if ($temp == false) {
                message('抱歉，操作数据失败！', '', 'error');
            } else {
                message('更新设置数据成功！', $this->createWebUrl('guanhuai', array('op' => 'set', 'weid' => $_W['uniacid'])), 'success');
            }
        }
        if ($op == 'set') {
            $row = pdo_fetch("SELECT guanhuai_thumb FROM " . tablename('we7car_set') . " WHERE  weid = :weid  ", array(':weid' => $_W['uniacid']));
            include $this->template('web/guanhuai_set');
        }
        if ($op == 'car') {
            if (intval($_GPC['so']) == 1) {
                $starttime = empty($_GPC['date']['start']) ? strtotime('-1 month') : strtotime($_GPC['date']['start']);
                $endtime = empty($_GPC['date']['end']) ? TIMESTAMP : strtotime($_GPC['date']['end']) + 86399;
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('we7car_care') . " WHERE weid = :weid  AND `createtime` > {$starttime} AND `createtime` < {$endtime}", array(':weid' => $_W['uniacid']));
                $pager = pagination($total, $pindex, $psize);
                $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_care') . "  WHERE `weid` = :weid  AND `createtime` > {$starttime} AND `createtime` < {$endtime} ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $_W['uniacid']));
            } else {
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('we7car_care') . " WHERE `weid` = :weid", array(':weid' => $_W['uniacid']));
                $pager = pagination($total, $pindex, $psize);
                $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_care') . "  WHERE `weid` = :weid ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $_W['uniacid']));
                $starttime = empty($_GPC['date']['start']) ? strtotime('-1 month') : strtotime($_GPC['date']['start']);
                $endtime = empty($_GPC['date']['end']) ? TIMESTAMP : strtotime($_GPC['date']['end']) + 86399;
            }
            include $this->template('web/car_show');
        }
        if ($op == 'del') {
            $id = intval($_GPC['id']);
            $thumb = pdo_fetchcolumn("SELECT car_photo FROM " . tablename('we7car_care') . " WHERE id = :id", array(':id' => $id));
            load()->func('file');file_delete($thumb);
            $temp = pdo_delete("we7car_care", array("weid" => $_W['uniacid'], 'id' => $id));
            if ($temp == false) {
                message('抱歉，删除数据失败！', '', 'error');
            } else {
                message('删除数据成功！', $this->createWebUrl('guanhuai', array('op' => 'car')), 'success');
            }
        }
        if ($op == 'showdetail') {
            $id = intval($_GPC['id']);
            $carone = pdo_fetch("SELECT * FROM" . tablename('we7car_care') . " WHERE `id` = :id LIMIT 1", array(':id' => $id));
            include $this->template('web/car_showdetail');
        }
    }

    //常用工具
    public function doWebTool() {
        global $_GPC, $_W;
        $op = trim($_GPC['op']);
        if ($op == 'save') {
            $insert = array(
                'weid' => $_W['uniacid'],
                'tools' => $_GPC['tools'],
                'create_time' => time(),
            );
            $temp = pdo_update('we7car_set', $insert, array('weid' => $_W['uniacid']));
            if ($temp == false) {
                $temp = pdo_insert('we7car_set', $insert);
            }
            if ($temp == false) {
                message('抱歉，更新设置数据失败！', '', 'error');
            } else {
                message('更新设置数据成功！', $this->createWebUrl('message', array('op' => 'set', 'weid' => $_W['uniacid'])), 'success');
            }
        }
        $tools = pdo_fetchcolumn("SELECT tools FROM " . tablename('we7car_set') . " WHERE  weid = :weid  ", array(':weid' => $_W['uniacid']));
        if ($tools === false) {
            $toolsArr = array(
                '1' => true,
                '2' => true,
                '3' => true,
                '4' => true,
                '5' => true,
            );
        } else {
            $toolsArr = array();
            $tools = explode(",", $tools);
            foreach ($tools as $v) {
                $toolsArr[$v] = true;
            }
        }
        include $this->template('web/tool_set');
    }

    //会员信息，讲客户关怀里面的车主信息系统直接调出来
    public function doWebCar(){
        global $_GPC, $_W;
        $op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
        $pindex = max(1, intval($_GPC['page']));
        $psize = 50;

        if($op =='list'){
            $car_no = $_GPC['car_no'];
            $phone = $_GPC['phone'];
            $sql = " WHERE weid=".$_W['uniacid'];
            if(!empty($car_no)){
                $sql .= " AND car_no like '%".$car_no."%'";
            }
            if(!empty($phone)){
                $sql .= " AND car_mobile = ".$phone;
            }
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " .tablename('we7car_care') . $sql);
            $sql .= " ORDER BY `createtime` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $pager = pagination($total, $pindex, $psize);
            $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_care') . $sql);
            include $this->template('web/car_list');
        }

        //详细页面
        if($op == 'detail'){
            $id = $_GPC['id'];
            $carone = pdo_fetch("SELECT * FROM" . tablename('we7car_care') . " WHERE `id` = :id LIMIT 1", array(':id' => $id));
            include $this->template('web/car_detail');
        }

        //修改资料
        if($op == 'post'){
            $id = $_GPC['id'];
            if($id > 0){
                $car = pdo_fetch("SELECT * FROM" . tablename('we7car_care') . " WHERE `id` = :id LIMIT 1", array(':id' => $id));
                $series = pdo_fetchall("SELECT * FROM ".tablename('we7car_series')." WHERE id=".$car['series_id']." AND weid=".$_W['uniacid']);
                $type = pdo_fetchall("SELECT * FROM ".tablename('we7car_type')." WHERE sid=".$car['series_id']." AND bid = ".$car['brand_id']." AND weid=".$_W['uniacid']);
            }
            $brand = pdo_fetchall("SELECT * FROM ".tablename('we7car_brand')." WHERE weid =".$_W['uniacid']." ORDER BY listorder");

            if(checksubmit('submit')){
                $id = $_GPC['id'];
                $car_no =  !empty($_GPC['car_no']) ? trim($_GPC['car_no']) : message('请填写车牌号码');
                $image =  $_GPC['image'];
                $type_id =  !empty($_GPC['type_id']) ? trim($_GPC['type_id']) : message('请选择车型');
                $car_username =  $_GPC['car_username'];
                $mobile_phone =  !empty($_GPC['mobile_phone']) ? trim($_GPC['mobile_phone']) : message('请填写电话号码');
                $car_code =  $_GPC['car_code'];
                $car_start_time =  $_GPC['car_start_time'];
                $ec_number =  $_GPC['ec_number'];
                $ec_type =  $_GPC['ec_type'];
                $remark =  $_GPC['remark'];
                $car_brind = pdo_fetch("SELECT t.*,b.title AS bname,s.title AS sname FROM ".tablename('we7car_brand')." AS b,".tablename('we7car_type')." AS t,".tablename('we7car_series')." AS s WHERE t.id=".$type_id." AND b.id=t.bid AND s.id=t.sid");
                $insert = array(
                    'weid' => $_W['uniacid'],
                    'brand_id' => $car_brind['bid'],
                    'brand_cn' => $car_brind['bname'],
                    'series_id' => $car_brind['sid'],
                    'series_cn' => $car_brind['sname'],
                    'type_id' => $car_brind['id'],
                    'type_cn' => $car_brind['title'],
                    'car_no' => $car_no,
                    'car_userName' => $car_username,
                    'car_mobile' => $mobile_phone,
                    'car_startTime' => $car_start_time,
                    'createtime' => TIMESTAMP,
                    'isshow' => 1,
                    'remark' => $remark,
                    'car_code' => $car_code,
                    'ec_number' => $ec_number,
                    'ec_type' => $ec_type,
                    'car_photo' => $image,
                );
                if (empty($id)) {
                    $check_car = pdo_fetch("SELECT * FROM ".tablename('we7car_care')." WHERE weid=".$_W['uniacid']." AND car_no like'".$car_no."'");
                    if(!empty($check_car)){
                        message('车牌号码已经存在！', $this->createWebUrl('car', array('op' => 'list')),'error');
                    }
                    pdo_insert('we7car_care', $insert);
                    !pdo_insertid() ? message('保存数据失败, 请稍后重试.', 'error') : '';
                } else {
                    if (pdo_update('we7car_care', $insert, array('id' => $id)) === false) {
                        message('更新数据失败, 请稍后重试.', 'error');
                    }
                }
                message('更新数据成功！', $this->createWebUrl('car', array('op' => 'list')), 'success');
            }

            include $this->template('web/car_post');
        }

        //根据品牌获取车系
        if($op == 'find_series'){
            $id = $_GPC['brand_id'];
            $series = pdo_fetchall("SELECT * FROM ".tablename('we7car_series')." WHERE weid =".$_W['uniacid']." AND bid =".$id);
            echo "<option value='0'>请选择所属车系</option>";
            foreach($series as $s){
                echo "<option value='".$s['id']."'>".$s['title']."</option>";
            }
            exit;
        }

        //根据车系获取车型
        if($op == 'find_type'){
            $id = $_GPC['series_id'];
            $type = pdo_fetchall("SELECT * FROM ".tablename('we7car_type')." WHERE weid =".$_W['uniacid']." AND sid =".$id);
            echo "<option value='0'>请选择所属车型</option>";
            foreach($type as $t){
                echo "<option value='".$t['id']."'>".$t['title']."</option>";
            }
            exit;
        }

    }

    //门店导航
    public function doMobileStore(){
        global $_GPC, $_W;
        $op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';
        $weid = $_W['uniacid'];

        if($op == 'index'){
            $stores = pdo_fetchall("SELECT * FROM ".tablename('we7car_stores')." WHERE weid =".$_W['uniacid']." AND weid =".$_W['uniacid']);
            foreach($stores as $key=>$store){
                $region = pdo_fetch("SELECT * FROM ".tablename('we7car_region')." WHERE id =".$store['region_id']);
                $region_name = $this->get_by_region($store['region_id'],$region['name'].$store['address'],$_W['uniacid']);
                $stores[$key]['region_name'] = $region_name;
            }
            include $this->template('stores');
        }
    }

    //根据region_id获取省份城市
    public function get_by_region($region_id,$region_name,$weid){
        $region = pdo_fetch("SELECT * FROM ".tablename('we7car_region')." WHERE id =".$region_id." AND weid =".$weid);
        if($region['parent_id'] > 0) {
            $up = pdo_fetch("SELECT * FROM " . tablename('we7car_region') . " WHERE id =" . $region['parent_id']." AND weid =".$weid);
            $region_name = $up['name'].$region_name;
            $region_name = $this->get_by_region($up['id'],$region_name,$weid);
        }
        return $region_name;
    }

    //客户订单
    public function doMobileOrders(){
        global $_GPC, $_W;
        $op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';
        $weid = $_W['uniacid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;


        //工单列表
        if($op == 'index'){
            $status = $_GPC['status'];
            $car_ids = $_GPC['car_ids'];
            if(empty($car_ids)){
                $my_cars = pdo_fetchall("SELECT * FROM ".tablename('we7car_care')." WHERE weid =".$weid." AND from_user='".$_W['openid']."'");
                if(!empty($my_cars)) {
                    $car_ids = array();
                    foreach ($my_cars as $car) {
                        $car_ids[] = $car['id'];
                    }
                    $car_ids = implode(',', $car_ids);
                }else{
                    message('您还未登记车辆信息，请先登记',$this->createMobileUrl('guanhuai', array('op' => 'caredit', 'weid' => $weid)), 'error');
                }
            }
            $sql = "SELECT o.*,c.car_no,s.store_name FROM ".tablename("we7car_orders")." AS o,".tablename("we7car_care")." AS c,".tablename('we7car_stores')." AS s WHERE o.weid =".$_W['uniacid']." AND o.car_id in(".$car_ids.") AND o.car_id = c.id AND s.id = o.store_id";
            $total_sql = "SELECT COUNT(*) FROM ".tablename('we7car_orders')." WHERE weid=".$_W['uniacid']." AND car_id in(".$car_ids.")";
            if(!empty($status)){
                $sql .= " AND o.status =".$status;
                $total_sql .= " AND status =".$status;
            }
            $total = pdo_fetchcolumn($total_sql);
            $sql .= " ORDER BY o.createtime DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}";
            $pager = pagination($total, $pindex, $psize);
            $orders = pdo_fetchall($sql);

            include $this->template('orders');
        }

        //订单详细
        if($op == 'detail'){
            $order_id = $_GPC['id'];
            $order_data = pdo_fetch("SELECT * FROM ".tablename('we7car_orders')." WHERE weid=".$_W['uniacid']." AND id =".$order_id);
            $car = pdo_fetch("SELECT * FROM ".tablename('we7car_care')." WHERE id =".$order_data['car_id']);
            $check = pdo_fetchall("SELECT *,c.name,c.title1,c.title2 FROM ".tablename('we7car_car_check')." AS oc,".tablename('we7car_check')." AS c WHERE oc.weid =".$_W['uniacid']." AND oc.order_id =".$order_id." AND oc.check_id = c.id");
            //服务类型
            $all_system = pdo_fetchall("SELECT * FROM ".tablename('we7car_system'));
            $services = pdo_fetchall("SELECT s.*,sy.name FROM ".tablename('we7car_orders_services')." AS s,".tablename('we7car_car_services')." AS sy WHERE order_id =".$order_id." AND s.service_id = sy.id");

            $total_price = 0;
            $worker_price = 0;
            foreach($services as $key=>$service){
                $worker_price += $service['worker_price'];
                $services_goods = pdo_fetchall("SELECT sg.*,g.name,g.goods_sn FROM ".tablename('we7car_order_goods')." as sg,".tablename('we7car_goods')." as g WHERE sg.order_service_id =".$service['id']." AND sg.weid =".$_W['uniacid']." AND sg.goods_id = g.id");
                if(!empty($services_goods)) {
                    foreach ($services_goods as $key2 => $g) {
                        $goods_price = $g['quantity'] * $g['goods_price'];
                        $services_goods[$key2]['total_price'] = $goods_price;
                        $total_price += $goods_price;
                    }
                    $services[$key]['goods'] = $services_goods;
                    $services[$key]['count'] = count($services_goods);
                }else{
                    $services[$key]['count'] = 1;
                }
            }
            $order_price = $total_price+$worker_price;
            include $this->template('order_detail');

        }

        //查看现场
        if($op == 'online_view'){
            $order_id = $_GPC['id'];
            $camera = pdo_fetch("SELECT * FROM ".tablename('we7car_camera')." WHERE weid =".$_W['uniacid']." AND order_id =".$order_id);
            if(empty($camera)){
                message("没有找到车辆对应的摄像头，请联系客服！", $this->createMobileUrl('orders', array('op' => 'detail', 'from_user' => $_W['fans']['from_user'],'id' => $order_id)), 'error');
            }
            include $this->template('camera');
        }

    }


    //客户关怀
    public function doMobileGuanhuai() {
        global $_GPC, $_W;
        $op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';
        $weid = $_W['uniacid'];

        $where = ' WHERE `weid` = :weid';
        $params = array(':weid' => $_W['uniacid']);

        // 获取车主关怀顶部图片
        $sql = 'SELECT `guanhuai_thumb` FROM ' . tablename('we7car_set') . $where;
        $banner = pdo_fetchcolumn($sql, $params);

        // 获取车主信息
        $sql = 'SELECT * FROM ' . tablename('we7car_care') . $where . ' AND `from_user` = :from_user';
        $params[':from_user'] = $_W['openid'];
        $car = pdo_fetch($sql, $params);

        // 设置分享信息
        $shareTitle = $title = '车主关怀';
        $shareDesc = $car['brand_cn'];
        $shareThumb = tomedia($car['car_photo']);

        if (!empty($car)) {
            $car['brand_val'] = $car['brand_id'] . '=' . $car['brand_cn'];
            $car['series_val'] = $car['series_id'] . '=' . $car['series_cn'];
            $car['type_val'] = $car['type_id'] . '=' . $car['type_cn'];
            $car_insurance_nextDate = $car['car_insurance_lastDate'] + 86400 * 90;
            $car_care_nextDate = $car['car_care_nextDate'] + 86400 * 365;
            $insurance_days = floor(($car_insurance_nextDate - TIMESTAMP) / 86400);
            $care_days = floor(($car_insurance_nextDate - TIMESTAMP) / 86400);
            $car['car_startTime'] = $car['car_startTime'] ? date('Y-m-d', $car['car_startTime']) : date('Y-m-d');
            $car['car_insurance_lastDate'] = $car['car_insurance_lastDate'] ? date('Y-m-d', $car['car_insurance_lastDate']) : date('Y-m-d');
            $car['car_care_lastDate'] = $car['car_care_lastDate'] ? date('Y-m-d', $car['car_care_lastDate']) : date('Y-m-d');
        } else {
            $car['car_startTime'] = date('Y-m-d');
            $car['car_insurance_lastDate'] = date('Y-m-d');
            $car['car_care_lastDate'] = date('Y-m-d');
        }

        if ($op == 'index') {
            $my_cars = pdo_fetchall("SELECT * FROM ".tablename('we7car_care')." WHERE weid =".$_W['uniacid']." AND from_user ='".$_W['openid']."'");

            foreach($my_cars as $key => $car){
                if(!empty($car['car_insurance_lastDate'])) {
                    $next_insurance_year = (date("Y", $car['car_insurance_lastDate']) + 1)."-".date("m-d", $car['car_insurance_lastDate']); //下一个保单周期

                    $my_cars[$key]['new_insurance'] = intval((strtotime($next_insurance_year) - TIMESTAMP) / 86400);
                }
            }
            include $this->template('guanhuai_index');
        }

        //客户自检
        if($op == 'check_myself'){
            $car_id = $_GPC['car_id'];
            $now_mileage = $_GPC['now_mileage'];
            //根据车辆的id查询所有做过的保养，然后查保养间隔
            $sql = "SELECT os.end_time,os.service_id,o.mileage,s.name,s.spacing_km,s.spacing_day FROM".tablename('we7car_orders_services')." AS os,".tablename('we7car_orders')." AS o,".tablename('we7car_car_services')." AS s WHERE o.car_id =".$car_id." AND os.order_id = o.id AND o.status = 50 AND os.service_id = s.id AND o.weid =".$_W['uniacid']." GROUP BY os.service_id ORDER BY os.end_time";
            $services = pdo_fetchall($sql);
            if(empty($services)){
                message('系统还未有您车的保养信息！',$this->createMobileUrl('guanhuai'),'error');
            }
            foreach($services as $key=>$service){
                if($service['spacing_day'] > 0) {
                    //先计算时间上面是否需要做保养
                    $next_time = $service['end_time'] + ($service['spacing_day'] * 86400);
                    if ($next_time > TIMESTAMP) {
                        $services[$key]['next_time'] = date("Y-m-d", $next_time);
                    }
                }
                if($service['spacing_km'] > 0) {
                    //先计算时间上面是否需要做保养
                    $next_mileage = $service['mileage'] + $service['spacing_km'];
                    if ($next_mileage > $now_mileage && $now_mileage>0) {
                        $services[$key]['next_mileage'] = $next_mileage-$now_mileage;
                    }
                }
            }
            include $this->template('check_myself');
        }

        if ($op == 'caredit') {
            $car_id = $_GPC['car_id'];
            if(!empty($car_id)) {
                $car = pdo_fetch("SELECT * FROM " . tablename('we7car_care') . " where weid =" . $_W['uniacid'] . " AND id =" . $car_id . " AND from_user =" . $_W['openid']);
            }else{
                $car = null;
            }
            $where .= ' AND `status` = :status';
            unset($params[':from_user']);
            // 获取汽车品牌
            $sql = 'SELECT `id`, `title` FROM ' . tablename('we7car_brand') . $where . ' ORDER BY `listorder` DESC';
            $params[':status'] = 1;
            $brands = pdo_fetchall($sql, $params);

            // 获取汽车车系
            $sql = 'SELECT `id`, `title` FROM ' . tablename('we7car_series') . $where . ' AND `bid` = :bid
                    ORDER BY `listorder` DESC';
            $params[':bid'] = $car['brand_id'];
            $eseries = pdo_fetchall($sql, $params);

            // 获取汽车车型
            $sql = 'SELECT `id`, `title` FROM ' . tablename('we7car_type') . $where . ' AND `sid` = :bid
                    ORDER BY `listorder` DESC';
            $params[':bid'] = $car['series_id'];
            $etypes = pdo_fetchall($sql, $params);

            if (checksubmit('submit')) {
                $brand = explode('=', $_GPC['brand']);
                $series = explode('=', $_GPC['serie']);
                $types = explode('=', $_GPC['types']);
                $insert = array(
                    'weid' => $_W['uniacid'],
                    'from_user' => $_W['fans']['from_user'],
                    'brand_id' => intval($brand[0]),
                    'brand_cn' => trim($brand[1]),
                    'series_id' => trim($series[0]),
                    'series_cn' => trim($series[1]),
                    'type_id' => trim($types[0]),
                    'type_cn' => trim($types[1]),
                    'car_note' => trim($_GPC['car_note']),
                    'car_no' => trim($_GPC['car_no']),
                    'car_userName' => trim($_GPC['car_userName']),
                    'car_mobile' => trim($_GPC['car_mobile']),
                    'car_startTime' => strtotime($_GPC['car_startTime']),
                    'car_insurance_lastDate' => strtotime($_GPC['car_insurance_lastDate']),
                    'car_insurance_lastCost' => trim($_GPC['car_insurance_lastCost']),
                    'car_care_mileage' => trim($_GPC['car_care_mileage']),
                    'car_care_lastDate' => strtotime($_GPC['car_care_lastDate']),
                    'car_care_lastCost' => trim($_GPC['car_care_lastCost']),
                    'car_insurance_lastDate' => strtotime($_GPC['car_insurance_lastDate']),
                    'createtime' => TIMESTAMP
                );
                if (!empty($_FILES['car_photo']['tmp_name'])) {
                    load()->func('file');
                    $upload = file_upload($_FILES['car_photo']);
                    if (is_error($upload)) {
                        message($upload['message']);
                    }
                    $insert['car_photo'] = $upload['path'];
                }
                if (empty($car['id'])) {
                    $temp = pdo_insert('we7car_care', $insert);
                } else {
                    $temp = pdo_update('we7car_care', $insert, array('id' => $car['id']));
                }
                if ($temp == false) {
                    message('抱歉，更新爱车数据失败！', $this->createMobileUrl('guanhuai', array('op' => 'caredit', 'from_user' => $_W['fans']['from_user'])), 'error');
                } else {
                    message('更新爱车数据成功！', $this->createMobileUrl('guanhuai', array('op' => 'index', 'weid' => $weid, 'from_user' => $_W['fans']['from_user'])), 'success');
                }
            }
            load()->func('tpl');
            include $this->template('guanhuai_caredit');
        }

    }

    //留言管理
    public function doWebMessage() {
        global $_GPC, $_W;
        $op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
        if ($op == 'list') {
            $isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : '2';
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            if ($isshow == '2') {
                //==2表示显示全部
                $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_message_list') . " WHERE weid = :weid AND fid = 0", array(':weid' => $_W['uniacid']));
                $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_message_list') . " WHERE weid = :weid AND fid = 0 ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}", array(':weid' => $_W['uniacid']));
            } else {
                $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_message_list') . " WHERE weid = :weid AND isshow = :isshow AND fid = 0", array(':weid' => $_W['uniacid'], ':isshow' => $isshow));
                $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_message_list') . " WHERE weid = :weid AND isshow = :isshow AND fid = 0 ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}", array(':weid' => $_W['uniacid'], ':isshow' => $isshow));
            }
            $pager = pagination($total, $pindex, $psize);
            $message = pdo_fetch("SELECT id, isshow, weid FROM " . tablename('we7car_message_set') . " WHERE weid = '{$weid}' LIMIT 1");

            if (!empty($list)) {
                foreach ($list as &$row) {
                    $row['content'] = emotion($row['content']);
                    $row['reply'] = pdo_fetchall("SELECT * FROM " . tablename('we7car_message_list') . " WHERE weid = :weid  AND fid = :fid ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}", array(':weid' => $_W['uniacid'], ':fid' => $row['id']));
                    unset($row);
                }
            }
            if (checksubmit('delete') && (!empty($_GPC['select']) || !empty($_GPC['select1']))) {
                if (!empty($_GPC['select'])) {
                    pdo_delete('we7car_message_list', " fid  IN  ('" . implode("','", $_GPC['select']) . "')");
                    pdo_delete('we7car_message_list', " id  IN  ('" . implode("','", $_GPC['select']) . "')");
                }
                if (!empty($_GPC['select1'])) {
                    pdo_delete('we7car_message_list', " id  IN  ('" . implode("','", $_GPC['select1']) . "')");
                }
                message('删除成功！', $this->createWebUrl('message', array('weid' => $weid, 'page' => $_GPC['page'], 'isshow' => $_GPC['isshow'])));
            }
            if (checksubmit('verify') && (!empty($_GPC['select']) || !empty($_GPC['select1']))) {
                $isshow = intval($_GPC['isshow']);
                if (!empty($_GPC['select'])) {
                    pdo_update('we7car_message_list', array('isshow' => $isshow), " id  IN  ('" . implode("','", $_GPC['select']) . "')");
                }
                if (!empty($_GPC['select1'])) {
                    pdo_update('we7car_message_list', array('isshow' => $isshow), " id  IN  ('" . implode("','", $_GPC['select1']) . "')");
                }
                message('审核成功！', $this->createWebUrl('message', array('weid' => $weid, 'page' => $_GPC['page'], 'isshow' => $_GPC['isshow'])));
            }
            include $this->template('web/message_list');
        }
        if ($op == 'set') {
            if (checksubmit('submit')) {
                $id = intval($_GPC['id']);
                $title = !empty($_GPC['title']) ? trim($_GPC['title']) : message('请填写意见反馈的显示名称');
                $thumb = !empty($_GPC['thumb']) ? trim($_GPC['thumb']) : message('请上传意见反馈的头部图片');
                //保存数据
                $insert = array(
                    'weid' => $_W['uniacid'],
                    'title' => $title,
                    'thumb' => $thumb,
                    'status' => intval($_GPC['status']),
                    'isshow' => intval($_GPC['isshow']),
                    'create_time' => TIMESTAMP
                );
                if ($id == 0) {
                    $temp = pdo_insert('we7car_message_set', $insert);
                } else {
                    $temp = pdo_update('we7car_message_set', $insert, array('id' => $id));
                }
                if ($temp == false) {
                    message('抱歉，更新设置数据失败！', '', 'error');
                } else {
                    message('更新设置数据成功！', $this->createWebUrl('message', array('op' => 'set','weid' => $_W['uniacid'])), 'success');
                }
            }
            $theone = pdo_fetch("SELECT * FROM " . tablename('we7car_message_set') . " WHERE  weid = :weid  ", array(':weid' => $_W['uniacid']));
            //数据为空，赋值
            if (empty($theone)) {
                $theone = array(
                    'status' => 1,
                    'isshow' => 1,
                );
            }
            include $this->template('web/message_set');
        }
    }

    public function doMobileMessage() {
        global $_GPC, $_W;
        $op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
        $set = pdo_fetch("SELECT * FROM " . tablename('we7car_message_set') . " WHERE weid = :weid ORDER BY `id` DESC LIMIT 1", array(':weid' => $_W['uniacid']));

        // 设置分享信息
        $shareDesc = $shareTitle = $title = $set['title'];
        $shareThumb = tomedia($set['thumb']);

        if ($set == false) {
            $set = array(
                'status' => 1,
                'isshow' => 1,
            );
        }
        if ($op == 'list') {
            if ($set['status'] == 0) {
                message('留言墙尚未开启,请耐心等待');
            }
            $total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('we7car_message_list') . " WHERE fid = 0 AND isshow = 1 AND weid = :weid", array(':weid' => $_W['uniacid']));
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $pager = pagination($total, $pindex, $psize);
            $messagelist = pdo_fetchall("SELECT * FROM " . tablename('we7car_message_list') . " WHERE  weid = :weid and fid=0 and isshow=1  ORDER BY create_time DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $_W['uniacid']));
            foreach ($messagelist as &$v) {
                $v['reply'] = pdo_fetchall("SELECT * FROM " . tablename('we7car_message_list') . " WHERE  weid = :weid AND fid = :fid AND isshow=1  LIMIT 20", array(':weid' => $_W['uniacid'], ':fid' => $v['id']));
            }
            //获取fans表中的username
            // $nickname = pdo_fetchcolumn("SELECT `nickname` FROM " . tablename('fans') . " WHERE weid = :weid AND `from_user` = :from_user LIMIT 1", array(':from_user' => $_W['fans']['from_user'], ':weid' => $_W['uniacid']));
            $mc = mc_require($_W['member']['uid'], array('nickname'));
            $nickname = $m['nickname'];
            include $this->template('message_list');
        }
        if ($op == 'ajax') {
            if (empty($_W['fans']['from_user'])) {
                $data['msg'] = '登陆过期，请重新从微信进入!';
                $data['success'] = false;
            } else {
                $sql = "SELECT * FROM " . tablename('we7car_message_list') . " WHERE from_user = :from_user AND weid = :weid ORDER BY id DESC";
                $params = array(':weid' => $_W['uniacid'], ':from_user' => $_W['fans']['from_user']);
                $message = pdo_fetch($sql, $params);
                $insert = array(
                    'weid' => $_W['uniacid'],
                    'nickname' => trim($_GPC['nickname']),
                    'info' => trim($_GPC['info']),
                    'fid' => intval($_GPC['fid']),
                    'from_user' => $_W['fans']['from_user'],
                    'isshow' => $set['isshow'],
                    'create_time' => TIMESTAMP
                );
                if (empty($insert['nickname'])) {
                    if (empty($_W['member']['uid'])) {
                        $sql = "SELECT nickname FROM " . tablename('mc_mapping_fans') . " WHERE uniacid = :uniacid  AND openid = :from_user";
                        $params = array(':uniacid' => $_W['uniacid'], ':from_user' => $_W['fans']['from_user']);
                        $nickname = pdo_fetchcolumn($sql, $params);
                        if (empty($nickname)) {
                            $nickname = $_W['fans']['from_user'];
                        }
                    } else {
                        load()->model('mc');
                        $nickname = mc_fetch($_W['member']['uid'], array('nickname'));
                        $nickname = $nickname['nickname'];
                    }
                    $insert['nickname'] = $nickname;
                }

                if (empty($message)) {
                    $id = pdo_insert('we7car_message_list', $insert);
                    $data['success'] = true;
                    $data['msg'] = '留言发表成功';
                    if ($set['isshow'] == 0) {
                        $data['msg'] = $data['msg'] . ',进入审核流程';
                    }
                } else {
                    if ((TIMESTAMP - $message['create_time']) < 5) {
                        $data['msg'] = '您的留言太过频繁，请5秒后留言';
                        $data['success'] = false;
                    } else {
                        $id = pdo_insert('we7car_message_list', $insert);
                        $data['success'] = true;
                        $data['msg'] = '留言发表成功';
                        if ($set['isshow'] == 0) {
                            $data['msg'] = $data['msg'] . ',进入审核流程';
                        }
                    }
                }
            }
            echo json_encode($data);
        }
    }

    public function doMobileTool() {
        global $_GPC, $_W;
        $sql = 'SELECT `shop_logo`, `tools` FROM ' . tablename('we7car_set') . ' WHERE `weid` = :weid';
        $car = pdo_fetch($sql, array(':weid' => $_W['uniacid']));
        $tools = $car['tools'];
        if ($tools === false) {
            $toolsArr = array(
                '1' => true,
                '2' => true,
                '3' => true,
                '4' => true,
                '5' => true,
            );
        } else {
            $toolsArr = array();
            $tools = explode(",", $tools);
            foreach ($tools as $v) {
                $toolsArr[$v] = true;
            }
        }

        // 设置分享信息
        $shareTitle = $title = '实用小工具';
        $shareDesc = '实用小工具';
        $shareThumb = tomedia($car['shop_logo']);

        include $this->template('tools_index');
    }

    public function doWebNews() {
        global $_GPC, $_W;
        $op = trim($_GPC['op']) ? trim($_GPC['op']) : 'news_list';
        if ($op == 'post') {
            $id = intval($_GPC['id']);
            if ($id) {
                $categoryone = pdo_fetch("SELECT * FROM " . tablename('we7car_news_category') . " WHERE  weid = :weid  AND id = :id limit 1", array(':weid' => $_W['uniacid'], ':id' => $id));
            } else {
                $categoryone = array('displayorder' => 0, 'status' => 1);
            }
            if (checksubmit('submit')) {
                $title = !empty($_GPC['title']) ? trim($_GPC['title']) : message('请填写分类标题');
                $description = !empty($_GPC['description']) ? trim($_GPC['description']) : message('请填写分类描述');
                $thumb = !empty($_GPC['thumb']) ? trim($_GPC['thumb']) : message('请上传分类图片');
                $displayorder = intval($_GPC['displayorder']);
                $data = array(
                    'title' => $title,
                    'description' => $description,
                    'displayorder' => $displayorder,
                    'weid' => $_W['uniacid'],
                    'thumb' => $thumb,
                    'status' => intval($_GPC['status'])
                );
                if (!$id) {
                    $temp = pdo_insert('we7car_news_category', $data);
                } else {
                    $temp = pdo_update('we7car_news_category', $data, array('id' => $id, 'weid' => $_W['uniacid']));
                }
                if ($temp === false) {
                    message('更新分类数据失败', '', 'error');
                } else {
                    message('更新分类数据成功', $this->createWebUrl('news', array('op' => 'list')), 'success');
                }
            }
            include $this->template('web/category');
        }
        if ($op == 'list') {
            $categorys = pdo_fetchall("SELECT * FROM " . tablename('we7car_news_category') . " WHERE  weid = :weid  ORDER BY displayorder DESC ", array(':weid' => $_W['uniacid']));
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update('we7car_news_category', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', 'refresh', 'success');
            }
            include $this->template('web/category');
        }
        if ($op == 'del') {
            $id = intval($_GPC['id']);
            $thumb = pdo_fetchcolumn("SELECT thumb FROM " . tablename('we7car_news_category') . " WHERE id = :id", array(':id' => $id));
            load()->func('file');file_delete($thumb);
            $temp = pdo_delete("we7car_news_category", array("weid" => $_W['uniacid'], 'id' => $id));
            if ($temp == false) {
                message('抱歉，删除分类数据失败！', '', 'error');
            } else {
                message('删除分类数据成功！', $this->createWebUrl('news', array('op' => 'list')), 'success');
            }
        }

        if ($op == 'add_news') {
            $categorys = pdo_fetchall("SELECT * FROM " . tablename('we7car_news_category') . " WHERE  weid = :weid  ORDER BY displayorder DESC ", array(':weid' => $_W['uniacid']));
            $template = pdo_fetchall("SELECT * FROM " . tablename('site_templates') . " ORDER BY `id` ASC");
            //var_dump($template);
            $id = intval($_GPC['id']);
            $cateid = intval($_GPC['cateid']);
            if (!empty($id)) {
                $newsone = pdo_fetch("SELECT * FROM " . tablename('we7car_news') . " WHERE id = :id", array(':id' => $id));
                $newsone['type'] = explode(',', $newsone['type']);
                if (empty($newsone)) {
                    message('抱歉，文章不存在或是已经删除！', '', 'error');
                }
            }
            if (checksubmit('fileupload-delete')) {
                load()->func('file');
                file_delete($_GPC['fileupload-delete']);
                pdo_update('we7car_news', array('thumb' => ''), array('id' => $id));
                message('删除成功！', referer(), 'success');
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('标题不能为空，请输入标题！');
                }
                $data = array(
                    'weid' => $_W['uniacid'],
                    'iscommend' => intval($_GPC['option']['commend']),
                    'ishot' => intval($_GPC['option']['hot']),
                    'category_id' => intval($_GPC['category_id']),
                    'title' => trim($_GPC['title']),
                    'template' => trim($_GPC['template']),
                    'description' => trim($_GPC['description']),
                    'content' => htmlspecialchars_decode($_GPC['content']),
                    'source' => $_GPC['source'],
                    'author' => $_GPC['author'],
                    'thumb'=>$_GPC['thumb'],
                    'createtime' => TIMESTAMP,
                );
                if (!empty($_GPC['autolitpic'])) {
                    $match = array();
                    preg_match('/attachment\/(.*?)(\.gif|\.jpg|\.png|\.bmp)/', $_GPC['content'], $match);
                    if (!empty($match[1])) {
                        $data['thumb'] = $match[1] . $match[2];
                    }
                }
                if (empty($id)) {
                    pdo_insert('we7car_news', $data);
                } else {
                    unset($data['createtime']);
                    pdo_update('we7car_news', $data, array('id' => $id));
                }
                message('文章更新成功！', $this->createWebUrl('news', array('op' => 'news_list')), 'success');
            }
            include $this->template('web/news');
        }
        if ($op == 'news_list') {
            $categorys = pdo_fetchall("SELECT * FROM " . tablename('we7car_news_category') . " WHERE  weid = :weid  ORDER BY displayorder DESC ", array(':weid' => $_W['uniacid']));
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $params = array();
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND title LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            $category_id = intval($_GPC['category_id']);
            if ($category_id > 0) {

                $condition .= " AND category_id = '{$category_id}'";
            }
            $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_news') . " WHERE weid = '{$_W['uniacid']}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            foreach ($list as &$li) {
                $category = pdo_fetch('SELECT id,title FROM ' . tablename('we7car_news_category') . " WHERE id = :id AND weid = '{$_W['uniacid']}'", array(':id' => $li['category_id']));
                $li['category_title'] = $category['title'];
                $li['category_id'] = $category['id'];
            }
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_news') . " WHERE weid = '{$_W['uniacid']}' $condition", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/news');
        }
        if ($op == 'del_news') {
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT id, thumb FROM " . tablename('we7car_news') . " WHERE id = :id", array(':id' => $id));
            if (empty($row)) {
                message('抱歉，文章不存在或是已经被删除！');
            }
            if (!empty($row['thumb'])) {
               load()->func('file'); file_delete($row['thumb']);
            }
            pdo_delete('we7car_news', array('id' => $id));
            message('删除成功！', referer(), 'success');
        }
    }

    public function doMobileNews() {
        global $_GPC, $_W;
        $op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'news_list';
        if ($op == 'news_list') {
            $category_id = intval($_GPC['category_id']);
            $category = pdo_fetch('SELECT * FROM ' . tablename('we7car_news_category') . " WHERE weid = '{$_W['uniacid']}' AND id = :id LIMIT 1", array(':id' => $category_id));
            $pindex = max(1, intval($_GPC['page']));
            $psize = 1;
            $news = pdo_fetchall("SELECT * FROM " . tablename('we7car_news') . " WHERE weid = '{$_W['uniacid']}' AND category_id = :category_id ORDER BY ishot DESC,createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':category_id' => $category_id));
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_news') . " WHERE weid = '{$_W['uniacid']}' AND category_id = :category_id ", array(':category_id' => $category_id));
            $pager = pagination($total, $pindex, $psize);

            // 设置分享信息
            $shareTitle = $title = $category['title'];
            $shareDesc = $category['description'];
            $shareThumb = tomedia($category['thumb']);
        }
        if ($op == 'news_detail') {
            $id = intval($_GPC['id']);
            $new = pdo_fetch("SELECT * FROM " . tablename('we7car_news') . " WHERE weid = :weid AND id = :id  LIMIT 1", array(':weid' => $_W['uniacid'], ':id' => $id));
            $new = istripslashes($new);
            $new['thumb'] = $_W['attachurl'] . trim($new['thumb'], '/');
            //独立选择内容模板
            if (!empty($new['template'])) {
                $_W['account']['template'] = $new['template'];
            }

            // 设置分享信息
            $shareTitle = $title = $new['title'];
            $shareDesc = str_replace("\r\n", '', strip_tags($new['description']));
            $shareThumb = tomedia($new['thumb']);
        }
        include $this->template('news');
    }

    //汽车相册
    public function doWebAlbum() {
        global $_W, $_GPC;
        $op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($op == 'getseries') {
            $bid = intval($_GPC['bid']);
            $ty = trim($_GPC['ty']);
            if ($bid) {
                if ($ty == 'series') {
                    $datas = pdo_fetchall("SELECT id,bid,title FROM " . tablename('we7car_series') . " WHERE `weid` = :weid AND `status` = 1 AND `bid` = :bid ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid'], ':bid' => $bid));
                    $html = "<option value='0'>请选择车系</option>";
                    foreach ($datas as $val) {
                        $html.="<option value='{$val['id']}'>{$val['title']}</option>";
                    }
                } elseif ($ty == 'types') {

                    $datas = pdo_fetchall("SELECT id,title FROM " . tablename('we7car_type') . " WHERE `weid` = :weid AND `status` = 1 AND `sid` = :bid ORDER BY `listorder` DESC", array(':weid' => $_W['uniacid'], ':bid' => $bid));
                    $html = "<option value='0'>请选择车型</option>";
                    foreach ($datas as $val) {
                        $html.="<option id='type_" . $val['id'] . "' value='{$val['id']}'>{$val['title']}</option>";
                    }
                }
            }
            exit($html);
        }
        if ($op == 'create') {
            $brands = pdo_fetchall('SELECT id,title FROM ' . tablename('we7car_brand') . " WHERE `weid` = :weid AND status = 1 ORDER BY listorder DESC", array(':weid' => $_W['uniacid']));
            $series = pdo_fetchall('SELECT id,title FROM ' . tablename('we7car_series') . " WHERE `weid` = :weid AND status = 1 ORDER BY listorder DESC", array(':weid' => $_W['uniacid']));
            $types = pdo_fetchall('SELECT id,title FROM ' . tablename('we7car_type') . " WHERE `weid` = :weid AND status = 1 ORDER BY listorder DESC", array(':weid' => $_W['uniacid']));

            $bid = intval($_GPC['bid']);
            $sid = intval($_GPC['sid']);
            $tid = intval($_GPC['tid']);
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('we7car_album') . " WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，相册不存在或是已经删除！', '', 'error');
                }
                $bid = $item['bid'];
                $sid = $item['sid'];
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('请输入相册名称！');
                }
                $data = array(
                    'weid' => $_W['uniacid'],
                    'type_id' => intval($_GPC['type_id']),
                    'title' => $_GPC['title'],
                    'thumb'=>$_GPC['thumb'],
                    'content' => $_GPC['content'],
                    'displayorder' => intval($_GPC['displayorder']),
                    'isview' => intval($_GPC['isview']),
                    'type' => intval($_GPC['type']),
                    'createtime' => TIMESTAMP,
                );
                if (!empty($_FILES['thumb']['tmp_name'])) {
                    load()->func('file');
                    file_delete($_GPC['thumb_old']);
                }
                if (empty($id)) {
                    pdo_insert('we7car_album', $data);
                } else {
                    unset($data['createtime']);
                    pdo_update('we7car_album', $data, array('id' => $id));
                }
                message('相册更新成功！', $this->createWebUrl('album', array('op' => 'display')), 'success');
            }
            include $this->template('web/album');
        } elseif ($op == 'display') {
            $brands = pdo_fetchall('SELECT id,title FROM ' . tablename('we7car_brand') . " WHERE `weid` = :weid AND status = 1 ORDER BY listorder DESC", array(':weid' => $_W['uniacid']));
            $series = pdo_fetchall('SELECT id,title FROM ' . tablename('we7car_series') . " WHERE `weid` = :weid AND status = 1 ORDER BY listorder DESC", array(':weid' => $_W['uniacid']));
            $types = pdo_fetchall('SELECT id,title FROM ' . tablename('we7car_type') . " WHERE `weid` = :weid AND status = 1 ORDER BY listorder DESC", array(':weid' => $_W['uniacid']));

            $bid = intval($_GPC['bid']);
            $sid = intval($_GPC['sid']);
            $tid = intval($_GPC['type_id']);
            
            $pindex = max(1, intval($_GPC['page']));
            $psize = 12;
            $condition = '';
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
            }
            if(!empty($tid)){
                $condition .= " AND type_id='{$tid}'";
            }

            $list = pdo_fetchall("SELECT * FROM " . tablename('we7car_album') . " WHERE weid = '{$_W['uniacid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_album') . " WHERE weid = '{$_W['uniacid']}' $condition");
            $pager = pagination($total, $pindex, $psize);
            if (!empty($list)) {
                foreach ($list as &$row) {
                    $row['total'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('we7car_album_photo') . " WHERE albumid = :albumid", array(':albumid' => $row['id']));
                }
                unset($row);
            }
            include $this->template('web/album');
        } elseif ($op == 'photo') {
            $id = intval($_GPC['albumid']);
            $album = pdo_fetch("SELECT id, type FROM " . tablename('we7car_album') . " WHERE id = :id", array(':id' => $id));
            if (empty($album)) {
                message('相册不存在或是已经被删除！');
            }
            if (checksubmit('submit')) {
                if (!empty($_GPC['attachment-new'])) {
                    foreach ($_GPC['attachment-new'] as $index => $row) {
                        if (empty($row)) {
                            continue;
                        }
                        $data = array(
                            'weid' => $_W['uniacid'],
                            'albumid' => intval($_GPC['albumid']),
                            'title' => $_GPC['title-new'][$index],
                            'description' => $_GPC['description-new'][$index],
                            'attachment' => $_GPC['attachment-new'][$index],
                            'displayorder' => $_GPC['displayorder-new'][$index],
                        );
                        pdo_insert('we7car_album_photo', $data);
                    }
                }
                if (!empty($_GPC['attachment'])) {
                    foreach ($_GPC['attachment'] as $index => $row) {
                        if (empty($row)) {
                            continue;
                        }
                        $data = array(
                            'weid' => $_W['uniacid'],
                            'albumid' => intval($_GPC['albumid']),
                            'title' => $_GPC['title'][$index],
                            'description' => $_GPC['description'][$index],
                            'attachment' => $_GPC['attachment'][$index],
                            'displayorder' => $_GPC['displayorder'][$index],
                        );
                        pdo_update('we7car_album_photo', $data, array('id' => $index));
                    }
                }
                message('相册更新成功！', $this->createWebUrl('album', array('op' => 'photo', 'albumid' => $album['id'])));
            }
            if ($album['type'] == 0) {
                $photos = pdo_fetchall("SELECT * FROM " . tablename('we7car_album_photo') . " WHERE albumid = :albumid ORDER BY displayorder DESC", array(':albumid' => $album['id']));
            } else {
                $photos = pdo_fetchall("SELECT * FROM " . tablename('we7car_album_photo') . " WHERE albumid = :albumid ORDER BY displayorder ASC", array(':albumid' => $album['id']));
            }
            include $this->template('web/album');
        } elseif ($op == 'delete') {
            $type = $_GPC['type'];
            $id = intval($_GPC['id']);
            if ($type == 'photo') {
                if (!empty($id)) {
                    $item = pdo_fetch("SELECT * FROM " . tablename('we7car_album_photo') . " WHERE id = :id", array(':id' => $id));
                    if (empty($item)) {
                        message('图片不存在或是已经被删除！');
                    }
                    pdo_delete('we7car_album_photo', array('id' => $item['id']));
                } else {
                    $item['attachment'] = $_GPC['attachment'];
                }
               load()->func('file'); file_delete($item['attachment']);
            } elseif ($type == 'album') {
                load()->func('file');
                $album = pdo_fetch("SELECT id, thumb FROM " . tablename('we7car_album') . " WHERE id = :id", array(':id' => $id));
                if (empty($album)) {
                    message('相册不存在或是已经被删除！');
                }
                $photos = pdo_fetchall("SELECT id, attachment FROM " . tablename('we7car_album_photo') . " WHERE albumid = :albumid", array(':albumid' => $id));
                if (!empty($photos)) {load()->func('file');
                    foreach ($photos as $row) {
                        file_delete($row['attachment']);
                    }
                }
                file_delete($album['thumb']);
                pdo_delete('we7car_album', array('id' => $id));
                pdo_delete('we7car_album_photo', array('albumid' => $id));
            }
            message('删除成功！', referer(), 'success');
        } elseif ($op == 'cover') {
            $id = intval($_GPC['albumid']);
            $attachment = $_GPC['thumb'];
            if (empty($attachment)) {
                message('抱歉，参数错误，请重试！', '', 'error');
            }
            $item = pdo_fetch("SELECT * FROM " . tablename('we7car_album') . " WHERE id = :id", array(':id' => $id));
            if (empty($item)) {
                message('抱歉，相册不存在或是已经删除！', '', 'error');
            }
            pdo_update('we7car_album', array('thumb' => $attachment), array('id' => $id));
            message('设置封面成功！', '', 'success');
        }
    }

    public function doMobilealbum() {
        global $_W, $_GPC;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $result = array();
        $result['list'] = pdo_fetchall("SELECT * FROM " . tablename('we7car_album') . " WHERE weid = '{$_W['uniacid']}' AND isview = '1' ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('we7car_album') . " WHERE isview = '1'");
        $result['pager'] = pagination($total, $pindex, $psize);
        // 设置分享信息
        $shareDesc = $shareTitle = $title = '车型欣赏';
        $shareThumb = $_W['siteroot'] . '/addons/we7_car/style/images/albums_head.jpg';

        include $this->template('album_list');
    }

    public function doMobileDetail() {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        $album = pdo_fetch("SELECT * FROM " . tablename('we7car_album') . " WHERE id = :id", array(':id' => $id));
        if (empty($album)) {
            message('相册不存在或是已经被删除！');
        }

        // 设置分享信息
        $shareTitle = $title = $album['title'];
        $shareDesc = $album['content'];
        $shareThumb = tomedia($album['thumb']);

        $result['list'] = pdo_fetchall("SELECT * FROM " . tablename('we7car_album_photo') . " WHERE albumid = :albumid ORDER BY displayorder DESC", array(':albumid' => $album['id']));
        $url = $this->createMobileUrl('detail', array('id' => $id));
        //360全景
        if ($album['type'] == 1 && $_GPC['gettype'] == 'xml') {
            $result['list'] = pdo_fetchall("SELECT * FROM " . tablename('we7car_album_photo') . " WHERE albumid = :albumid ORDER BY displayorder ASC", array(':albumid' => $album['id']));
            header("Content-type: text/xml");
            $xml = '<?xml version="1.0" encoding="UTF-8"?>
			<panorama id="" hideabout="1">
				<view fovmode="0" pannorth="0">
					<start pan="5.5" fov="80" tilt="1.5"/>
					<min pan="0" fov="80" tilt="-90"/>
					<max pan="360" fov="80" tilt="90"/>
				</view>
				<userdata title="" datetime="2013:05:23 21:01:02" description="" copyright="" tags="" author="" source="" comment="" info="" longitude="" latitude=""/>
				<hotspots width="180" height="20" wordwrap="1">
					<label width="180" backgroundalpha="1" enabled="1" height="20" backgroundcolor="0xffffff" bordercolor="0x000000" border="1" textcolor="0x000000" background="1" borderalpha="1" borderradius="1" wordwrap="1" textalpha="1"/>
					<polystyle mode="0" backgroundalpha="0.2509803921568627" backgroundcolor="0x0000ff" bordercolor="0x0000ff" borderalpha="1"/>
				</hotspots>
				<media/>
				<input tilesize="700" tilescale="1.014285714285714" tile0url="' . $_W['attachurl'] . $result['list']['0']['attachment'] . '" tile1url="' . $_W['attachurl'] . $result['list']['1']['attachment'] . '" tile2url="' . $_W['attachurl'] . $result['list']['2']['attachment'] . '" tile3url="' . $_W['attachurl'] . $result['list']['3']['attachment'] . '" tile4url="' . $_W['attachurl'] . $result['list']['4']['attachment'] . '" tile5url="' . $_W['attachurl'] . $result['list']['5']['attachment'] . '"/>
				<autorotate speed="0.200" nodedelay="0.00" startloaded="1" returntohorizon="0.000" delay="5.00"/>
				<control simulatemass="1" lockedmouse="0" lockedkeyboard="0" dblclickfullscreen="0" invertwheel="0" lockedwheel="0" invertcontrol="1" speedwheel="1" sensitivity="8"/>
			</panorama>';
            return $xml;
        }
        include $this->template('album_detail');
    }



}
