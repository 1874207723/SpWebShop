<?php
//  Project name Q-Buy
//  Created by window on 17/5/10.
//  Copyright © 2017年 worning. All rights reserved.

/**
 *　　　　　　　 ┏┓       ┏┓+ +
 *　　　　　　　┏┛┻━━━━━━━┛┻┓ + +
 *　　　　　　　┃　　　　　　 ┃
 *　　　　　　　┃　　　━　　　┃ ++ + + +
 *　　　　　　 █████━█████  ┃+
 *　　　　　　　┃　　　　　　 ┃ +
 *　　　　　　　┃　　　┻　　　┃
 *　　　　　　　┃　　　　　　 ┃ + +
 *　　　　　　　┗━━┓　　　 ┏━┛
 *               ┃　　  ┃
 *　　　　　　　　　┃　　  ┃ + + + +
 *　　　　　　　　　┃　　　┃　Code is far away from     bug with the animal protecting
 *　　　　　　　　　┃　　　┃ + 　　　　         神兽保佑 , 代码无bug
 *　　　　　　　　　┃　　　┃
 *　　　　　　　　　┃　　　┃　　+
 *　　　　　　　　　┃　 　 ┗━━━┓ + +
 *　　　　　　　　　┃ 　　　　　┣┓
 *　　　　　　　　　┃ 　　　　　┏┛
 *　　　　　　　　　┗┓┓┏━━━┳┓┏┛ + + + +
 *　　　　　　　　　 ┃┫┫　 ┃┫┫
 *　　　　　　　　　 ┗┻┛　 ┗┻┛+ + + +
 */

namespace app\index\controller;
use think\Db;

