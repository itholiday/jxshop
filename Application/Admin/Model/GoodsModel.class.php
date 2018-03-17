<?php
namespace Admin\Model;

use Think\Crypt\Driver\Think;

class GoodsModel extends CommonModel{
    //自定义字段(表结构)
    protected $fields = array('id','goods_name','goods_sn','cate_id','market_price','shop_price','goods_img','goods_thumb',
        'goods_body','is_hot','is_rec','is_new','addtime','isdel','type_id','is_sale','goods_number','cx_price','start','end',
        'plcount','sale_number');
    protected $_validate = array(
        array('goods_name','require','商品名称不能为空',1),
        array('market_price','currency','市场价格格式填写不正确'),
        array('shop_price','currency','商品价格格式填写不正确')
    );

    public function _before_insert(&$data)
    {
        //生成时间和货号
        $data['addtime'] = time();
        if($data['goods_sn'] == ''){
            $data['goods_sn'] = 'JX'.uniqid();
        }else{
            $info = $this->where('goods_sn='.$data['goods_sn'])->find();
            if($info){
                $this->error = '货号重复';
                return false;
            }
        }

        //促销商品处理
        if($data['cx_price']<=0){
            $this->error = '促销商品价格不合法';
            return false;
        }else{
            $data['start'] = strtotime($data['start']);
            $data['end'] = strtotime($data['end']);
        }

        if(!empty($_FILES['goods_img']['name'])){
            //商品图片上传
            if($path = $this->getPathAfterUpload($_FILES['goods_img'])){
                $data['goods_img'] = $path['goods_img'];
                $data['goods_thumb'] = $path['path'];
            }
        }
    }

    public function _after_insert($data, $options)
    {
        //得到刚插入的商品ID
        $id = $data['id'];
        //得到扩展分类ID
        $ext_cate_ids = array_unique(I('post.ext_cate_id'));
        foreach($ext_cate_ids as $key => $value){
            if($value != 0){
                $list[] = array(
                    'goods_id' => $id,
                    'cate_id' => $value
                );
            }
        }
        M('GoodsCate')->addAll($list);

        //商品属性添加
        $attr = I('post.attr');
        D('GoodsAttr')->insertAttr($attr,$id);

        //商品相册
        unset($_FILES['goods_img']);
        $config = array(
            'maxSize' => 3145728,
            'exts' => array('jpg', 'gif', 'png', 'jpeg')
        );
        $upload = new \Think\Upload($config);
        $info = array();
        $info = $upload->upload();
        if(!$info){
            $this->error = $upload->getError();
            return false;
        }
        foreach ($info as $key => $value){
            //原图地址
            $goods_img = 'Uploads/' . $value['savepath'] . $value['savename'];

            //缩略图生成
            $img = new \Think\Image();
            $img->open($goods_img);
            $thumb_path = 'Uploads/' . $value['savepath'] . 'thumb_' . $value['savename'];
            $img->thumb(450,450)->save($thumb_path);
            $list[] = array(
                'goods_id' => $id,
                'goods_img' => $goods_img,
                'goods_thumb' => $thumb_path
            );
        }
        M('GoodsImg')->addAll($list);
    }

    public function listData($trash=false){
        if(!$trash){
            $where = 'isdel=1';
        }else{
            $where = 'isdel=0';
        }

        $cate_id = intval(I('get.cate_id'));

        //选中搜索分类，拼接where条件
        if($cate_id){
            $m = D('Category');
            $child_cate_ids = $m->getSubTree($cate_id);
            foreach($child_cate_ids as $value){
                $list[] = $value['id'];
            }
            $list[] = $cate_id;
            $cate_str = implode(',',$list);

            $goods_ids = D('GoodsCate')->group('goods_id')->where('cate_id='.$cate_id)->select();
            foreach ($goods_ids as $value){
                $list[] = $value['goods_id'];
            }
            $goods_str = implode(',',$list);
            if(!$goods_ids){
                $where .= " AND cate_id in ($cate_str)";
            }else{
                $where .= " AND (cate_id in ($cate_str)) OR (id in ($goods_str)) ";
            }
        }

        $intro_type = (string)I('get.intro_type');
        if($intro_type){
            if($intro_type == 'is_rec'){
                $where .= " AND (is_rec = 1)";
            }elseif ($intro_type == 'is_hot'){
                $where .= " AND (is_hot = 1)";
            }elseif($intro_type == 'is_new'){
                $where .= " AND (is_new = 1) ";
            }
        }

        $is_sale = intval(I('get.is_sale'));
        if($is_sale){
            if($is_sale == 1){
                $where .= " AND (is_sale = 1)";
            }elseif ($is_sale == 2){
                $where .= " AND (is_sale = 0)";
            }
        }

        $keyword = (string)I('get.keyword');
        if($keyword){
            $where .= " AND (goods_name like '%$keyword%')";
        }

        $count = $this->where($where)->count();
        $pagesize = 10;
        $page = new \Think\Page($count,$pagesize);
        $pagestring = $page->show();
        $p = intval(I('get.p'));
        $data = $this->page($p,$pagesize)->where($where)->select();
        return array(
            'data' => $data,
            'pagestring' => $pagestring
        );
    }

