<?php
//  Project name Q-Buy
//  Created by window on 17/5/20.
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

namespace app\admin\controller;
use think\Request;
use think\Db;
use app\admin\model\Goods;
class Article extends Base
{

	public function articleList ()
	{
		$list = Db::name('article')->paginate(10);
		$this->assign(['list' => $list ]);
		return $this->fetch();
	}

	//删除公告
	public function articleDelete ()
	{
		$article_id = input('post.article_id');
		$res = Db::name('article')->delete($article_id);
		return $res;
	}

	//处理公告的请求
	public function dealAction ()
	{
		$article_id = input('post.article_id');
		$action = input('post.action');
		$this->setAdminLog('修改公告状态');
		switch ($action) {
			case 'open':
				$res = Db::name('article')->update(['is_open' => 1,'article_id' => $article_id]);
				break;
			case 'noopen':
				$res = Db::name('article')->update(['is_open' => 0,'article_id' => $article_id]);
				break;
		}
		return $res;
	}

	//编辑公告
	public function editArticle ()
	{
		$article_id = input('get.article_id');
		$res = Db::name('article')->where(['article_id' => $article_id])->find();
		$this->assign(['info' => $res]);
		return $this->fetch();
	}

	//新增公告
	public function addArticle ()
	{

		return $this->fetch();
	}


	//处理编辑公告的form表单请求
	public function dealEditArticle() {
		$data = input('post.');
		$res = Db::name('article')->update($data);
		if ($res == 0 || $res == 1) {
			$this->setAdminLog('修改公告内容');
			$this->success('修改公告成功',url('article/articleList'));
		} else {
			$this->error('修改公告失败');
		}
	}

	//处理增加公告的form表单请求
	public function dealaddArticle() {
		$data = input('post.');
		$data['publish_time'] = time();
		$res = Db::name('article')->insert($data);
		if ($res == 0 || $res == 1) {
			$this->setAdminLog('增加公告');
			$this->success('增加公告成功',url('article/articleList'));
		} else {
			$this->error('增加公告失败');
		}
	}


	//遍历用户的评论
	public function comment ()
	{
		$res = Db::name('comment')->paginate(20);
		$this->assign(['list' => $res]);
		return $this->fetch();
	}

	//查看用户的评论
	public function showComment ()
	{
		$comment_id = input('get.id');
		$res = Db::name('comment')->where('comment_id='.$comment_id)->find();
		$res2 = Db::name('comment')->where('parent_id='.$comment_id)->select();
		$this->assign(['info' => $res,'info2' => $res2]);
		return $this->fetch();
	}

	//管理员回复用户的评论
	public function replyComment ()
	{
		$data = input('post.');
		$data['parent_id'] = $data['comment_id'];
		$data['email'] = 'Notice@wnphp.cn';
		$data['username'] = 'Q-Buy快购官方';
		$data['img'] = '__STATIC__/images/kuaigou.png';
		$data['add_time'] = time();
		unset($data['comment_id']);
		$reg = Db::name('comment')->insert($data);
		$res = Db::name('sysmsg')->insert(['message' => '商家给您的评论回复了~快去查看吧','uid' => $data['user_id'],'type' => 0]);
		if ($res && $res) {
			$this->setAdminLog('回复用户评论');
			$this->success('回复用户成功',url('article/comment'));
		} else {
			$this->error('回复用户失败');
		}
	}

	//删除用户的评论
	public function delCommon ()
	{
		$common_id = input('get.id');
		$this->setAdminLog('删除用户评论');
		Db::name('comment')->delete($common_id);
	}

}

