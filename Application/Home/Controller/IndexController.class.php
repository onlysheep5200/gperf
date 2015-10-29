<?php
namespace Home\Controller;
use Probe\Util\ProbeTaskUtil;
class IndexController extends HomeController
{
    private $util = NULL;
    private $model = NULL;
    public function __construct()
    {
        parent::__construct();
        $this->util = new ProbeTaskUtil();
        $this->model = D('Task');
    }

    public function index() 
    {
        // $Channel = M('Channel');
        // $main_channels = $Channel->field('id,title,url')->where(array('pid'=>0))->select();
        // $main_channels = array_map(function ($e){
        //     $subs = $Channel->field('title,rul')->where(array('pid'=>$e['id']))->select();
        //     $e['subs']=$subs;
        //     return $e;
        // },$main_channels); 
        // $this->assign('_channels',$main_channels);
        // $this->display();
        session('mul_task_id',NULL);
        if(IS_AJAX)
        {
            $result = array();
            $arg = I('arg');
            $arg = explode(";", $arg);
            $arg = $arg[count($arg)-1];
            $args = explode(" ", $arg);
            // $probes = M('Probe')->where("search_key like '%$arg%'")->select();
            foreach ($args as $key => $value) {
                # code...
                $probes = M('Probe')->where("search_key like '%$arg%'")->select();
                $probes = array_map(function($e){
                    return $e['search_key'];
                }, $probes);
                $result = array_merge($result,$probes);
                $result = array_unique($result);
            }
            if(empty($result))
                $this->ajaxReturn("none");
            // $probes = array_map(function($e){
            //     return $e['search_key'];
            // }, $probes);
            $this->ajaxReturn($result);
        }
        else
        {
            $uid = is_login();
            $this->assign('_uid',$uid);
            $this->getCategories();
            $this->display();
        }
    }