class Shop extends Base
{
    public function list()
    {


        $brandid = input('get.brand');

        //查询板块分类，1,2,3级。
        $level1 = Db::table('qb_goods_cate')->where('level', 1)->order('sort_order desc')->select();
        foreach($level1 as $value) {
            $data = Db::table('qb_goods_cate')->where('parent_id', $value['id'])->select();
            $dataLimit = Db::table('qb_goods_cate')->where('parent_id', $value['id'])->limit(4)->select();
            $level2[$value['id']] = $data;
            $level2Limit[$value['id']] = $dataLimit;
            foreach($data as $value2) {
                $data2 = Db::table('qb_goods_cate')->where('parent_id', $value2['id'])->limit(5)->select();
                $level3[$value2['id']] = $data2;
            }
        }
        $this->assign([
            'level1' => $level1,
            'level2' => $level2,
            'level3' => $level3,
            'level2Limit' => $level2Limit
            ]);

        //查询头部板块推荐。
        $hotCate = Db::table('qb_goods_cate')->where('is_hot', 1)->where('level', 'in', '2,3')->limit(8)->select();
        $this->assign('hotCate', $hotCate);

        //查询销量、新品、评价、价格。
        $salesum = !empty(input('get.salesum')) && input('get.salesum')=='1' ? (' asc') : (!empty(input('get.salesum')) && input('get.salesum')=='2' ? ' desc' : 0);
        $ontime = !empty(input('get.ontime')) && input('get.ontime')=='1' ? (' asc') : (!empty(input('get.ontime')) && input('get.ontime')=='2' ? ' desc' : 0);
        $commentcount = !empty(input('get.commentcount')) && input('get.commentcount')=='1' ? (' asc') : (!empty(input('get.commentcount')) && input('get.commentcount')=='2' ? ' desc' : 0);
        $this->assign(['salesum' => $salesum, 'ontime' => $ontime, 'commentcount' => $commentcount]);

        //得到cateid查询数据
        if (!empty(input('cateid'))) {

            $cateid = request()->param()['cateid'];
            $this->assign('cateid', $cateid);

            //判断是id为几级分类。
            $idCheck = Db::table('qb_goods_cate')->field('level,name,id,parent_id,parent_id_path')->where('id', $cateid)->find();

            //取得父级id，name。
            if ($idCheck) {
                $parent = explode('_', $idCheck['parent_id_path']);
                if (!empty($parent[1])) {
                    $parent1 = Db::table('qb_goods_cate')->field('id,name')->where('id', $parent[1])->find();
                    $parentData1 = Db::table('qb_goods_cate')->field('id,name')->where('parent_id', $parent1['id'])->select();
                    if (!empty($parentData1)) {
                        foreach ($parentData1 as $pd1first) {
                            $checkData1[$pd1first['id']] = $pd1first['name'];
                            $pd1second = Db::table('qb_goods_cate')->field('id,name')->where('parent_id', $pd1first['id'])->select();
                            if (!empty($pd1second)) {
                                foreach ($pd1second as $pd1third) {
                                    $checkData1[$pd1third['id']] = $pd1third['name'];
                                }
                            }
                        }
                        $checkData[1] = $checkData1;
                    }

                    //服务路径。
                    $this->assign('parent1', $parent1);
                }
                if (!empty($parent[2])) {
                    $parent2 = Db::table('qb_goods_cate')->field('id,name')->where('id', $parent[2])->find();
                    $parentData2 = Db::table('qb_goods_cate')->field('id,name')->where('parent_id', $parent2['id'])->select();
                    if (!empty($parentData2)) {
                        foreach ($parentData2 as $pd2first) {
                           $checkData2[$pd2first['id']] = $pd2first['name'];
                        }
                        $checkData[2] = $checkData2;
                    } else {
                        $checkData2 = $checkData1;
                        $checkData[2] = $checkData2;
                    }

                    //服务路径
                    $this->assign('parent2', $parent2);
                }
                if (!empty($parent[3])) {
                    $parent3 = Db::table('qb_goods_cate')->field('id,name,parent_id_path')->where('id', $parent[3])->find();
                    $checkData3 = $checkData2;
                    $checkData[3] = $checkData3;

                    //服务路径
                    $this->assign('parent3', $parent3);
                }

                //该id查询的所有分类的数组。以键值对形式存储。
                $checkReco = $checkData[$idCheck['level']];
                $this->assign('checkReco', $checkReco);

                //查询本品牌热销
                $recommendData = Db::table('qb_goods')->where('cat_id', 'in', array_keys($checkReco))->where('is_recommend', 1)->select();
                if (!empty($recommendData)) {
                    foreach($recommendData as $red) {
                        $recoData[] = $red['cat_id'];
                    }
                    $recoData = array_slice($recoData, 0, 8);
                    $this->assign('recoData', array_unique($recoData));
                }

                //查询大家都在选
                $hotData = Db::table('qb_goods')->field('cat_id,goods_name')->where('cat_id', 'in', array_keys($checkReco))->where('is_hot', 1)->limit(6)->select();

                //查询推荐商品
                $recommendGoods = Db::table('qb_goods')->field('goods_id,goods_name,comment_count,market_price,shop_price,original_img')->where('cat_id', 'in', array_keys($checkReco))->where('is_recommend', 1)->paginate(3);

                //查询品牌
                $brandData = Db::table('qb_brand')->field('id,logo')->where('cat_id',$parent[1])->order('sort desc')->limit(18)->select();
                $brandAll = Db::table('qb_brand')->where('cat_id',$parent[1])->order('sort desc')->select();

                //查询商品。价格区间
                $goods = Db::table('qb_goods')->field('goods_id,goods_name,comment_count,market_price,sales_sum,shop_price,original_img')->where('cat_id', 'in', array_keys($checkReco))->where('is_on_sale', 1);

                if (!empty($salesum)) {
                    $goods->order('sales_sum '.$salesum);
                }
                if (!empty($ontime)) {
                    $goods->order('on_time '.$ontime);
                }
                if (!empty($commentcount)) {
                    $goods->order('comment_count '.$commentcount);
                }
                if (!empty(input('get.priceF'))) {
                    $goods->where('shop_price', 'between', input('get.priceF').','.input('get.priceS'));
                    $this->assign('pricef', input('get.priceF'));
                    $this->assign('prices', input('get.priceS'));
                }
                $goods = $goods->paginate(12);

            }
        }


        //得到brand查询数据

        if (!empty(input('get.brand'))) {
            $brand = request()->param()['brand'];
            $this->assign('brand', $brand);

            //取得品牌信息
            $brandCheck = Db::table('qb_brand')->field('name,cat_id')->where('id', $brand)->find();
            $this->assign('brandCheck', $brandCheck);

            //取得品牌父板块信息
            $parent = Db::table('qb_goods_cate')->where('id', $brandCheck['cat_id'])->find();
            $this->assign('parent', $parent);

            //查询本品牌热销
            $recommendData = Db::table('qb_brand')->field('id,name')->where('id', $brandCheck['cat_id'])->select();
            if (!empty($recommendData)) {
                $recoData = array_slice($recommendData, 0, 8);
                $this->assign('recoData', $recoData);
            }

            //查询大家都在选
            $hotData = Db::table('qb_goods')->field('cat_id,goods_name')->where('brand_id', $brand)->where('is_hot', 1)->limit(6)->select();

            //查询推荐商品
            $recommendGoods = Db::table('qb_goods')->field('goods_id,goods_name,comment_count,market_price,shop_price,original_img')->where('brand_id', $brand)->where('is_recommend', 1)->paginate(3);

            //查询品牌
            $brandData = Db::table('qb_brand')->field('logo,id')->where('cat_id',$brandCheck['cat_id'])->order('sort desc')->limit(18)->select();
            $brandAll = Db::table('qb_brand')->where('cat_id',$brandCheck['cat_id'])->order('sort desc')->select();

            //查询商品。价格区间
            $goods = Db::table('qb_goods')->field('goods_id,goods_name,comment_count,market_price,sales_sum,shop_price,original_img')->where('brand_id', $brand)->where('is_on_sale', 1);

            if (!empty($salesum)) {
                $goods->order('sales_sum '.$salesum);
            }
            if (!empty($ontime)) {
                $goods->order('on_time '.$ontime);
            }
            if (!empty($commentcount)) {
                $goods->order('comment_count '.$commentcount);
            }
            if (!empty(input('get.priceF'))) {
                $goods->where('shop_price', 'between', input('get.priceF').','.input('get.priceS'));
                $this->assign('pricef', input('get.priceF'));
                $this->assign('prices', input('get.priceS'));
            }
            $goods = $goods->paginate(12);

        }

        $this->assign([
            'hotData' => $hotData,
            'brandData' => $brandData,
            'brandAll' => $brandAll,
            'goods' => $goods,
            'recommendGoods' => $recommendGoods
            ]);

        return $this->fetch();
    }

