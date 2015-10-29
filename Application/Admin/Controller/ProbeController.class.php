<?php 
namespace Admin\Controller;
use Probe\Util\ProbeTaskUtil;
class ProbeController extends AdminController
{
    private $util = NULL;
    public function __construct()
    {
        parent::__construct();
        $this->util = new ProbeTaskUtil();
    }
	public function index()
	{
		//TODO: 搜索
		if(IS_AJAX)
		{
			$page = I('get.page');
			$probes = M('Probe')->page(I('get.page').',20')->select();
			int_to_string($list,array('status'=>array(1=>'正常',0=>'不可用',-1=>'删除',2=>'故障')));
			$this->ajaxReturn($probes);
		}
		else
		{
			$map = array('status',array('egt',0));
			$probes = M('Probe')->page(1,20)->select();
			$pages = (M('Probe')->where('status>0')->count())/20+1;
			$this->meta_title = '探针信息';
			int_to_string($probes,array('status'=>array(1=>'正常',0=>'禁用',-1=>'删除',2=>'故障')));
			$this->assign('_list',$probes);
			if($pages>1)
				$this->assign('_pages',$pages);
            $members = M('Member')->where(array('status'=>array('egt',0)))->select();
            $this->assign('_members',$members);
			$this->display();
		}
	}

	public function delete($pid=NULL)
	{
		if(!is_null($pid))
		{
			if(M('Probe')->save(array('pid'=>$pid,'status'=>-1)))
			{
				$this->success('操作成功',U('index'));
			}
			else
			{
				$this->error('操作失败');
			}
		}
	}
	/**
	 * status:-1:删除,0:禁用,1:正常,2:故障
	 */
	public function changeStatus($pid=NULL,$newStatus=NULL)
	{
		if(!is_null($pid) && !is_null($newStatus))
		{
			if(M('Probe')->save(array('pid'=>$pid,'status'=>$newStatus)))
			{
				$this->success('状态更改成功',U('index'));
			}
			else
			{
				$this->error('状态更改失败');
			}
		}
	}

	/**
	 * 探针概览
	 */
	public function show($id=NULL)
    {
        if(is_null($id))
        {
            //TODO:将所有节点的经纬度信息分配给view
            $this->display();
        }
        else
        {
            //TODO : 将pid为$id的节点的经纬度信息分配给视图
            $this->display();
        }
    }


    /**
     * 探针配置
     */
    public function configure($pid=NULL)
    {
        if(IS_POST)
        {
            $pid = I('post.pid');
            if(is_null($this->util))
            {
                $this->util = new ProbeTaskUtil();
            }
            $p = $this->util->getExportFile($pid);
        	header("Content-type:application/octet-stream");
        	header("Accept-Ranges:bytes");
        	header("Content-Disposition:attachment;filename=setup");
			echo json_encode($p);
			exit();
        }
        else
        {
            if($pid)
            {
        	   $serverlist = implode('\n', C('PROBE_SERVER_LIST'));
        	   $this->assign('_pid',$pid);
        	   $this->display();
            }
        }
    }
    
    public function task()
    {
        $Task = M('Task');
        //每页十条
        if(IS_AJAX)
        {
            $page = I('get.page');
            $items - $Task->page($page,10)->select();
            $this->ajaxReturn($items);
        }
        else
        {
            $num = ceil($Task->count()/10);
            $types = M('Tasktype')->select();
            $tasks = $Task->page(1,10)->select();
            $this->assign('_list',$tasks);
            $this->assign('_pages',$num);
            $this->assign('_types',$types);
            $probes = M('Probe')->select();
            $this->assign('_probes',$probes);
            $this->display();
        }
    }

    public function addTask()
    {
       $tid = $this->util->addTask($_POST);
       if($tid)
       {
            $result = $this->util->quickDo($tid);
            if($result)
            {
                $this->success('Operation Success',U('Admin/Probe/task'));

            }
            else
            {
                $this->error('Operation Failed',U('Admin/Probe/task'));
            }
       }
       else
       {
            $this->error('操作失败',U('Probe/task'));
       }
    }