    public function search()
    {
        //搜索参数字段名 暂定为search_args
        if(IS_AJAX)
        {
            $selected_str = trim(I('pids'));
            $selected =  explode(' ',$selected_str);
            session('selected_probes',$selected);
        }
        elseif(IS_POST)
        {
			$continents = ['AF'=>'africa','EU'=>'europe','AS'=>'asia','OA'=>'oceania','NA'=>'north america','SA'=>'south america','AN'=>'antarctica'];
            session('selected_probes',NULL);
            $num = 0;
            $search_args = I('post.search_args');
            //组合搜索时，个字段用分号分割
            $search_args = array_unique(explode(';',$search_args));
            $groups = M('ProbeGroup')->select();
            $names = array_map(function($e){
                return $e['name'];
            },$groups);
            $categorys = array();
            $shortcuts = file_get_contents('/var/www/html/Public/static/country_shortcut.json');
            $shortcuts = json_decode($shortcuts,True);
            $countryNames = file_get_contents('/var/www/html/Public/static/country_names.js');
            $countryNames = json_decode($countryNames,True);
            foreach($search_args as $key=>$value)
            {
                //TODO:搜索
                $value = preg_replace('/\s+/',' ', $value);
               // $value = preg_replace('/[^0-9a-zA-Z ,\.]/',',', $value);
                
                if(in_array($value,$names))
                {
                    $gid = -1;
                    foreach($groups as $group)
                    {
                        if($group['name']==$value)
                        {
                            $gid = $group['gid'];
                            break;
                        }
                    }
                    $probes = M('Probe')->where(array('gid'=>$gid,'status'=>1))->select();
                }
                elseif($value == '*')
                {
                    $probes = M('Probe')->select();
					$opens = M('OpenProbe')->select();
                    if(empty($probes))
                        $probes = array();
                    if(empty($opens))
                        $opens = array();
                    else{
					    $opens = array_map(function($e){
						    $e['opensource'] = True;
						    return $e;
					    },$opens);
                    }
					$probes = array_merge($probes,$opens);
                }
                elseif(byr_checkip($value)==4)
                {
                    //$probes = M('Probe')->where(array('ip'=>$value,'status'=>1))->select();
                    $probes = M('Probe')->where(array('ip'=>$value,'status'=>1))->select();
					$opens = M('OpenProbe')->where(array('ip'=>$value,'status'=>1))->select();
                    if(empty($probes))
                        $probes = array();
                    if(empty($opens))
                        $opens = array();
                    else{
					    $opens = array_map(function($e){
						    $e['opensource'] = True;
						    return $e;
					    },$opens);
                    }
                    $probes = array_merge($probes,$opens);

                }
                elseif(is_ip_prefix($value))
                {
                    $len = strlen($value);
                    $last = substr($value,$len-1,1);
                    $tmp = $value;
                    if($last != '.')
                        $tmp .= '.';
                    $probes = M('Probe')->where("ip like '$tmp%' and status=1")->select();
                    $opens = M('OpenProbe')->where("ip like '$tmp%' and status =1")->select();
                    if(empty($probes))
                        $probes = array();
                    if(empty($opens))
                        $opens = array();
                    else{
					    $opens = array_map(function($e){
						    $e['opensource'] = True;
						    return $e;
					    },$opens);
                    }
                    $probes = array_merge($probes,$opens);
                }
                elseif(is_numeric($value))
                {
                    $probes = M('Probe')->where(array('as_number'=>$value,'status'=>1))->select();
					$opens = M('OpenProbe')->where(array('as_number'=>$value,'status'=>1))->select();
                    if(empty($probes))
                        $probes = array();
                    if(empty($opens))
                        $opens = array();
                    else{
					    $opens = array_map(function($e){
						    $e['opensource'] = True;
						    return $e;
					    },$opens);
                    }
					$probes = array_merge($probes,$opens);
                }
                elseif(!empty($continents[strtoupper($value)]) || in_array(strtolower($value),array_values($continents)))
                {
                    $continent = $continents[strtoupper($value)];
					if(empty($continent))
						$continent = $value;
                    $opens = M('OpenProbe')->where("continent like '$continent' and status=1")->select();
                    $probes = M('Probe')->where("continent like '$continent' and status=1")->select();
                    if(empty($probes))
                        $probes = array();
                    if(empty($opens))
                        $opens = array();
                    $opens = array_map(function($e){
                        $e['opensource'] = True;
                        return $e;
                    },$opens);
                    $probes = array_merge($probes,$opens);
                }
                elseif(!empty($shortcuts[strtoupper($value)]))
                {
                    $country = $shortcuts[strtoupper($value)];
                    $opens = M('OpenProbe')->where(array('country'=>$country,'status'=>1))->select();
                    $probes = M('Probe')->where(array('country'=>$country,'status'=>1))->select();
                    if(empty($probes))
                        $probes = array();
                    if(empty($opens))
                        $opens = array();
                    $opens = array_map(function($e){
                        $e['opensource'] = True;
                        return $e;
                    },$opens);
                    $probes = array_merge($probes,$opens);
                }
                elseif(in_array(strtolower($value),$countryNames))
                {
                    $country = $value;
                    $opens = M('OpenProbe')->where("country like '$country' and status=1")->select();
                    $probes = M('Probe')->where("country like '$country' and status=1")->select();
                    if(empty($probes))
                        $probes = array();
                    if(empty($opens))
                        $opens = array();
                    $opens = array_map(function($e){
                        $e['opensource'] = True;
                        return $e;
                    },$opens);
                    $probes = array_merge($probes,$opens);
                }
                else
                {
                    $probes = M('Probe')->where("search_key like '%$value%' and status=1")->select();
                    if(!$probes)
                        $probes = array();
					$opens = M('OpenProbe')->where("search_key like '%$value%' and status=1")->select();
                    if(empty($opens))
                        $opens = array();
                    else
                    {
					$opens = array_map(function($e){
						$e['opensource'] = True;
						return $e;
					},$opens);
                    }
					$probes = array_merge($probes,$opens);
                }
                if(isset($probes))
                {
                    /*$probes = array_map(function($p){
                        $mid = $p['mid'];
                        $mem = M('Member')->where(array('uid'=>$mid))->field('nickname')->find();
                        $p['mid'] = $mem['nickname'];
                        return $p;
                    },$probes);
                     */
                    array_push($categorys,array('arg'=>$value,'probes'=>$probes));
                    $num += count($probes);
                    }
                }
            $countrys = M('Probe')->field('country')->where(array('status'=>1))->distinct(true)->select();
            $countrys = array_map(function($c){
                $result = array();
                $name = $c['country'];
                $organizations = M('Probe')->field('organization')->distinct(true)->where(array('country'=>$name,'status'=>1))->select();
                $result['name'] = $name;
                $result['organizations'] = $organizations;
                return $result; 
            },$countrys);
            $this->assign('_base_levels',$countrys);
            $this->assign('_categorys',$categorys);
            $this->assign('_num',$num);
            $page =ceil(count($search_args)/5);
            $this->assign('_page',$page);
            $this->assign('_uid',is_login());
            $this->getCategories();
            $this->display();
        }
        else
        {
            $this->_empty();
        }
    }
    
