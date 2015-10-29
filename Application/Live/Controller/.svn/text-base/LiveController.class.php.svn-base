<?php
namespace Live\Controller;
use Think\Controller;
class LiveController extends Controller {
	public function live()
	{
		$this->display();
	}
	
	public function index()
	{
        $this->display();
	}
    public function getLocationInfo()
    {
        if(IS_GET)
        {
            $Probe = M('Probe');
            $OpenProbe = M('OpenProbe');
            $probes = $Probe->field(array('position'))->where(array('status'=>1))->select();
            $probes = array_map(function($e){
                $tmp = explode(',',$e['position']);
                $e['position'] = $tmp[1].','.$tmp[0];
                return $e['position'];
            },$probes);
            if(empty($probes))
                $probes = array();
            $opens = $OpenProbe->where(array('status'=>1))->field(array('position'))->select();
            $opens = array_map(function($e){
                return $e['position'];
            },$opens);
            $probes = array_unique(array_merge($probes,$opens));
            $tmp = $probes;
            $probes = array();
            foreach($tmp as $key=>$value)
            {
                array_push($probes,$value);
            }
            $probes = array_map(function($e){
                $probe = array();
                $probe['name'] = guid();
                $e = str_replace('?','',$e);
                $e = explode(',',$e);
                $e = array_map(function($e){
                    return floatval($e);
                },$e);
                $probe['geoCoord'] = $e;
                return $probe;
            },$probes);
            $this->ajaxReturn($probes);
        }
    }
}

