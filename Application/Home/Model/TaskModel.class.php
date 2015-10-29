<?php
namespace Probe\Util;
use Think\Model;
    class TaskModel extends Model
    {
        /**
         *导出任务
         *@type : 导出格式
         *@tid : 导出任务id
         */
        public function exportTask($type = 'PDF',$tid,$uid)
        {
            $task = $this->getTask($tid,$uid);
            if(!$task)
                $this->exportError();
            // switch($type)
            // {
            //     case 'PDF'：
            //         $this->exportPDF($selectedTask);
            //         break;
            //     default : 
            //         $this->exportError();
            // } 
        }

        public function exportPDF($task)
        {
            echo 'export pdf';
            die();
        }

        public function getTask($tid,$uid=FALSE)
        {
            $task = $this->where(array('tid'=>$tid))->find();
            $result = array();
            $legal = FALSE;
            if($uid)
            {
                if($uid == $task['mid'])
                     $legal=TRUE;
            }
            elseif($task['mid']==-1 && $task['guid']==session('guid'))
            {
                $legal = TRUE;
            }
            $result['status']=$legal;
            if($legal)
            {
                return $task;
            }
            return FALSE;
        }


    }
?>