    /*核对商品是否真正删除，如果在回收站则true，如果不在false*/
    public function checkDelete($id){
        $status = $this->field('isdel')->find($id);
        if($status) {
            return false;
        }else{
            return true;
        }
    }

    /*彻底删除商品*/
    public function deleteGoods($id){
        $this->deleteOriginImg($id);
        return $this->delete($id);
    }

    /*删除原有的图片*/
    public function deleteOriginImg($id){
        if($img_path = $this->field('goods_img,goods_thumb')->find($id)){
            $res1 = unlink($img_path['goods_img']);
            $res2 = unlink($img_path['goods_thumb']);
            if($res1 && $res2){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /*将商品放置回收站*/
    public function trashGoods($id){
        return $this->where("id=$id")->setField('isdel',0);
    }

    /*更新商品信息*/
    public function updateGoods($data){
        $id = intval(I('get.id'));
        //生成时间和货号
        $data['addtime'] = time();

        /*货号为空，不更新货号；货号不为空，检查是否重复；不重复，则使用更新后的货号*/
        if($data['goods_sn'] != ''){
            $goods_sn = $data['goods_sn'];

            $origin_sn_info = ($this->field('goods_sn')->find($id));
            $origin_sn = $origin_sn_info['goods_sn'];
            if($goods_sn == $origin_sn){
                $data['goods_sn'] = $goods_sn;
            }else{
                $info = $this->where('goods_sn='. "'$goods_sn'" .' AND id !=' . $id)->find();
                if($info){
                    $this->error = '货号与其他商品重复';
                    return false;
                }
            }
        }else{
            $data['goods_sn'] = 'JX'.uniqid();
        }

        //促销商品处理
        if($data['cx_price'] != '' && $data['cx_price']<0){
            $this->error = '促销商品价格不合法';
            return false;
        }elseif($data['cx_price'] == ''){
            $data['cx_price'] = 0.00;
        }
        $data['start'] = strtotime($data['start']);
        $data['end'] = strtotime($data['end']);
        //商品图片处理
        /*删除原来图片*/
        $this->deleteOriginImg($id);
        /*上传新图片*/
        if($path = $this->getPathAfterUpload($_FILES['goods_img'])){
            $data['goods_img'] = $path['goods_img'];
            $data['goods_thumb'] = $path['path'];
        }

        $res = $this->save($data);
        if(!$res){
            $this->error = '更新商品基本信息失败';
            return false;
        }

        //属性更新
        $g = D('GoodsAttr');
        $g->where('goods_id='.$id)->delete();
        $attr = I('post.attr');
        if($attr) {
            $res = $g->insertAttr($attr,$id);
            if(!$res){
                $this->error = '更新商品属性失败';
                return false;
            }
        }

        //更新扩展分类
        $ext_cate_ids = I('post.ext_cate_id');
        if($ext_cate_ids[0]>0){
            $res = D('GoodsCate')->updateExtCate($id,$ext_cate_ids);
            if(!$res){
                $this->error = '更新扩展分类失败';
                return false;
            }
        }

        //商品相册
        unset($_FILES['goods_img']);
        $config = array(
            'maxSize' => 3145728,
            'exts' => array('jpg', 'gif', 'png', 'jpeg')
        );
        $upload = new \Think\Upload($config);
        $info = array();
        $info = $upload->upload();
        if($info){
            foreach ($info as $key => $value){
                //原图地址
                $goods_img = 'Uploads/' . $value['savepath'] . $value['savename'];

                //缩略图生成
                $img = new \Think\Image();
                $img->open($goods_img);
                $thumb_path = 'Uploads/' . $value['savepath'] . 'thumb_' . $value['savename'];
                $img->thumb(450,450)->save($thumb_path);
                $list[] = array(
                    'goods_id' => $id,
                    'goods_img' => $goods_img,
                    'goods_thumb' => $thumb_path
                );
            }
            $res = M('GoodsImg')->addAll($list);
            if(!$res){
                $this->error = '更新商品相册失败';
                return false;
            }
        }
        return true;
    }

    /*图片上传返回原图和缩略图地址信息*/
    public function getPathAfterUpload($file){
        if(!empty($file['name'])){
            $config = array(
                'maxSize' => 3145728,
                'exts' => array('jpg', 'gif', 'png', 'jpeg')
            );

            $upload = new \Think\Upload($config);
            $info = $upload->uploadOne($_FILES['goods_img']);

            if(!$info){
                $this->error = $upload->getError();
                return false;
            }

            $goods_img = 'Uploads/' . $info['savepath'] . $info['savename'];

            //缩略图生成
            $img = new \Think\Image();
            $img->open($goods_img);
            $path = 'Uploads/' . $info['savepath'] . 'thumb_' . $info['savename'];
            $img->thumb(450,450)->save($path);
            return array(
                'goods_img' => $goods_img,
                'path' => $path
            );
        }
        return false;
    }

    public function getRecGoods($type){
        return $this->where("is_sale=1 and isdel=1 and goods_number>0 and $type=1")->limit(5)->select();
    }

    public function getCrazyGoods(){
        $where = "is_sale=1 and isdel=1 and cx_price>0 and start<".time()." and end>".time();
        return $this->where($where)->limit(5)->select();
    }

    //根据分类ID标识获取对应的商品信息
    public function getList($cate_id,$limit=8)
    {
        //1、获取当前分类下面子分类信息
        $children = D('Admin/Category')->getSubTree($cate_id);
        //2、将当前分类的标识追加到对应的子分类中
        $children[]['id']=$cate_id;
        //3、将$children格式化为字符串的格式目的就是为了使用MySQL中的in语法
        foreach ($children as $key => $value){
            $list[] = intval($value['id']);
        }
        $children = implode(',',$list);
        //4、通过目前的分类信息获取商品数据
        $where = "is_sale=1 and cate_id in ($children)";

        $sort = I('get.sort') ? I('get.sort') : 'sale_number';

        $goods_info = $this->field('max(shop_price) max_price,min(shop_price) min_price,count(id) 
        goods_count,group_concat(id) goods_ids')
            ->where($where)->find();


        if($goods_info['goods_count']>1){
            $cha = $goods_info['max_price']-$goods_info['min_price'];
            if($cha<100){
                $sec = 1;
            }elseif ($cha<500){
                $sec = 2;
            }elseif ($cha<1000){
                $sec = 3;
            }elseif ($cha<5000){
                $sec = 4;
            }elseif ($cha<10000){
                $sec = 5;
            }else{
                $sec = 6;
            }
            $price = array();
            $first = floor($goods_info['min_price']);
            $zl = ceil($goods_info['max_price']);
            for($i = 0;$i<$sec;$i++){
                $price[] = $first . '-' . ($first+$zl);
                $first +=$zl;
            }
        }

        //价格区间筛选
        if(I('get.price')){
            $tmp = explode('-',I('get.price'));
            $where .= ' and shop_price>' . $tmp[0] . ' and shop_price<' . $tmp[1];
        }

        //属性筛选
        if($goods_info['goods_ids']){
            $attr = M('GoodsAttr')->alias('a')->field('distinct a.attr_id,a.attr_values,b.attr_name')
                ->join('left join __ATTR__ b on a.attr_id=b.id')->where("a.goods_id in ({$goods_info['goods_ids']})")
                ->select();
            foreach ($attr as $key => $value){
                $attrwh[$value['attr_id']][] = $value;
            }
        }

        if(I('get.attr')){
            $attrParam = explode(',',I('get.attr'));
            $goods = M('GoodsAttr')->field('group_concat(goods_id) goods_ids')->where(array('attr_values'=>
            array('in',$attrParam)))->find();
            if($goods['goods_ids']){
                $where .= " and id in ({$goods['goods_ids']})";
            }
        }
        $count = D('Goods')->where($where)->count();
        $pagesize = 1;
        $page = new \Think\Page($count,$pagesize);
        $show = $page->show();
        $p = intval(I('get.p'));
        $goods = D('Goods')->where($where)->page($p,$pagesize)->order($sort.' desc')
            ->limit($limit)->select();
        return array('list'=>$goods,'page'=>$show,'price'=>$price,'attrwhere'=>$attrwh);
    }
}