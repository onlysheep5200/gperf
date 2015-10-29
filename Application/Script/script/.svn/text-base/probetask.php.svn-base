<?php
define('BYR_GATEWAY', '');
//include_once(dirname(__FILE__).'/../config.php');
//include_once(dirname(__FILE__).'/../model/network.php');
//byr_parallel_exec('/usr/bin/php '.WEBDIR.'/script/probetask.php '.escapeshellarg($r['type']).' '.escapeshellarg($r['arg']).' '.escapeshellarg($r['src']).' '.escapeshellarg($signfile));
include_once(dirname(__FILE__).'/PyExpect.class.php');
include_once(dirname(__FILE__).'/DB.class.php');
//$root = dirname(dirname(dirname(dirname(__FILE__))));
//function __autoload($class)
//{
  //  require($root.'/Think/Library/Probe/'.$class.'.class.php');
//}
include_once(dirname(__FILE__).'/ProbeTask.class.php');
//echo $root;
if (count($argv) != 5)
{
    echo count($argv);
    die();
}
function byr_parallel_run($params, $func) {
    $count = count($params);
    DB::enablePersistent(FALSE);
    $db = DB::getSingle();
    //$db("SELECT value FROM sys_var WHERE name = 'sys_parallel_num'");
    //$pnum = $db->ele();
    $pnum = 8;
    for ($i=0; $i!=$pnum && $i!=$count; $i++) {
        if (($pid = pcntl_fork()) == 0) {
            $func($params[$i]);
            exit();
        }
    }

    while ($i < $count && pcntl_wait($status)) {
        if (($pid = pcntl_fork()) == 0) {
            $func($params[$i]);
            exit();
        }
        $i++;
    }

    $waitcount = ($count < $pnum ? $count : $pnum);
    for ($i=0; $i!=$waitcount; $i++) pcntl_wait($status);
    DB::enablePersistent(TRUE);
}
$tasktype = $argv[1];
$arg = json_decode($argv[2], true);
$probelist = json_decode($argv[3], true);
$signfile = $argv[4];
$output = fopen('/tmp/output.txt','w+');
/**
 * bandwith 不能并行
 */
if($tasktype == 'bandwidth'){
foreach($probelist as $pid){
    $db = DB::getSingle();
    $db("select port from gperf_probe where pid = '$pid'");
    $port = $db->ele();
    $result = null;
	$bandwidth_rec = fopen("/tmp/bandwidth.txt","a+");
    if(!empty($port)){

        $out =  ProbeTask::get($tasktype)->beforecmd($arg); 
        $cmd = ProbeTask::get($tasktype)->getcmd($arg);
        fwrite($bandwidth_rec,$cmd." ".$out.'\n');
        $ex = new PyExpect('telnet', array("127.0.0.1", $port));
        $ex->addOperation($cmd);
//        $ex->enableDebug();
        $result = $ex->run();
        ProbeTask::get($tasktype)->aftercmd($arg);
    }
    fclose($bandwidth_rec);  
    $f = fopen($signfile, 'a');
    flock($f, LOCK_EX);
    fwrite($f, json_encode(array('pid' => $pid, 'result' => $result))."\n");
    flock($f, LOCK_UN);
    fclose($f);
}
} else{
    /**
     * function 后加use($params...) ==> 生成闭包
     */
    $arg['dest'] = array_unique(explode(' ',$arg['dest']));
    if(!is_array($arg['dest']))
        $arg['dest'] = array($arg['dest']);
    foreach($arg['dest'] as $dest){
byr_parallel_run($probelist, function ($pid) use ($tasktype, $arg, $signfile,$dest) {
    $arg['dest'] = $dest;
    $db = DB::getSingle();
    $db("select port from gperf_probe where pid = '$pid'");
    $port = $db->ele();
    if(!empty($port)){
        ProbeTask::get($tasktype)->beforecmd($arg);
        $cmd = ProbeTask::get($tasktype)->getcmd($arg);
        $ex = new PyExpect('telnet', array("127.0.0.1", $port));
        $ex->addOperation($cmd);
 //       $ex->enableDebug();
        $result = $ex->run();
        ProbeTask::get($tasktype)->aftercmd($arg);
    }
    $f = fopen($signfile, 'a');
    flock($f, LOCK_EX);
    fwrite($f, json_encode(array('pid' => $pid,'dest'=>$dest, 'result' => $result))."\n");
    flock($f, LOCK_UN);
    fclose($f);
});
    }
}
?>
