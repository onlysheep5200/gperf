<?php 	
namespace UserCenter\Controller;
class InfoController extends HomeController
{
    private $uid;
    public function __construct()
    {
        parent::__construct();
        $this->uid = is_login();
    }
	public function index()
	{
        
	}

    public function getTask($page=1,$num=10)
    {
        $tasks = M('Task')->where(array('mid',$this->uid))->page($page,$num)->select();
        return $tasks;
    }

    public function getProbes($page=1,$num=10)
    {
        $probes = M('Probe')->where(array('mid'=>$this->uid))->page($page,$num)->select();
        return $probes;
    }

    public function getPageNum($model,$num)
    {
        if($num == 0)
            return FALSE;
        $Model = M($model);
        if($Model)
        {
            $count = $Model->where(array('mid'=>$this->uid))->count();
            $pages = ceil($count/$num);
            return $pages;
        }
        else
            return FALSE;
    }
}

 ?>
