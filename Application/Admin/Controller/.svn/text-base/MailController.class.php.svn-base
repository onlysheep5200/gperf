<?php 	
	namespace Admin\Controller;
	use Think\PHPMailer;
	use Think\SMTP;
	class MailController extends AdminController
	{
		public function test()
		{
			$result = sendMail('this is subject','this is content',array('email'=>'onlysheep1234@163.com'));
			echo $result['info'];
		}
	}
	
 ?>