    public function uncertProbe()
    {
        $NewProbe = M('NewProbe');
		$pageNum = I('page');
		$_cur_page = 0;
		$_page = 0;
		$_list = null;
        if(!empty($pageNum))
        {
			$_cur_page = $pageNum;
			$_page = ceil($NewProbe->count()/10);
			$_list = $NewProbe->page($pageNum,10)->select();
			
            // $pagenum = I('get.page');
//             $tasks = $NewProbe->page($pagenum,10)->select();
//             $this->ajaxReturn($tasks);
        }
        else
        {
			$_cur_page = 1;
            $_page = ceil($NewProbe->count()/10);
            $_list = $NewProbe->page(1,10)->select();
        }
		$this->assign('_cur_page',$_cur_page);
		$this->assign('_page',$_page);
        $this->assign('_list',$_list);
        $this->display();
    }

    public function deleteNewProbe()
    {
        if(IS_POST)
        {
            $pids = (array)I('pid');
            if(!empty($pids))
            {
                $NewProbe = M('NewProbe');
                foreach($pids as $pid)
                {
                    $NewProbe->where(array('pid'=>$pid))->delete();
                }
            }
            $this->success('操作成功');
        }
    }
    /**
     * 添加新探针---待验证
     */

    public function addNewProbe()
    {
        if(IS_POST)
        {
            if(is_null($this->util))
                $this->util = new ProbeTaskUtil();
            $p['serverlist'] = C('PROBE_SERVER_LIST');
            $p['dns1'] =FALSE;
            $p['dns2'] = FALSE;
            $p['country'] = I('post.country');
            $p['city'] = I('post.city');
            $p['as_num'] = I('post.as_num');
            $p['mid'] = I('post.mid');
			$p['gid'] = I('post.gid');
            $p['name'] = I('post.name');
            $pid = $this->util->addNewProbe($p);
            if($pid)
            {
                $this->success('待验证探针生成成功，请导出配置文件',U('uncertProbe',array('pid'=>$pid)));
                
            }
            else
            {
                $this->error('添加失败',U('index',array('pid'=>$pid)));
            }
        }
    }

	
    /**
     * 根据任务类型得到相应参数
     */
    public function getArgs()
    {
        if(IS_AJAX)
        {
            $type = I('get.type');
            echo  $this->util->getArgs($type);
        }
    }

    public function deleteProbe()
    {
        $ids = array_unique((array)I('id',0));
        $Probe = M('Probe');
        if(is_array($ids))
        {
            foreach($ids as $value)
            {
                //$probe = $Probe->where(array('pid'=>$value))->find();
                //if($probe)
               // {
                   // $probe['status'] = -1;
                    //$Probe->save($probe);
                //}
                $Probe->where(array('pid'=>$value))->delete();
            }
        }
        else
        {
            //$probe = $Probe->where(array('pid'=>$pid))->find();
            //if($probe)
            //{
                //$probe['status'] = -1;
                //$Probe->save($probe);
            //}
            $Probe->where(array('pid'=>$ids))->delete();
        }
    }

    public function deleteTask()
    {
        $ids = array_unique((array)I('id',0));
        $Task = M('Task');
        $success = TRUE;
        if(is_array($ids))
        {
            foreach($ids as $id)
            {
                $task = $Task->where(array('tid'=>$id))->find();
                $filename = $task['resultfile'];
                if(!$Task->where(array('tid'=>$id))->delete())
                {
                    unlink($filename);
                    $success = FALSE;
                    break;
                }
            }
            if($success)
            {
                $this->success('Operation success',U('Probe/task'));
            }
            else
            {
                $this->error('Operation Failed');
            }

        }
        else
        {
            if($Probe->where(array('tid'=>$ids))->delete())
            {
                
                $this->success('Operation success',U('Probe/task'));
            }
            else
            {

                $this->error('Operation Failed');
            }
        }
    }

    public function export($probes=NULL)
    {
        if(IS_POST)
        {
            $this->configure();
        } 
        else
        {
            if(!empty($probes))
            {
                $this->assign('_probes',$probes);
                $this->display();
            }
            else
            {
                //TODO: 到处未连接的探针配置
            }
        }
    }

}

 ?>
