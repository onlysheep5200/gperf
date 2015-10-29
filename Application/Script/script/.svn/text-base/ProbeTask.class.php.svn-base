<?php
class ProbeTask {
    static public function get($task) {
        $class = ucfirst($task).'ProbeTask';
        $task != '' && class_exists($class) or $class = 'BaseProbeTask';
        return new $class;
    }

    static public function getMid($pid, $increase=true) {
        $db = DB::get();
        $db("SELECT mid FROM prb_probe WHERE pid = '$pid'");
        $mid = $db->ele();
        if ($increase) {
            //if ($mid == 4294967295) {
            if ($mid == 512) {
                $newmid = 1;
            } else {
                $newmid = $mid + 1;
            }
            $db->update('prb_probe', array('mid' => $newmid), "pid = '$pid'");
        }
        return $mid;
    }
}

class BaseProbeTask{
    public function tplfile() {
        return 'probe/task/'.strtolower(substr(get_class($this), 0, -9)).'.tpl';
    }
    public function tplarg() { return array(); }
    public function addtask($p) { return array(); }
    public function afteraddtask($tid) {}
    public function onrecv($tid, $pid, $arg, $result) { }
    public function ondelete($tid) { }
    public function showarg($arg, $tid) { return ""; }
    public function showshortarg($arg, $tid) { return ""; }
    public function showresult($result, $tid) { return ""; }
    public function showshortresult($result, $tid) { return ""; }
    public function getcmd($p) { return "";}
    public function beforecmd($p){ return "";}
    public function aftercmd($p){ return "";}
    public function _export($result) { return ''; }
    public function checkAlert($tid, $pid, $val) { }
    public function getAlert($tid, $ipstr) { }
    public function export($tid) {
        $content = array();
        foreach ($this->getTaskHistory($tid) as $date => $result) {
            $content[] = "== $date ================";
            $content[] = '';
            foreach ($result as $probe => $v) {
                $content[] = "-= $probe =-";
                $content[] = $this->_export($v);
            }
            $content[] = '';
        }
        $content[] = '';
        return implode("\r\n", $content);
    }
    protected function getTaskHistory($tid) {
        $db = DB::get();
        $tid = byr_esc($tid);
        $db("SELECT uptime, result FROM prb_taskhistory WHERE tid = '$tid' ORDER BY uptime DESC");
        $result = array();
        $probe_map = null;
        $all = $db->all();
        foreach ($all as $r) {
            $res = json_decode($r['result'], true);
            if ($probe_map === null) {
                $pids = implode("', '", array_keys($res));
                $probe_map = byr_get_options("SELECT pid, name FROM prb_probe WHERE pid in ('$pids')");
            }
            $tmp = array();
            foreach ($res as $k => $v) {
                if ($v['error']) {
                    $v = null;
                } else {
                    $v = $v['return'];
                }
                $tmp[$probe_map[$k]] = $v;
            }
            $result[$r['uptime']] = $tmp;
        }
        return $result;
    }
    public function getprobename($pid){ 
        $db = DB::getSingle();
        $db("select ip, name from prb_probe where pid = '$pid'");
        $r = $db();
        if(empty($r)){
            $ret = "已删除未知探针";
        } else{
            $ret = inet_ntop($r['ip']);
            if(!empty($r['name'])){
                $ret .= "({$r['name']})";
            }
        }
        return $ret;
    }
}

