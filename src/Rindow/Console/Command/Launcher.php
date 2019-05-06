<?php
namespace Rindow\Console\Command;

use Rindow\Container\ModuleManager;

class Launcher
{
    static public function run($config,$namespace,$argv)
    {
        try {
            $moduleManager = new ModuleManager($config);
            $app = $moduleManager->getServiceLocator()->get('Rindow\\Console\\Command\\DefaultDispatcher');
            $exitcode = $app->run($namespace,$argv);
        } catch(\Exception $e) {
            while(true) {
                echo 'Exception: '.get_class($e)."\n";
                echo $e->getMessage()."(".$e->getCode().")\n";
                echo "Source: ".$e->getFile()."(".$e->getLine().")\n";
                echo "Stack Trace:\n";
                echo $e->getTraceAsString()."\n";
                $e = $e->getPrevious();
                if($e==null)
                    break;
                echo "##### Previous Exception ####\n";
            }
            $exitcode = -1;
        }
        return $exitcode;
    }
}
