<?php
namespace Probex\Controller;
use Think\Controller;
class IpprobeController extends Controller {
    private $model;
    public function index() {
        $this->model = D('Probe');
        ob_start();
        $out = '';
        $ret = false;
        $str = '';
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        $ip = $_SERVER['REMOTE_ADDR'];

        $this->model->dispatch($ip, $data);
        

        $f = fopen('/tmp/probe.log', 'a+');
        $out .= ob_get_contents();
        fwrite($f, "--------------------\n$raw\n". date("H:i:s", time()));
        fclose($f);
        ob_end_flush();
        //return $ret;
        //echo $raw;
        //return $raw;
    }
    function indent($json) {
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = '  ';
        $newLine = "\n";
        $prevChar = '';
        $outOfQuotes = true;

        for ($i = 0; $i <= $strLen; $i++) {
            $char = substr($json, $i, 1);
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
            } elseif (($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos--;
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            $result .= $char;
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            $prevChar = $char;
        }
        return $result;
    }
}
