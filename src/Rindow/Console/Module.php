<?php
namespace Rindow\Console;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'components' => array(
                    'Rindow\\Console\\Command\\DefaultDispatcher' => array(
                        'class' => 'Rindow\\Console\\Command\\Dispatcher',
                        'properties' => array(
                            'serviceLocator' => array('ref'=>'ServiceLocator'),
                            'config' => array('config'=>'console::commands'),
                            'output' => array('ref'=>'Rindow\\Console\\Display\\DefaultOutput'),
                        ),
                    ),
                    'Rindow\\Console\\Display\\DefaultOutput' => array(
                        'class' => 'Rindow\\Console\\Display\\Output',
                        // If you need ...
                        //'properties' => array(
                        //    'translator' => array('ref'=>'I18nMessageTranslator'),
                        //    'encoding'   => array('config'=>'console::output::encoding'),
                        //),
                    ),
                ),
            ),
        );
    }
}