class PingProbeTask extends BaseProbeTask {
    public function addtask($p) {
        if (empty($p['dest'])) return '目的地址不能为空';
        $p['dest'] = byr_esc($p['dest']);
        $p['vtype'] = byr_esc($p['vtype']);
        if (!is_numeric($p['time'])) return '次数必须为数字';
        $p['time'] = intval(byr_esc($p['time']));
        if (!is_numeric($p['size'])) return '数据大小必须为数字';
        $p['size'] = intval(byr_esc($p['size']));
        if (!is_numeric($p['delaylimit'])) return '延迟阈值必须为数字';
        $p['delaylimit'] = intval(byr_esc($p['delaylimit']));
        if (!is_numeric($p['lostlimit'])) return '丢包阈值必须为数字';
        $p['lostlimit'] = intval(byr_esc($p['lostlimit']));

        return array('vtype' => $p['vtype'], 'dest' => $p['dest'], 'time' => $p['time'], 'size' => $p['size'], 'delaylimit' => $p['delaylimit'], 'lostlimit' => $p['lostlimit']);
    }
    public function afteraddtask($tid) {
        $db = DB::getSingle();
        $db("select src from prb_task where tid = '$tid'");
        $src = $db->ele();
        foreach(json_decode($src, true) as $pid){
            RRD::getCreator(RRDFILE_DIR."/probe/$tid/$pid.delay.rrd", 'delay')->create();
            RRD::getCreator(RRDFILE_DIR."/probe/$tid/$pid.lost.rrd", 'lost')->create();
        }
    }
    public function onrecv($tid, $pid, $arg, $result) {
        if(!$result['error']){
            $data = $result['return'];
            RRD::getUpdater(RRDFILE_DIR."/probe/$tid/$pid.delay.rrd")->update($data['delay']);
            RRD::getUpdater(RRDFILE_DIR."/probe/$tid/$pid.lost.rrd")->update($data['lost']);
            include_once(WEBDIR."/model/alert.php");
            $model = new AlertModel;
            if(!empty($arg['delaylimit'])){
                $model->newEvent($data['delay'] > $arg['delaylimit'], $tid, 'probe', $pid, 'delay', $data['delay']);
            }
            if(!empty($arg['lostlimit'])){
                $model->newEvent($data['lost'] > $arg['lostlimit'], $tid, 'probe', $pid, 'lost', $data['lost']);
            }
        }
    }
    public function ondelete($tid) {
        $db = DB::getSingle();
        $db("select src from prb_task where tid = '$tid'");
        $src = $db->ele();
        foreach(json_decode($src, true) as $pid){
            unlink(RRDFILE_DIR."/probe/$tid/$pid.delay.rrd");
            unlink(RRDFILE_DIR."/probe/$tid/$pid.lost.rrd");
        }
    }
    public function showarg($arg, $tid) {
        $str = "目标: {$arg['dest']}<br />次数: {$arg['time']}<br />包大小: {$arg['size']}字节";
        if(!empty($arg['delaylimit'])){
            $str .= "<br />延迟告警阈值: {$arg['delaylimit']}ms";
        }
        if(!empty($arg['lostlimit'])){
            $str .= "<br />丢包告警阈值: {$arg['lostlimit']}%";
        }
        return $str;
    }
    public function showshortarg($arg, $tid) {
        return "目标: {$arg['dest']}";
    }
    public function showresult($result, $tid) {
        $ret = array();
        foreach($result as $pid => $r){
            $probe = $this->getprobename($pid);
            if($r['error']){
                $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> 执行失败"; 
            } else{
                $data = $r['return'];
                if(empty($data)){
                    $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> 目标地址错误"; 
                } else{
                    if($data['lost'] == 100){
                        $data['delay'] = '-';
                    }
                    $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span><span class='grd6 disin'>目标地址: {$data['ip']}</span><span class='grd3 disin'>丢包: {$data['lost']}%</span>平均延时: {$data['delay']}ms</br>";
                }
            }
        }
        return $ret;
    }
    public function showshortresult($result, $tid) {
        $total = $result['ok'] + $result['fail'];

        $url1 = byr_url('probe', 'ajaxpingtaskrrd', array('delay', $tid));
        $url2 = byr_url('probe', 'ajaxpingtaskrrd', array('lost', $tid));
        $content = "<img src=\'$url1\' /><br /><img src=\'$url2\' />";
        $a = <<<JS
<a href="javascript:MIZA.box.open('$content',{height:440,width:670,time:0,isPre:0,title:'{$result['dest']} 历史图'});">历史图</a>
JS;

        return "{$result['ad']}ms,&nbspOK: {$result['ok']}/$total $a";
    }
    public function getcmd($p) {
        $pingnum = $p['time'];
        $pingsize = $p['size'];
        $ipstr = $p['dest'];
        if($p['vtype'] == 6){
            $exec = "ping6 -I eth0 -c $pingnum -s $pingsize -q $ipstr";
        } else{
            $exec = "ping -c $pingnum -s $pingsize -q $ipstr";
        }
        $cmd = <<<TPL
#py
import re
proc.expect('#')
proc.sendline('$exec')
proc.expect('PING')
proc.expect('statistics')
ipmatch = re.search(r'\(([0-9.a-faA-F:]+)\)', proc.before)
if ipmatch is None: 
    proc.ret_value = None
else:
    ip = ipmatch.group(1)
    proc.expect('#')
    lostmatch = re.search(r'([0-9.]+)% packet loss', proc.before)
    lost = int(lostmatch.group(1))
    if lost == 100:
        delay = None
    else:
        delaymatch = re.search(r'/([0-9.]+)/', proc.before)
        delay = float(delaymatch.group(1))
    proc.ret_value = {'ip': ip, 'lost': lost, 'delay': delay,'before':proc.before} 
TPL;
        return $cmd;
    }
    public function aftercmd($p){
    }
    public function _export($result) {
        if ($result === null) {
            return '执行失败';
        } else {
            if($result['lost'] == 100){
                $result['delay'] = '-';
            }
            return "目标地址: {$result['ip']} 丢包: {$result['lost']}% 平均延时: {$result['delay']}ms";
        }
    }
    public function checkAlert($arg, $pid, $val) {
    }
    public function getAlert($tid, $ipstr) {
        $db = DB::getSingle();
        $ipbin = byr_pton($ipstr);
        $tid = byr_esc($tid);
        $db("select name from prb_probe where ip = '$ipbin'");
        $probename = $db->ele();
        $db("select * from prb_task where tid = '$tid'");
        $data = $db();
        if(empty($probename) || empty($data)){
            return "探针或任务已删除";
        } else{

        }
    }
}

