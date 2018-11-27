<?php
namespace Rindow\Console\Command;

use Rindow\Console\Exception;

class Arguments
{
    protected $argv;
    protected $shortOptions;
    protected $longOptions;

    public function __construct(array $commandLineArguments=null,$shortOptions=null,$longOptions=null)
    {
        if($commandLineArguments===null) {
            global $argv;
            $commandLineArguments = $argv;
        }
        $this->setArgv($commandLineArguments);
        $this->setShortOptions($shortOptions);
        $this->setLongOptions($longOptions);
    }

    public function setArgv($argv)
    {
        $this->argv = $argv;
    }

    public function setShortOptions($shortOptions)
    {
        $this->shortOptions = $shortOptions;
    }

    public function setLongOptions($longOptions)
    {
        $this->longOptions = $longOptions;
    }

    public function getArgv()
    {
        return $this->argv;
    }

    public function shift()
    {
        return array_shift($this->argv);
    }

    public function getCommand()
    {
        if(!isset($this->argv[0]))
            return false;
        return $this->argv[0];
    }

    public function getOptions()
    {
        return $this->getOpt($this->argv,$this->shortOptions,$this->longOptions);
    }

    public function getArguments()
    {
        $this->getOpt($this->argv,$this->shortOptions,$this->longOptions,$optind);
        $arguments = $this->argv;
        while($optind--) {
            array_shift($arguments);
        }
        return $arguments;
    }

    public function getOpt(array $argv, $shortOptions, array $longOptions=null, &$optind=null)
    {
        $shortOptions = $this->generateShortOptions($shortOptions);
        $longOptions = $this->generateLongOptions($longOptions);
        $options = array();
        $argc = count($argv);
        for($i=1; $i < $argc; $i++) { 
            $arg = $argv[$i];
            if($arg==='--') {
                $i++;
                break;
            }
            if(substr($arg, 0, 2)=='--') {
                list($opt,$value) = $this->getLongOpt($argv,$argc,$i,substr($arg, 2),$longOptions);
                if($opt===false)
                    break;
                $options = $this->addOpt($options,$opt,$value);
                continue;
            }
            if(substr($arg, 0, 1)!='-')
                break;
            $arglen = strlen($arg);
            for($j=1; $j<$arglen; $j++) {
                $opt = substr($arg,$j,1);
                if(!array_key_exists($opt, $shortOptions))
                    throw new Exception\InvalidCommaindLineOptionException('"-'.$opt.'" is unknown option.');
                if($shortOptions[$opt]==':' || $shortOptions[$opt]=='::') {
                    $j++;
                    if($shortOptions[$opt]==':' && $j>=$arglen) {
                        $j=0;
                        $i++;
                        if($i<$argc) {
                            $arg = $argv[$i];
                            $arglen = strlen($arg);
                        } else {
                            throw new Exception\InvalidCommaindLineOptionException('Option "-'.$opt.'" needs a value.');
                        }
                    }
                    $options = $this->addOpt($options,$opt,substr($arg, $j));
                    break;
                }
                $options = $this->addOpt($options,$opt,false);
            }
        }
        $optind = $i;
        return $options;
    }

    protected function generateShortOptions($shortOptions)
    {
        $len = strlen($shortOptions);
        $options = array();
        for ($i=0; $i < $len; $i++) {
            $opt = substr($shortOptions, $i, 1);
            $value = false;
            if(substr($shortOptions, $i+1, 1)==':') {
                $i++;
                $value = ':';
            }
            if(substr($shortOptions, $i+1, 1)==':') {
                $i++;
                $value = '::';
            }
            $options[$opt] = $value;
        }
        return $options;
    }

    protected function generateLongOptions($longOptions)
    {
        if($longOptions===null)
            return null;
        $options = array();
        foreach ($longOptions as $opt) {
            if(substr($opt,-1)==':') {
                if(substr($opt,-2)=='::') {
                    $value = '::';
                    $opt = substr($opt,0,-2);
                } else {
                    $value = ':';
                    $opt = substr($opt,0,-1);
                }
            } else {
                $value = false;
            }
            $options[$opt] = $value;
        }
        return $options;
    }

    protected function addOpt($options,$opt,$value)
    {
        if(array_key_exists($opt, $options)) {
            if(is_array($options[$opt])) {
                $options[$opt][] = $value;
            } else {
                $options[$opt] = array($options[$opt],$value);
            }
        } else {
            $options[$opt] = $value;
        }
        return $options;
    }

    protected function getLongOpt($argv,$argc,&$i,$opt,$longOptions)
    {
        if($longOptions===null)
            throw new Exception\InvalidCommaindLineOptionException('"--'.$opt.'" is unknown option.');
        $j = strpos($opt, '=');
        if($j===false) {
            $value = false;
        } else {
            $value = substr($opt,$j+1);
            $opt = substr($opt, 0, $j);
        }
        if(!array_key_exists($opt, $longOptions))
            throw new Exception\InvalidCommaindLineOptionException('"--'.$opt.'" is unknown option.');
        if($value===false && ($longOptions[$opt]==':')) {
            $i++;
            if($i<$argc) {
                $value = $argv[$i];
            } else {
                throw new Exception\InvalidCommaindLineOptionException('Option "-'.$opt.'" needs a value.');
            }
        }
        return array($opt,$value);
    }
}