    public function configure()
    {
       
        session('mul_task_id',NULL);
        $legal_types = M('Tasktype')->field('type,name')->select();
        $legal = array_map(function($v){
             return $v['type'];
        },$legal_types);
        if(IS_AJAX)
        {
            $type = I('type');
            if(in_array($type, $legal))
                $this->ajaxReturn($this->util->getArgs($type,"Home")); 
            else
                $this->ajaxReturn('none');
        } 
       /* elseif(IS_POST)
        {
            if($this->addTask())
            {
                $this->startTask();
                $this->redirect(U('Index/run','',''));
            }
            else
            {
                $this->undo();
                $this->error("Operation Failed");
            }
        }*/
        else
        {
            $pids = explode(' ',trim(I('values')));
            $args = '';
            $selectedProbes = array();
            foreach ($pids as $key => $value) {
                # code...
                $probe = M("Probe")->where(array('pid'=>$value))->find();
                if(!is_null($probe))
                    array_push($selectedProbes,$probe);
            }
            $this->assign('_probes',$selectedProbes);
            $args = $this->util->getArgs('ping','Home');
            $this->assign('_args',$args);
            $this->assign('_types',$legal_types);
            $this->assign('_default','Ping');
            //设置表单token
            $token = md5(microtime(true));
            session('token',$token);
            $this->assign('_token',$token);
            $this->assign('_uid',is_login());
            $this->getCategories();
            $this->display();
        }
        // $this->assign('_args',$args);
        // if(is_null($type))
        // {
        //     $this->assign('_type','ping');
        // }
        // $this->display();
    }

    public function beforeComplete()
    {
        if(IS_POST)
        {

            $token = session('token');
            if($token == I('token'))
            {
                echo $this->addTask();
            }
            else
                return;
        }
    }



    public function run()
    {
        if(!session('guid'))
			echo "<h1>Create a task first</h1>";
        else if(IS_POST)
        {
            $pids = (array)I('pid');
            $token = I("post.token");
            if($token != session('token'))
                redirect(U('Index/configure'),3,"Do not try to submit data repeatedly");
            if(($id=$this->addTask()))
            {
                // $this->redirect(U('Home/Index/run'));
                $continue = I('continue');
                if(empty($continue))
                {
                    $this->startTask();
                    session('token','');
                    redirect(U('Index/result'));
                }
                else
                {
                    echo "Continue $id";
                }
            }
            else
            {
                $this->undo();
                session('add_task_failed','1');
                $this->error("Something Error: Invalid Parameter",U('index'));
                echo("Operation failed");
            }
        }
        else
        {
            $this->assign('_uid',is_login());
            $this->display();
        }
    }

    public function checkTasks()
    {
        if(IS_AJAX)
        {
            $arg = I('arg');
            $Task = M('Task');
            $id = session('mul_task_id');
            if($id)
            {
                $mul = M('MulTask')->where(array('id'=>$id))->find();
                $tids = explode(',',$mul['tids']);
                if(!is_array($tids))
                    $tids = array($tids);
                if($arg == 'init')
                {
                    $infos = array();
                    foreach($tids as $tid)
                    {
                        $tid = intval($tid);
                        $result = $this->util->getTaskInfo($tid);
                        if($result)
                        {
                            if(is_array($result['probes']))
                              $result['probes'] = implode(',',$result['probes']);
                            $tmp = $result['type']." task to ".$result['dest']." using probe ".$result['probes']." start...";
                            array_push($infos,$tmp);
                        }
                        else
                        {
                            $this->ajaxReturn("failed to get task info");
                        }
                    }
                    $this->ajaxReturn($infos);
                    session('request_time',1);
                }
                $results = array();
                $complete = TRUE;
                foreach($tids as $tid)
                {
                    $result = $this->util->isTaskComplete($tid);
                    $task = $Task->where(array('tid'=>$tid))->find();
                    $arg = json_decode($task['arg'],TRUE);
                    if($result == 'finish')
                    {
                        $rtn = $task['type'].' task to '.$arg['dest'].' has been finished'; 
                        array_push($results,$rtn);
                    }
                    elseif($result == 'doing')
                    {
                        $complete = FALSE;
                        $time = session('request_time');
                        if($time == 1)
                        {
                            $rtn = $task['type'].' task to '.$arg['dest'].' is being performed...';
                        }

                    }
                    elseif($result == false)
                    {
                        $complete = FALSE;
                        $rtn = $task['type']." task to ".$arg['dest']." is failed";
                        array_push($results,$rtn);
                    }

                }
                if($complete)
                {
                    $this->ajaxReturn(array('complete'=>'true','result'=>$results));
                }
                else
                {
                    $this->ajaxReturn(array('complete'=>'false','result'=>$results));
                }
            }
            else
            {
                $this->ajaxReturn("no task");
            }
        } 
    }