class TracertProbeTask extends BaseProbeTask {
    public function addtask($p) {
        if (empty($p['dest'])) return '目的地址不能为空';
        $p['dest'] = byr_esc($p['dest']);
        if (!is_numeric($p['maxttl'])) return '最大跳数必须为数字';
        $p['maxttl'] = intval(byr_esc($p['maxttl']));
        $p['vtype'] = byr_esc($p['vtype']);
        return array('vtype' => $p['vtype'], 'dest' => $p['dest'], 'maxttl' => $p['maxttl']);
    }
    public function showarg($arg, $tid) {
        return "目标: {$arg['dest']}<br />最大跳数: {$arg['maxttl']}";
    }
    public function showshortarg($arg, $tid) {
        return "目标: {$arg['dest']}";
    }
    public function showresult($result, $tid) {
        $ret = array();
        foreach ($result as $pid => $a) {
            $probe = $this->getprobename($pid);
            if($a['error']){
                $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> 执行失败"; 
            } else{
                $data = $a['return'];
                if(empty($data)){
                    $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> 目标地址错误"; 
                } else{
                    $str = "<span class='grd6 disin'>源探针: $probe </span><span class='grd5 disin'>目标地址: {$data['address']}</span></br>";
                    foreach ($data['result'] as $r) {
                        empty($r['ip']) and $r['ip'] = '';
                        empty($r['time'][0]) and $r['time'][0] = '*';
                        empty($r['time'][1]) and $r['time'][1] = '*';
                        empty($r['time'][2]) and $r['time'][2] = '*';
                        $str .= "<span class='padl20 grd2 disin'>跳数: {$r['ttl']}</span>
                            <span class='grd7 disin'>IP: {$r['ip']}</span>
                            <span class='grd2 disin'>{$r['time'][0]}ms</span>
                            <span class='grd2 disin'>{$r['time'][1]}ms</span>
                    {$r['time'][2]}ms</br>";
                    }
                    $ret[$pid] = $str;
                }
            }
        }
        return $ret;
    }
    public function showshortresult($result, $tid) {
        $count = count($result);
        return "跳数: $count";
    }
    public function getcmd($p) {
        $maxttl = $p['maxttl'];
        $ipstr = $p['dest'];
        $traceroutecmd = ($p['vtype'] == '4' ? 'traceroute' : 'traceroute6');
        $cmd = <<<PYTHON
#py
proc.expect('#')
proc.sendline('$traceroutecmd -m $maxttl $ipstr')
proc.expect('traceroute')
proc.expect('#', timeout=300)
if 'hops max' not in proc.before:
    proc.ret_value = None
else:
    address = '$ipstr'
    result = []
    lines = proc.before.splitlines()
    lines.pop(0)
    for line in lines:
        parts = filter(None, line.split(' '))
        try:
            ttl = int(parts.pop(0))
        except ValueError:
            continue
        ip = None
        time = None
        while len(parts):
            tmp = parts.pop(0)
            if tmp == '*':
                continue
            ip = tmp
            parts.pop(0)

            time = []
            while len(parts) > 1:
                if parts[1] != 'ms':
                    parts.pop(0)
                    continue
                try:
                    time.append(parts.pop(0))
                except ValueError:
                    continue
                parts.pop(0)
            if not time:
                time = None
            break
        ret = {'ttl': ttl, 'ip': ip, 'time': time}
        result.append(ret)
    proc.ret_value = {'address': address, 'result': result}
PYTHON;
        //$cmd = <<<TPL
//#py
//import re
//proc.expect('#')
//proc.sendline('traceroute -m $maxttl $ipstr')
//proc.expect('#', timeout=300)
//retmatch = re.search(r'traceroute to [\s\S]* \(([0-9.]+)\),', proc.before)
//if retmatch is None: 
    //proc.ret_value = None
//else:
    //address = retmatch.group(1)
    //result = []
    //for line in proc.before.split('\\r\\n'):
        //match = re.search(r'^([0-9 ][0-9]) ', line)
        //if match != None:
            //ttl = int(match.group(1))
            //ip = None
            //time = None
            //ipmatch = re.search(r'([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)', line)
            //if ipmatch == None:
                //ip = None
            //else:
                //ip = ipmatch.group(1)
                //timematch = re.findall(r'([0-9.]+) ms', line)
                //if timematch == None:
                    //time = None
                //else:
                    //time = timematch
            //ret = {'ttl': ttl, 'ip': ip, 'time': time}
            //result.append(ret)
    //proc.ret_value = {'address': address, 'result': result} 
//TPL;
        return $cmd;
    }
    public function aftercmd($p){
    }
    public function _export($result) {
        if ($result === null) {
            return '执行失败';
        } else {
            $str = "目标地址: {$result['address']}\r\n";
            foreach ($result['result'] as $r) {
                empty($r['ip']) and $r['ip'] = '';
                empty($r['time'][0]) and $r['time'][0] = '*';
                empty($r['time'][1]) and $r['time'][1] = '*';
                empty($r['time'][2]) and $r['time'][2] = '*';
                $str .= "跳数: {$r['ttl']} IP: {$r['ip']} {$r['time'][0]}ms {$r['time'][1]}ms {$r['time'][2]}ms\r\n";
            }
            return $str;
        }
    }
}

