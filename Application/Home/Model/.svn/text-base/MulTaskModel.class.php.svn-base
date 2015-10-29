<?php
namespace Home\Model;
use Think\Model;
class MulTaskModel extends Model
{
    public function getTasks($guid)
    {
        $task = $this->field('tids')->where(array('guid'=>$guid))->order('createtime desc')->find();
        if(empty($task))
            return FALSE;
        $tids = explode(',',$task['tids']);
        $result = array();
        $Task = M('Task');
        foreach($tids as $tid)
        {
            $t = ($Task->where(array('tid'=>$tid))->select())[0];
            if($t)
            {
                array_push($result,$t);
            }
        }
        if(!empty($result))
            return $result;
        else
            return FALSE;
    }
}

?>