    public function result()
    {
        $id = session('mul_task_id');
        if($id)
        {
            $mul = M("MulTask")->where(array('id'=>$id))->find();
            if($mul)
            {
                $tids = explode(',',$mul['tids']);
                $tasks = array();
                $Task = M('Task');
                $types = array();
                $Probe = M('Probe');
                $reportUtil = new \Probe\Util\ReportUtil();
                foreach($tids as $tid)
                {
                    $task = $Task->where(array('tid'=>$tid))->find();
                    array_push($types,$task['type']);
                    if($task)
                    {
                        $task['arg'] = json_decode($task['arg'],TRUE);
                       // $task['arg']['dest'] = explode(' ',$task['arg']['dest']);
                        $dests = explode(' ',$task['arg']['dest']);
                        $srcs = json_decode($task['src'],TRUE);
                        /*$srcs = json_decode($task['src'],TRUE);
                        $task['src'] = array_map(function($pid) use ($Probe){
                            $probe = $Probe->where(array('pid'=>$pid))->find();
                            if($probe)
                               return $probe['name']; 
                        },$srcs);*/
                        $reports = $reportUtil->getReport($task); 
                        $pTasks = array();
                        foreach($srcs as $src)
                        {
                            $probe = M('Probe')->field('name')->where(array('pid'=>$src))->find();
                            $pt['pid'] = $src;
                            $pt['pname'] = $probe['name'];
                            $pt['tid'] = $task['tid'];
                            $pt['starttime'] = $task['starttime'];
                            $pt['endtime'] = $task['endtime'];
                            $pt['dests'] = $dests;
                            $pt['status'] = $task['status'];
                            $pt['tid'] = $task['tid'];
                            array_push($pTasks,$pt);
                        }
                        $tasks[$task['type']] = $pTasks;

                    }
                    else
                    {
                        $this->error("Database Error");
                    }
                }
                $this->getCategories();
                $this->assign('_types',$types);
                $this->assign('_tasks',$tasks);
            }
            else
            {
                $this->error('Database Error!',U('Index/index'));
            }
        }
        else
        {
            $this->error("You should create a task first");
        }
        $this->assign('_uid',is_login());
        $this->display();
    }



    public function addTask()
    {
        $MulTask = M('MulTask');
        $id = session('mul_task_id');
        if(!empty($id))
        {
            $mul = $MulTask->where(array('id'=>$id))->find();
            $tid = $this->util->addTask($_POST);
            if($tid)
             {   
                 $mul['tids'].=",$tid";
                 return $MulTask->save($mul);
             }
            else
            {
                return false;
            }
        }
        else
        {
            $mid = is_login();
            $mul = array();
            if($mid)
            {
                $mul['mid'] = $mid;
            }
            $mul['guid'] = session('guid');
            $mul['createtime'] = Date("Y-m-d H:i:s",time());
            $tid = $this->util->addTask($_POST);
            if($tid)
            {
                $mul['tids']= $tid;
                $id=$MulTask->add($mul);
                session('mul_task_id',$id);
                $task = M("Task")->where(array('tid'=>$tid))->find();
                $task['mtid'] = $id;
                M('Task')->save($task);
                return $id;
            }
            else
                return false;
        }
    }

    public function undo()
    {

        $id = session('mul_task_id');
        if(empty($id))
            return;
        $MulTask = M('MulTask');
        $Task = M('Task');
        $guid = session('guid');
        $mul = $MulTask->where(array('id'=>$id))->find();
        if($mul)
        {
            $tids = explode(',',$mul['tids']);
            if(!is_array($tids))
                $tids = array($tids);
            foreach($tids as $tid)
            {
                $Task->where(array('tid'=>$tid))->delete();
            }
            $MulTask->where(array('id'=>$mul['id']))->delete();
            session("mul_task_id",NULL);
        }
    }

    public function startTask()
    {
        $id = session('mul_task_id');
        if($id)
        {
            $MulTask = M('MulTask');
            $mul = $MulTask->where(array('id'=>$id))->find();
            $tids = explode(',',$mul['tids']);
            if(!is_array($tids))
            {
                $tids = array($tids);
            }
            foreach($tids as $tid)
            {
                $this->util->quickDo($tid);
            }
        }
    }