class DnsProbeTask extends BaseProbeTask {
    public function addtask($p) {
        if (empty($p['dest'])) return '目的地址不能为空';
        $p['dest'] = byr_esc($p['dest']);
        if (!is_numeric($p['delaylimit'])) return '延迟阈值必须为数字';
        $p['delaylimit'] = intval(byr_esc($p['delaylimit']));
        return array('dest' => $p['dest'], 'delaylimit' => $p['delaylimit']);
    }
    public function afteraddtask($tid) {
        $db = DB::getSingle();
        $db("select src from prb_task where tid = '$tid'");
        $src = $db->ele();
        foreach(json_decode($src, true) as $pid){
            RRD::getCreator(RRDFILE_DIR."/probe/$tid/$pid.delay.rrd", 'delay')->create();
        }
    }
    public function onrecv($tid, $pid, $result) {
        if(!$result['error']){
            $data = $result['return'];
            RRD::getUpdater(RRDFILE_DIR."/probe/$tid/$pid.delay.rrd")->update($data['delay']);
        }
    }
    public function ondelete($tid) {
        $db = DB::getSingle();
        $db("select src from prb_task where tid = '$tid'");
        $src = $db->ele();
        foreach(json_decode($src, true) as $pid){
            unlink(RRDFILE_DIR."/probe/$tid/$pid.delay.rrd");
        }
    }
    public function showarg($arg, $tid) {
        $str = "目标: {$arg['dest']}";
        if(!empty($arg['delaylimit'])){
            $str .= "<br />延迟告警阈值: {$arg['delaylimit']}ms";
        }
        return $str;
    }
    public function showshortarg($arg, $tid) {
        return "目标: {$arg['dest']}";
    }
    public function showresult($result, $tid) {
        $ret = array();
        foreach($result as $pid => $r){
            $probe = $this->getprobename($pid);
            if($r['error']){
                $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> 执行失败"; 
            } else{
                $data = $r['return'];
                $server = $data['server'];
                $address = '';
                if(empty($data['address'])){
                    $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span><span class='grd4 disin'>DNS服务器: $server </span> 目标地址解析失败"; 
                } else{
                    foreach ($data['address'] as $key => $r) {
                        $address .= "<span class='padl20 grd8 disin'>地址{$r['no']}: {$r['ip']}</span></br>";
                    }
                    $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> DNS服务器: $server </br> $address"; 
                }
            }
        }
        return $ret;
    }
    public function _export($result) {
        if ($result === null) {
            return '执行失败';
        } else {
            $server = $result['server'];
            foreach ($result['address'] as $key => $r) {
                $address .= "地址{$r['no']}: {$r['ip']}\r\n";
            }
            return "DNS服务器: $server \r\n$address"; 
        }
    }
    public function showshortresult($result, $tid) {
        $count = count($result);
        return "跳数: $count";
    }
    public function getcmd($p) {
        $ipstr = $p['dest'];
        $cmd = <<<TPL
#py
import re
import time
proc.expect('#')
stime = time.time()
proc.sendline('nslookup $ipstr')
proc.expect('Server.*#', timeout=20)
etime = time.time()
cost = round((etime - stime) * 1000)
servermatch = re.search(r'Server:[\s]*([0-9.]+)', proc.after)
if servermatch is None:
    server = 'Server unknown'
    address = None
else:
    server = servermatch.group(1)
    tmp = proc.after.split('Name');
    str = tmp[1]
    addressmatch = re.findall(r'Address ([0-9]+): ([0-9.a-f:]+)', str)
    if addressmatch is None:
        address = None
    else:
        address = []
        for match in addressmatch:
            ret = {'no': match[0], 'ip': match[1]}
            address.append(ret)
proc.ret_value = {'delay': cost, 'server': server, 'address': address}
TPL;
        return $cmd;
    }
    public function aftercmd($p){
    }
}

