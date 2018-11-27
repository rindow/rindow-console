<?php
namespace Rindow\Console\Command;

use Rindow\Console\Exception;

class Dispatcher
{
    protected $config;
    protected $serviceLocator;
    protected $output;

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
    
    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function run($namespace,array $argv)
    {
        try {
            if(!isset($this->config[$namespace])) {
                throw new Exception\DomainException('namespace not found: '.$namespace);
            }
            array_shift($argv);
            $args = new Arguments($argv);
            $command = $args->getCommand();
            if(!$command) {
                $message = "Usage:\n";
                $message .= "command [options] [arguments]\n";
                $message .= "\n";
                $message .= "Available commands:\n";
                $message .= implode(', ',array_keys($this->config[$namespace]))."\n";
                throw new Exception\InvalidCommaindLineOptionException($message);
            }
            if(!isset($this->config[$namespace][$command])) {
                throw new Exception\InvalidCommaindLineOptionException('command not found: '.$command);
            }
            $route = $this->config[$namespace][$command];
            if(!isset($route['component']))
                throw new Exception\DomainException('component not specified for "'.$command.'"');
            $component = $this->serviceLocator->get($route['component']);
            if(isset($route['method']))
                $func = array($component,$route['method']);
            else
                $func = $component;
            if(!is_callable($func))
                throw new Exception\DomainException('Invalid command handler for "'.$command.'".: '.$route['component'].(isset($route['method'])?('->'.$route['method']):''));
            return call_user_func($func,$argv);
        } catch(Exception\InvalidCommaindLineOptionException $e) {
            $this->output->printfln($e->getMessage());
        }
    }
}