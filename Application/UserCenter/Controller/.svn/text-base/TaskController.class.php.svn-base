<?php 	
namespace UserCenter\Controller;
class TaskController extends HomeController
{
    private $uid;
    public function __construct()
    {
        parent::__construct();
        $this->uid = is_login();
        $this->getCategories();
    }

	public function index()
    {
        $Task = M('Task');
        $sums = ceil($Task->count()/7);
        $cur_page = I('page');
        if(empty($cur_page))
            $cur_page = 1;
        else
        {
            if($cur_page >$sums || $cur_page <=0)
                $this->error('Illegal page number');
        }
        $mid = $this->uid;
        $tasks = $Task->where(array("mid=$mid"))->page($cur_page,7)->select();
        $tasks = array_map(function($task){
            $task['arg'] = json_decode($task['arg'],TRUE);
            $task['src'] = json_decode($task['src']);
            $task['srcnum'] = count($task['src']);
           return $task; 
        },$tasks);
        $member = M('Member')->where(array('uid'=>$this->uid))->find();
        $this->assign('_user',$member);
        $pages = ceil(($Task->where(array('mid'=>$this->uid))->count())/7);
        $this->assign('_tasks',$tasks);
        $this->assign('_pages',$pages);
        $this->assign('_cur_page',$cur_page);
        $user = M('Member')->where(array('uid'=>$this->uid))->find();
        $this->assign('_user',$user);
        $this->setBottomColumns();
        $this->setTabsCount();
		$this->display();
	}

    public function getTasksByPage()
    {
        if(IS_AJAX)
        {
            $Task = M('Task');
            $page = I('page');
            $sums = ceil(($Task->count())/7);
            if($page<=0 || $page>$sum)
                return 'false';
            else
            {
                $tasks = $Task->where(array('mid'=>$this->uid))->page($page,7)->select();
                $tasks = array_map(function($task){
                    $task['arg'] = json_decode($task['arg'],TRUE);
                    $task['src'] = json_decode($task['src'],TRUE);
                    $task = json_decode($task,TRUE);
                    return $task;
                },$tasks);
                $this->ajaxReturn($tasks);
            }
        }
    }

    public function detail($tid)
    {
        $tid = intval($tid);
        if($tid)
        {
            $task = M('Task')->where(array('tid'=>$tid))->find();
            if($task['mid'] == $this->uid)
            {
                $task['arg'] = json_decode($task['arg'],TRUE);
                $task['src'] = json_decode($task['src'],TRUE);
                $this->assign('_task',$task);
                $this->setBottomColumns();
                $this->display();

            }
            else
                $this->error("Illegal Task");
        }
        else
            $this->error('Illegal Task');

    }

    public function delete()
    {
        $Task = M('Task');
        $tid = I('tid');
        if($Task->where(array('tid'=>$tid))->delete())
        {
            $this->success("Delete successfully",U("Task/index"));
        }
        else
            $this->error("Delete Failed",U('Task/index'));
    }
    

}


