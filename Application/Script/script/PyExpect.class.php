<?php
class PyExpect
{
    static public $PY_SCRIPT = '';

    public $args = array();

    public function __construct($cmd, $args=array()) 
    {
        $this->args['cmd'] = $cmd;
        if (!is_array($args)) {
            $args = array($args);
        }
        $this->args['args'] = $args;
        $this->args['operations'] = array();
    }

    public function run() 
    {
        $ret = shell_exec('python '.self::$PY_SCRIPT.' '.
            escapeshellarg(json_encode($this->args)));
        $ret = json_decode($ret, true);
        return $ret === null ? false : $ret;
    }

    public function setTimeout($seconds)
    {
        $this->args['timeout'] = $seconds;
    }

    public function enableDebug() 
    {
        $this->args['debug'] = True;
    }

    public function addOperation($operation, $type=null)
    {
        if ($type == null) {
            $arr = array($operation);
        } else {
            $arr = array($type, $operation);
        }
        $this->args['operations'][] = $arr;
    }

    public function addContext($key, $value=null)
    {
        if (!isset($this->args['context'])) {
            $this->args['context'] = array();
        }
        if (is_array($key)) {
            $this->args['context'] = array_merge($this->args['context'], $key);
        } else {
            $this->args['context'][$key] = $value;
        }
    }

}
PyExpect::$PY_SCRIPT=dirname(__FILE__).'/../'.'python/expect/expect.py';

//$ex = new PyExpect('python');
//$ex->addContext('name', 'xitianfz');
//$ex->addOperation(<<<TPL
//--- python
//print '{name}'+'test'
//<<<{name}test
//TPL
//);
//var_dump($ex->run());
