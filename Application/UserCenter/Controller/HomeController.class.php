<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace UserCenter\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		//$this->redirect('Index/index');
        echo "<h1>404 Not Found</h1>";
	}


    protected function _initialize(){
       	is_login() || $this->error('Please Login First', U('Home/User/login'));
        $user = M('Member')->where(array('uid'=>is_login()))->find();
        $this->assign('_user',$user);
    }

    public function setBottomColumns()
    {
        $Probe = M('Probe');
        $Task = M('Task');
        $mid = is_login();
        if(empty($mid))
            $this->error("You should login firstly.",U("Home/Index/index"));
        $probes = $Probe->where(array('mid'=>$mid))->order('updated desc')->page(1,7)->select();
        //$this->assign('_probes',$probes);
        $score_infos = M('ScoreLog')->where(array('mid'=>$mid))->order('createtime desc')->page(1,7)->select();
        $scores = array_map(function($e){
            $result['op'] = ($e['plus']==1)?'+':'-';
            $result['score'] = $e['score'];
            $task = M('Task')->where(array('tid'=>$e['tid']))->find();
            $result['time'] = $e['createtime'];
            $srcs = json_decode($task['src'],TRUE);
            $srcs = array_map(function($src){
                $src = M('Probe')->where(array('pid'=>$src))->find();
                if($src)
                    return $src['name'];
                else
                    return '';
            },$srcs);
            $srcs = implode(',',$srcs);
            if($result['op'] == '-')
                $result['info'] ="use probe $srcs to complete ".$task['type'].' task';
            else
            {
                $member = M('Member')->where(array('uid'=>$task['mid']))->find();
                $result['info'] = $member['nickname'].' use your probe to perform '.$task['type']." task.";
            }
            return $result;
        },$score_infos);
        $score = M('Member')->where(array('uid'=>$mid))->find();
        $score = $score['score'];
        $shares = $Task->where(array('mid'=>array('neq',$this->uid),'public'=>1))->page(1,6)->order('starttime desc')->select();  
        $shares = array_map(function($e){ 

            $result = array();
            $member = M('Member')->where(array('uid'=>$e['mid']))->find();
            $e['arg'] = json_decode($e['arg'],TRUE);
            $e['src'] = json_decode($e['src'],TRUE);
            $srcs = array_map(function($src){
                $probe = M('Probe')->where(array('pid'=>$src))->find();
                return $probe['name'];
            },$e['src']);
            $dest = str_replace(' ',',',$e['arg']['dest']);
            $result['info'] = $member['nickname'].' operate '.$e['type'].' task to '.$dest.' use probe '.implode(',',$srcs).'.';
            $result['time'] = $e['endtime'];
            return $result;
        },$shares);
        $this->assign('_btn_probes',$probes);
        $this->assign('_shares',$shares);
        $this->assign('_score',$score);
        $this->assign("_scores",$scores);
    }

    public function setTabsCount()
    {
        $mid = is_login();
        $task_count = M("Task")->where(array('mid'=>$mid))->count();
        $probe_count = M('Probe')->where(array('mid'=>$mid))->count()+M('NewProbe')->where(array('mid'=>$mid))->count();
        $profile_count = M('Task')->where(array('mid'=>$mid,'persistent'=>1))->count();
        $this->assign('_task_count',$task_count);
        $this->assign('_probe_count',$probe_count);
        $this->assign('_profile_count',$profile_count);
    }


    public function shareData()
    {
        $tids = I('tids');
        $tids = explode(' ',trim($tids));
        $Task = M('Task');
        $flag = TRUE;
        foreach ($tids as $tid)
        {
           $task = $Task->where(array('tid'=>$tid))->find(); 
           $task['public'] = 1;
           if(!$Task->save($task))
           {
                $flag = FALSE;
                break;
           }
        }
        if($flag)
            $this->ajaxReturn("success");
        else
            $this->ajaxReturn("failed");
    }


    public function getCategories(){
        $channels = M('Channel')->order("sort asc")->select();
        $channels = array_map(function($channel)
        {
            $name = $channel['title'];
            $pcategory = M('Category')->where(array('name'=>$name))->find();
            $channel['sub'] = M('Category')->where(array('pid'=>$pcategory['id']))->select();
            return $channel;
        },$channels);
        $this->assign('channels',$channels);
    }
}