class BandwidthProbeTask extends BaseProbeTask {
    public function addtask($p) {
        $arr['protocol'] = byr_esc($p['protocol']);
        if($p['targettype'] == 'probe'){
            if(in_array($p['targetprobe'], $p['src'])){
                return "源端不能选择目的探针";
            }
            $arr['targetprobe'] = byr_esc($p['targetprobe']);
        }
        $arr['targettype'] = byr_esc($p['targettype']);
        if (!is_numeric($p['bandwidthlimit'])) return '带宽阈值必须为数字';
        $arr['bandwidthlimit'] = intval(byr_esc($p['bandwidthlimit']));
        $arr['vtype'] = byr_esc($p['vtype']);
        return $arr;
    }
    public function afteraddtask($tid) {
        $db = DB::getSingle();
        $db("select src from prb_task where tid = '$tid'");
        $src = $db->ele();
        foreach(json_decode($src, true) as $pid){
            RRD::getCreator(RRDFILE_DIR."/probe/$tid/$pid.bandwidth.rrd", 'bandwidth')->create();
    }
    }
    public function onrecv($tid, $pid, $result) {
        if(!$result['error']){
            $data = $result['return'];
            RRD::getUpdater(RRDFILE_DIR."/probe/$tid/$pid.bandwidth.rrd")->update($data['bandwidth']);
    }
    }
    public function ondelete($tid) {
        $db = DB::getSingle();
        $db("select src from prb_task where tid = '$tid'");
        $src = $db->ele();
        foreach(json_decode($src, true) as $pid){
            unlink(RRDFILE_DIR."/probe/$tid/$pid.bandwidth.rrd");
    }
    }
    public function showarg($arg, $tid) {
        $str = "协议: {$arg['protocol']}";
        if($arg['targettype'] == 'server'){
            $str = "目标: 本服务器</br>$str";
    } else{
        $str = "目标: 探针{$arg['targetprobe']}</br>$str";
    }
    if(!empty($arg['bandwidthlimit'])){
        $str .= "<br />带宽告警阈值: {$arg['bandwidthlimit']}Mbits/sec";
    }
    return $str;
    }
    public function showshortarg($arg, $tid) {
        if($arg['targettype'] == 'server')
            return "目标: 本服务器";
        else
            return "目标: 探针{$arg['targetprobe']}";
    }
    public function showresult($result, $tid) {
        $ret = array();
        foreach($result as $pid => $r){
            $probe = $this->getprobename($pid);
            if($r['error']){
                $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> 执行失败"; 
    } else{
        $data = $r['return'];
        if(empty($data)){
            $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> 带宽测试失败"; 
    } else{
        $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> 带宽测试结果: {$data['bandwidth']}Mbits/sec</br>"; 
    }
    }
    }
    return $ret;
    }
    public function _export($result) {
        if ($result === null) {
            return '执行失败';
        } else {
            return "带宽测试结果: {$result['bandwidth']}Mbits/sec";
        }
    }
    public function showshortresult($result, $tid) {
        $count = count($result);
        return "跳数: $count";
    }
    public function beforecmd($p){

        
    if($p['targettype'] == 'server'){
         if($p['protocol'] == 'tcp'){
            $exec = "iperf -s";
        }
         else{
             
             $exec = "iperf -s -u";
         }
         if($p['vtype'] == 6){
             $exec .= " -V";
           }
         if (($pid = pcntl_fork()) == 0) {
            return shell_exec($exec);
            exit();
         }
    } 
    else{
        if (($pid = pcntl_fork()) == 0) {
            $db = DB::getSingle();
            $pid = $p['targetprobe'];
            $db("select port from gperf_probe where pid = '$pid'");
            $port = $db->ele();
            if($p['protocol'] == 'tcp'){
                $do = "iperf -s";
            } else{
                 $do = "iperf -s -u";
             }
        if($p['vtype'] == 6){
            $do .= " -V";
     }
    //$quit = "netstat -nap | grep 5001 | awk '{print $7}' | awk -F '/' '{print $1}' | xargs kill -9";
    //proc.sendline('$quit')
    $ex = new PyExpect('telnet', array("127.0.0.1", $port));
    $cmd = <<<TPL
#py
proc.expect('#')
proc.sendline('$do')
proc.expect('Mbits', timeout=30)
proc.ret_value = None
TPL;
    $ex->addOperation($cmd);
    $ex->run();
    exit();
    }
    sleep(1);
    }
    }
    public function getcmd($p) {
        if($p['targettype'] == 'server'){
            $eth0ip = exec('/sbin/ifconfig |grep -o "inet addr:[0-9.]*"',$out,$err);
            $server = substr($out[0],10);
    } else{
        $db = DB::getSingle();
        $pid = $p['targetprobe'];
        if($p['vtype'] == 4)
            $db("select ip from gperf_probe where pid = '$pid'");
        else
            $db("select ip_v6 from gperf_probe where pid = '$pid'");
        $server = $db->ele();
        if($p['vtype'] == 6)
        {
            $server = explode('/',$server);
            $server = $server[0];
        }
    }
    if($p['protocol'] == 'tcp'){
        $do = "iperf -c $server";
    } else{
        $do = "iperf -c $server -u";
    }
    if($p['vtype'] == 6){
        $do .= " -V";
    }
    $cmd = <<<TPL
#py
import re
import time
proc.expect('#')
stime = time.time()
proc.sendline('$do')
proc.expect('Client', timeout=30)
proc.expect('#')
etime = time.time()
cost = round((etime - stime) * 1000)
match = re.search(r'([0-9.]+) Mbits/sec', proc.before)
if match is None:
    proc.ret_value = None
else:
    bandwidth = float(match.group(1))
    proc.ret_value = {'delay': cost, 'bandwidth': bandwidth}
TPL;
    return $cmd;
    }
    public function aftercmd($p){
        //unfinished how to lock???
        if($p['targettype'] == 'server'){
            exec("netstat -nap | grep 5001 | awk '{print $7}' | awk -F '/' '{print $1}' | xargs kill -9");
    } else{
            /*
            $db = DB::getSingle();
            $pid = $p['targetprobe'];
            $db("select port from prb_probe where pid = '$pid'");
            $port = $db->ele();
            $do = "netstat -nap | grep 5001 | awk '{print $7}' | awk -F '/' '{print $1}' | xargs kill -9";
            $ex = new PyExpect('telnet', array("127.0.0.1", $port));
            $cmd = <<<TPL
#py
proc.expect('#')
proc.sendline('$do')
proc.expect('#')
proc.ret_value = None
TPL;
            $ex->addOperation($cmd);
            $ex->run();;
             */
    }
    }
}

