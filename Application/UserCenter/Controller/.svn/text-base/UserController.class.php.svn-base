<?php 
    namespace UserCenter\Controller;
    class UserController extend HomeController
    {
        public function changeInfo()
        {
            if(IS_POST)
            {
                $p = array_filter(function($e){
                    if(!empty($e))
                        return true;
                    else
                        return false;
                    
                },I('post'));
            }
            else
            {
                $uid = is_login();
                if($uid)
                {
                    $member = M('Member')->where(array('uid'=>$uid))->find();
                    $umember = M('UcenterMember')->where(array('id'=>$uid))->find();
                    $probe = M("Probe")->where(array('mid'=>$uid))->find();
                    $groups = M("ProbeGroup")->select();
                    $this->assign('_username',$umember['username']);
                    $this->assign('_email',$umember['email']);
                    $this->assign('_as_number',$probe['as_number']);
                    $this->assign('_country',$probe['country']);
                    $this->assign('_city',$probe['city']);
                    $this->assign('_organization',$probe['organization']);
                    $this->assign('_groups',$groups);
                    $this->assign('_current_group',$probe['gid']);
                    $this->display();
                        
                }
            }
        }
    }
?>
