<?php 	
namespace UserCenter\Controller;
class ProbeController extends HomeController
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
        $cur_page = I("page");
        $Probe = M('Probe');
        $NewProbe = M('NewProbe');
        $sums = ceil(($Probe->where(array('mid'=>$this->uid))->count()+$NewProbe->where(array('mid'=>$this->uid))->count())/7);
        if(empty($cur_page))
            $cur_page = 1;
        elseif($cur_page <=0 || $cur_page > $sums)
            $this->error('Illegal page num');
        $probes = $Probe->where(array('mid'=>$this->uid))->select();
        $new_probes = $NewProbe->where(array('mid'=>$this->uid))->select();
        $this->assign('_probes',$probes);
        $this->assign('_new_probes',$new_probes);
        $pages = ceil($Probe->where(array('mid'=>$this->uid))->count()/7)+ceil($NewProbe->where(array('mid'=>$this->uid))->count()/7);
        $this->assign('_pages',$pages);
        $this->assign('_cur_page',$cur_page);
        $this->assign('_count',$Probe->where(array('mid'=>$this->uid))->count()+$NewProbe->where(array('mid'=>$this->uid))->count());
        $user = M('Member')->where(array('uid'=>$this->uid))->find();
        $this->assign('_user',$user);
        $this->setBottomColumns();
        $this->setTabsCount();
        $this->display();
	}


    public function newProbe()
    {

    }

    public function detail($pid)
    {
        $pid = intval($pid);
        if($pid)
        {
            $probe = M('Probe')->where(array('pid'=>$pid))->find();
            if($probe)
            {
                $this->assign('_probe',$probe);
                $this->setBottomColumns();
                $this->display();
            }
            else
                $this->error('Illegal Probe');

        }
        else
            $this->error('Illegal Probe');

    }

    public function getConfigure($pid=NULL)
    {
        if(!empty($pid))
        {
            $util = new \Probe\Util\ProbeTaskUtil();
            $p = $util->getExportFile($pid);
            if(!empty($p)){
        	    header("Content-type:application/octet-stream");
            	header("Accept-Ranges:bytes");
        	    header("Content-Disposition:attachment;filename=setup");
			    echo json_encode($p);
			    exit();
            }
        }
    }
    

}