    public function getTaskDetails($tid)
    {
        
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

    public function getProbeTaskReport($pid,$tid)
    {
        $file = fopen('/tmp/report.txt','a+');
        fwrite($file,"tid is $tid and pid is $pid\n");
        if(IS_AJAX)
        {
            $mul_task_id = session('mul_task_id');
            if(empty($mul_task_id))
                $this->ajaxReturn('you shold create tasks firstly');
            $mul_task = M('MulTask')->where(array('id'=>$mul_task_id))->find();
            if($mul_task['mid']!=0 && $mul_task['mid'] != is_login())
            {
                $this->ajaxReturn("this is not your task");
            }
            elseif($mul_task_id['mid'] == 0)
            {
                $tids = explode(',',$mul_task['tids']);
                if(!in_array($tid,$tids))
                    $this->ajaxReturn('this is not your task');
            }
            $task = M('Task')->where(array('tid'=>$tid))->find();
            $type = $task['type'];
			$args = json_decode($task['arg'],True);
            $probe = M('Probe')->where(array('pid'=>$pid))->find();
            $reportUtil = new \Probe\Util\ReportUtil();
            $reports = $reportUtil->getReportByProbeTask($pid,$tid);
            $dests = array_map(function($report){
                return $report['dest'];
            },$reports);
            $title = ucfirst($type).' To '.implode(',',$dests);
            $subtitle = 'complete by '.$probe['name'].' status in '.$probe['country'].','.$probe['city'].','.$probe['organization'];
            $result = array(
                'type'=>$type,
                'title' =>$title,
                'subtitle' => $subtitle,
                'dests' =>$dests,
                'probe' => $probe['name'],
            );
            if($type == 'ping')
            {
                $delays = array_map(function($report){
                    return $report['delay'];
                },$reports);
                $losses = array_map(function($report){
                    return $report['lost'];
                },$reports);
                $result['delays'] = $delays;
                $result['losses'] = $losses;
            }
            elseif($type == 'dns')
            {
                foreach ($reports as $report)
                {
                    $key = $report['dest'];
                    $result[$key] = $report['addresses'];
                }
            }
            elseif($type == 'bandwidth')
            {
                $delays = array_map(function($report){
                    return $report['delay'];
                },$reports);
                $bandwidths = array_map(function($report){
                    return $report['bandwidth'];
                },$reports);
				$probe_id = $args['targetprobe'];
				if(!empty($probe_id))
				{
					$p = M('Probe')->where(array('pid'=>$probe_id))->find();
					$probe_id = $p['name'];
				}
				else
				{
					$probe_id = 'server';
				}
				$result['dests'] = array($probe_id,);
                $result['delays'] = $delays;
                $result['bandwidths'] = $bandwidths;
            }
            elseif($type == 'tracert')
            {
                foreach($reports as $report)
                {
                    $dest = $report['dest'];
                    $result[$dest] = $report['hips'];
                }
            }
            fclose($file);
            $this->ajaxReturn($result);
        }
    }

    public function checkStatus()
    {
        if(IS_AJAX)
        {
            $mul_id = session('mul_task_id');
            if(empty($mul_id))
                $this->ajaxReturn('no task exists');
            else
            {
                $mul = M('MulTask')->where(array('id'=>$mul_id))->find();
                $pid = I('pid');
                $tid = I('tid');
                $tids = explode(',',$mul['tids']);
                if(!in_array($tid.'',$tids))
                    $this->ajaxReturn("illegal task");
                $Task = M('Task');
                $task = $Task->where(array('tid'=>$tid))->field('starttime,endtime,status')->find();
                if($task['status'] == 'queueing')
                    $this->ajaxReturn('queueing');
                $result = array('tid'=>$tid,'pid'=>$pid,'starttime'=>$task['starttime'],'endtime'=>$task['endtime'],'status'=>$task['status']);
                $this->ajaxReturn($result);
                
            }
        }
    }
    
    public function getProbesByOrg()
    {
        if(IS_AJAX)
        {
            $selected = session('selected_probes');
            $org = I('org');
            $results = M('Probe')->where(array('organization'=>$org,'status'=>1))->field('pid,name,city,country,ip,organization,mid,running_status,position')->select(); 
            if(!empty($selected))
            {
                $results = array_map(function($r) use($selected){
                    if (in_array($r['pid'],$selected))
                    {
                        $r['checked'] = true;
                    } 
                    else
                        $r['checked'] = false;
                    return $r;
                },$results);
            }
            $this->ajaxReturn($results);
        }
        else
        {
            echo "hello world";
        }
    }

}
?>
