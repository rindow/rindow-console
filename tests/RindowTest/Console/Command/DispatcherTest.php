<?php
namespace RindowTest\Console\Command\DispatcherTest;

use PHPUnit\Framework\TestCase;
use Rindow\Console\Command\Dispatcher;
use Rindow\Stdlib\Dict;
use Rindow\Console\Exception\InvalidCommaindLineOptionException;

class TestOutput
{
	protected $log = array();
	public function getLog()
	{
		return $this->log;
	}
	public function printfln()
	{
        $args = func_get_args();
        $this->log[] = implode(',', $args);
	}
    public function printf()
    {
        $args = func_get_args();
        $this->log[] = implode(',', $args);
    }
    public function writeln($text)
    {
        $this->log[] = $text;
    }
    public function write($text)
    {
        $this->log[] = $text;
    }
}

class TestApp
{
	protected $output;
	public function setOutput($output)
	{
		$this->output = $output;
	}
	public function printHello($argv)
	{
		$argstr = implode(',', $argv);
		$this->output->writeln('hello('.$argstr.')');
		return 123456;
	}
	public function throwError($argv)
	{
		throw new InvalidCommaindLineOptionException("Error in TestApp");
	}
}

class Test extends TestCase
{
	public function getDispatcher($config)
	{
		$container = new Dict();
		$output = new TestOutput();
		$app = new TestApp();
		$app->setOutput($output);
		$container->set('TestApp',$app);
		$dispatcher = new Dispatcher();
		$dispatcher->setOutput($output);
		$dispatcher->setServiceLocator($container);
		$dispatcher->setConfig($config);
		return array($output,$dispatcher);
	}

	public function testNormal()
	{
		$config = array(
			'TestNamespace' => array(
				'hello' => array(
					'component' => 'TestApp',
					'method' => 'printHello',
				),
			),
		);
		list($output,$dispatcher) = $this->getDispatcher($config);
		$argv = array('bincmd','hello','foo','bar');
		$return = $dispatcher->run('TestNamespace',$argv);
		$this->assertEquals(123456,$return);
		$this->assertEquals(array(
			'hello(hello,foo,bar)',
		),$output->getLog());
	}

	public function testDefaultUsage()
	{
		$config = array(
			'TestNamespace' => array(
				'hello' => array(
					'component' => 'TestApp',
					'method' => 'printHello',
				),
			),
		);
		list($output,$dispatcher) = $this->getDispatcher($config);
		$argv = array('bincmd');
		$return = $dispatcher->run('TestNamespace',$argv);
		$this->assertEquals(array(
			"Usage:\n".
			"command [options] [arguments]\n".
			"\n".
			"Available commands:\n".
			"hello\n"
		),$output->getLog());
	}

	public function testCommandNotFound()
	{
		$config = array(
			'TestNamespace' => array(
				'hello' => array(
					'component' => 'TestApp',
					'method' => 'printHello',
				),
			),
		);
		list($output,$dispatcher) = $this->getDispatcher($config);
		$argv = array('bincmd','notfoundcmd');
		$return = $dispatcher->run('TestNamespace',$argv);
		$this->assertEquals(array(
			'command not found: notfoundcmd'
		),$output->getLog());
	}

    /**
     * @expectedException        Rindow\Console\Exception\DomainException
     * @expectedExceptionMessage component not specified for "hello"
     */
	public function testNoComponent()
	{
		$config = array(
			'TestNamespace' => array(
				'hello' => array(
					'method' => 'printHello',
				),
			),
		);
		list($output,$dispatcher) = $this->getDispatcher($config);
		$argv = array('bincmd','hello');
		$return = $dispatcher->run('TestNamespace',$argv);
	}

    /**
     * @expectedException        Rindow\Console\Exception\DomainException
     * @expectedExceptionMessage Invalid command handler for "hello".: TestApp->invalid
     */
	public function testInvalidComponentOrMethod()
	{
		$config = array(
			'TestNamespace' => array(
				'hello' => array(
					'component' => 'TestApp',
					'method' => 'invalid',
				),
			),
		);
		list($output,$dispatcher) = $this->getDispatcher($config);
		$argv = array('bincmd','hello');
		$return = $dispatcher->run('TestNamespace',$argv);
	}

	public function testThrowError()
	{
		$config = array(
			'TestNamespace' => array(
				'error' => array(
					'component' => 'TestApp',
					'method' => 'throwError',
				),
			),
		);
		list($output,$dispatcher) = $this->getDispatcher($config);
		$argv = array('bincmd','error','foo','bar');
		$return = $dispatcher->run('TestNamespace',$argv);
		$this->assertEquals(array(
			'Error in TestApp',
		),$output->getLog());
	}
}
