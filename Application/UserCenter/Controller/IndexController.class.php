<?php
namespace UserCenter\Controller;
use Probe\Util\ProbeTaskUtil;
class IndexController extends HomeController
{
    private $util = NULL;
    private $model = NULL;
    private $uid;
    public function __construct()
    {
        parent::__construct();
        $this->util = new ProbeTaskUtil();
        $this->model = D('Task');
        $this->uid = is_login();
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
            $user = M('Member')->where(array('uid'=>$this->uid))->find();
            $this->assign('_user',$user);
            $this->display();
        }
    }

    public function search()
    {
        //搜索参数字段名 暂定为search_args
        if(IS_POST)
        {
            $num = 0;
            $search_args = I('post.search_args');
            //组合搜索时，个字段用分号分割
            $search_args = explode(';',$search_args);
            $groups = M('ProbeGroup')->field('gid,name')->select();
            $names = array_map(function($e){
                return $e['name'];
            },$groups);
            $categorys = array();
            foreach($search_args as $key=>$value)
            {
                //TODO:搜索
                $value = preg_replace('/\s+/',' ', $value);
                $value = preg_replace('/[^0-9a-zA-Z ,]/',',', $value);
                if(in_array($value,$names))
                {
                    $gid = NULL;
                    foreach($groups as $group)
                    {
                        if($group['name']==$value)
                        {
                            $gid = $value['gid'];
                            break;
                        }
                    }
                    $probes = M('Probe')->where(array('gid'=>$gid))->select();
                }
                elseif(byr_checkip($value)==4)
                {
                    $probes = M('Probe')->where(array('ip'=>byr_pton($value)))->select();
                }

                elseif(is_numeric($value))
                {
                    $probes = M('Probe')->where(array('as_number'=>$value))->select();
                }
                else
                {
                    //probe 表中应加入一个search_key字段
                    $probes = M('Probe')->where("search_key like '%$value%'")->select();
                }
                if(isset($probes))
                {
                    array_push($categorys,array('arg'=>$value,'probes'=>$probes));
                    $num += count($probes);
                }
            }
            $user = M('Member')->where(array('uid'=>$this->uid))->find();
            $this->assign('_user',$user);
            $this->assign('_categorys',$categorys);
            $this->assign('_num',$num);
            $page =ceil(count($search_args)/5);
            $this->assign('_page',$page);
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
            $this->assign('_selected_probes',$selectedProbes);
            $args = $this->util->getArgs('ping','Home');
            $this->assign('_args',$args);
            $this->assign('_types',$legal_types);
            $this->assign('_default','Ping');
            //设置表单token
            $token = md5(microtime(true));
            session('token',$token);
            $this->assign('_token',$token);
            $this->setBottomColumns();
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
        
        if(IS_POST)
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
                    redirect(U('Index/run'));
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
                $this->error("Operation Failed");
                echo("Operation failed");
            }
        }
        else
        {
            $this->setBottomColumns();
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
                              $result['probes'] = implode(',',$resutl['probes']);
                            $tmp = $result['type']." task to ".$result['dest']." using ".$result['probes']." start...";
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
                $Probe = M('Probe');
                $reportUtil = new \Probe\Util\ReportUtil();
                foreach($tids as $tid)
                {
                    $task = $Task->where(array('tid'=>$tid))->find();
                    if($task)
                    {
                        $task['arg'] = json_decode($task['arg'],TRUE);
                        $task['arg']['dest'] = explode(' ',$task['arg']['dest']);
                        $srcs = json_decode($task['src'],TRUE);
                        $task['src'] = array_map(function($pid) use ($Probe){
                            $probe = $Probe->where(array('pid'=>$pid))->find();
                            if($probe)
                               return $probe['name']; 
                        },$srcs);
                       $task['report'] = $reportUtil->getReport($task); 
                        array_push($tasks,$task);
                    }
                    else
                    {
                        $this->error("Database Error");
                    }
                }
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
        $this->setBottomColumns();
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
                $task = M('Task')->where(array('tid'=>$tid))->find();
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

}
?>