    public function details()
    {
        //查询板块分类，1,2,3级。
        $level1 = Db::table('qb_goods_cate')->where('level', 1)->order('sort_order desc')->select();
        foreach($level1 as $value) {
            $data = Db::table('qb_goods_cate')->where('parent_id', $value['id'])->select();
            $dataLimit = Db::table('qb_goods_cate')->where('parent_id', $value['id'])->limit(4)->select();
            $level2[$value['id']] = $data;
            $level2Limit[$value['id']] = $dataLimit;
            foreach($data as $value2) {
                $data2 = Db::table('qb_goods_cate')->where('parent_id', $value2['id'])->limit(5)->select();
                $level3[$value2['id']] = $data2;
            }
        }
        $this->assign([
            'level1' => $level1,
            'level2' => $level2,
            'level3' => $level3,
            'level2Limit' => $level2Limit
            ]);

        //查询头部板块推荐。
        $hotCate = Db::table('qb_goods_cate')->where('is_hot', 1)->where('level', 'in', '2,3')->limit(8)->select();
        $this->assign('hotCate', $hotCate);

        return $this->fetch();
    }

    public function checklevel($data)
    {
        $level2 = Db::table('qb_goods_cate')->where('parent_id', $data)->limit(5)->select();
        return json($level2);
    }

    public function goodsinfo ()
    {
        return $this->fetch();
    }

}