class HttpProbeTask extends BaseProbeTask {
    public function addtask($p) {
        if (empty($p['dest'])) return '页面地址不能为空';
        $p['dest'] = byr_esc($p['dest']);
        if (!is_numeric($p['delaylimit'])) return '延迟阈值必须为数字';
        $p['delaylimit'] = intval(byr_esc($p['delaylimit']));
        if($p['monitortype'] == 'all'){
            return array('dest' => $p['dest'], 'monitortype' => $p['monitortype'], 'delaylimit' => $p['delaylimit']);
        } else{
            $p['keyword'] = byr_esc($p['keyword']);
            return array('dest' => $p['dest'], 'monitortype' => $p['monitortype'], 'keyword' => urlencode($p['keyword']), 'delaylimit' => $p['delaylimit']);
        }
    }
    public function afteraddtask($tid) {
        $db = DB::getSingle();
        $db("select src from prb_task where tid = '$tid'");
        $src = $db->ele();
        foreach(json_decode($src, true) as $pid){
            RRD::getCreator(RRDFILE_DIR."/probe/$tid/$pid.delay.rrd", 'delay')->create();
        }
    }
    public function onrecv($tid, $pid, $result) {
        if(!$result['error']){
            $data = $result['return'];
            RRD::getUpdater(RRDFILE_DIR."/probe/$tid/$pid.delay.rrd")->update($data['delay']);
        }
    }
    public function ondelete($tid) {
        $db = DB::getSingle();
        $db("select src from prb_task where tid = '$tid'");
        $src = $db->ele();
        foreach(json_decode($src, true) as $pid){
            unlink(RRDFILE_DIR."/probe/$tid/$pid.delay.rrd");
        }
    }
    public function showarg($arg, $tid) {
        $str = "目标: {$arg['dest']}<br />";
        if($arg['monitortype'] == 'all'){
            $str .= '监控内容: 页面防篡改';
        } else{
            $arg['keyword'] = urldecode($arg['keyword']);
            $str .= '监控内容: 匹配关键字 '. $arg['keyword'];
        }
        if(!empty($arg['delaylimit'])){
            $str .= "<br />延迟告警阈值: {$arg['delaylimit']}ms";
        }
        return $str;
    }
    public function showshortarg($arg, $tid) {
        return "目标: {$arg['dest']}";
    }
    public function showresult($result, $tid) {
        $db = DB::getSingle();
        $tid = byr_esc($tid);
        $db("select arg from prb_task where tid = '$tid'");
        $arg = $db->ele();
        $arg = json_decode($arg, true);
        $dest = $arg['dest'];
        $ret = array();
        foreach($result as $pid => $r){
            $probe = $this->getprobename($pid);
            if($r['error'] == true){
                $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span> 执行失败"; 
            } else{
                $data = $r['return'];
                $cost = $data['delay']. 'ms';
                if($arg['monitortype'] == 'all'){
                    $ret[$pid] = "<span class='grd6 disin'>源探针: $probe </span><span class='grd4 disin'>响应时间: $cost</span> </br> <iframe src='{$dest}' width='100%' ></iframe>"; 
                } else{
                    if($data['data']){
                        $ret[$pid] = "<span class='grd6 disin'>源探针: $probe</span><span class='grd4 disin'>响应时间: $cost</span><span>匹配关键字成功</span> </br>"; 
                    } else{
                        $ret[$pid] = "<span class='grd6 disin'>源探针: $probe</span><span class='grd4 disin'>响应时间: $cost</span><span>匹配关键字失败</span> </br>"; 
                    }
                }
            }
        }
        return $ret;
    }
    public function _export($result) {
        if ($result === null) {
            return '执行失败';
        } else {
            $cost = $result['delay']. 'ms';
            $data = $result['data'];
            if(!is_numeric($data)){
                return "响应时间: $cost 内容： $data"; 
            } else{
                if($data){
                    return "响应时间: $cost 匹配关键字成功"; 
                } else{
                    return "响应时间: $cost 匹配关键字失败"; 
                }
            }
        }
    }
    public function showshortresult($result, $tid) {
        $count = count($result);
        return "跳数: $count";
    }
    public function getcmd($p) {
        $ipstr = $p['dest'];
        $cmd = <<<TPL
#py
import re
import time
proc.expect('#')
stime = time.time()
proc.sendline('wget -q -O - $ipstr')
proc.expect('/ #', timeout=20)
etime = time.time()
str = proc.before
cost = round((etime - stime) * 1000)
proc.ret_value = {'delay': cost, 'data': str}
TPL;
        if($p['monitortype'] == 'keyword'){
            $keyword = $p['keyword'];
            $cmd = <<<TPL
#py
import re
import time
proc.expect('#')
stime = time.time()
proc.sendline('wget -q -O - $ipstr')
proc.expect('/ #', timeout=20)
etime = time.time()
str = proc.before
str = str.replace(' wget -q -O - $ipstr\\r\\n', '');
match = str.find('$keyword')
cost = round((etime - stime) * 1000)
if match is None:
    proc.ret_value = {'delay': cost, 'data': 0}
else:
    proc.ret_value = {'delay': cost, 'data': 1}
TPL;
        }
        return $cmd;
    }
    public function aftercmd($p){
    }
}

