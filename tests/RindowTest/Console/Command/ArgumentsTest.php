<?php
namespace RindowTest\Console\Command\ArgumentsTest;

use PHPUnit\Framework\TestCase;
use Rindow\Console\Command\Arguments;

class Test extends TestCase
{
	public function testNormalOption()
	{
		$cl = new Arguments();
        $argv = array('test.php','-abc','test');
        $this->assertEquals(
            array(
                'a' => false,
                'b' => false,
                'c' => false,
            ),
            $cl->getOpt($argv,'abc')
        );

        $argv = array('test.php','-ac','test');
        $this->assertEquals(
            array(
                'a' => false,
                'c' => false,
            ),
            $cl->getOpt($argv,'abc')
        );

	}

    public function testNormalOptInd()
    {
        $cl = new Arguments();
        $argv = array('test.php','-a','test');
        $this->assertEquals(
            array(
                'a' => false,
            ),
            $cl->getOpt($argv,'ab',null,$optind)
        );
        $this->assertEquals(2,$optind);
        $this->assertEquals('test',$argv[$optind]);

        $argv = array('test.php','-a','-b','test');
        $this->assertEquals(
            array(
                'a' => false,
                'b' => false,
            ),
            $cl->getOpt($argv,'ab',null,$optind)
        );
        $this->assertEquals(3,$optind);
        $this->assertEquals('test',$argv[$optind]);
    }

    /**
     * @expectedException        Rindow\Console\Exception\InvalidCommaindLineOptionException
     * @expectedExceptionMessage "-x" is unknown option.
     */
    public function testShortOptionUnkownOptionFailure1()
    {
        $cl = new Arguments();
        $argv = array('test.php','-x');
        $this->assertEquals(
            array(
            ),
            $cl->getOpt($argv,'a:b',null,$optind)
        );
    }

    public function testMustHasValue()
    {
        $cl = new Arguments();
        $argv = array('test.php','-avx','-b','test');
        $this->assertEquals(
            array(
                'a' => 'vx',
                'b' => false,
            ),
            $cl->getOpt($argv,'a:b',null,$optind)
        );
        $this->assertEquals(3,$optind);
        $this->assertEquals('test',$argv[$optind]);

        $argv = array('test.php','-a','vx','-b','test');
        $this->assertEquals(
            array(
                'a' => 'vx',
                'b' => false,
            ),
            $cl->getOpt($argv,'a:b',null,$optind)
        );
        $this->assertEquals(4,$optind);
        $this->assertEquals('test',$argv[$optind]);

        $argv = array('test.php','-a','-b','test');
        $this->assertEquals(
            array(
                'a' => '-b',
            ),
            $cl->getOpt($argv,'a:b',null,$optind)
        );
        $this->assertEquals(3,$optind);
        $this->assertEquals('test',$argv[$optind]);
    }

    /**
     * @expectedException        Rindow\Console\Exception\InvalidCommaindLineOptionException
     * @expectedExceptionMessage Option "-a" needs a value.
     */
    public function testShortOptionNoValueFailure1()
    {
        $cl = new Arguments();
        $argv = array('test.php','-a');
        $this->assertEquals(
            array(
                'a' => 'v',
                'b' => false,
            ),
            $cl->getOpt($argv,'a:b',null,$optind)
        );
    }

    public function testOptionalValue()
    {
        $cl = new Arguments();
        $argv = array('test.php','-avx','-b','test');
        $this->assertEquals(
            array(
                'a' => 'vx',
                'b' => false,
            ),
            $cl->getOpt($argv,'a::b',null,$optind)
        );
        $this->assertEquals(3,$optind);

        $argv = array('test.php','-a','-b','test');
        $this->assertEquals(
            array(
                'a' => false,
                'b' => false,
            ),
            $cl->getOpt($argv,'a::b',null,$optind)
        );
        $this->assertEquals(3,$optind);

        $argv = array('test.php','-a','test');
        $this->assertEquals(
            array(
                'a' => false,
            ),
            $cl->getOpt($argv,'a::b',null,$optind)
        );
        $this->assertEquals(2,$optind);
        $this->assertEquals('test',$argv[$optind]);
    }

    public function testMultiShortOption()
    {
        $cl = new Arguments();
        $argv = array('test.php','-a','-a','test');
        $this->assertEquals(
            array(
                'a' => array(
                    false,
                    false,
                ),
            ),
            $cl->getOpt($argv,'a',null,$optind)
        );
        $this->assertEquals(3,$optind);
        $this->assertEquals('test',$argv[$optind]);

        $argv = array('test.php','-avx','-a','vz','test');
        $this->assertEquals(
            array(
                'a' => array(
                    'vx',
                    'vz',
                ),
            ),
            $cl->getOpt($argv,'a:b',null,$optind)
        );
        $this->assertEquals(4,$optind);
        $this->assertEquals('test',$argv[$optind]);
    }

    public function testNoOption()
    {
        $cl = new Arguments();
        $argv = array('test.php','--','-a','test');
        $this->assertEquals(
            array(
            ),
            $cl->getOpt($argv,'a',null,$optind)
        );
        $this->assertEquals(2,$optind);
        $this->assertEquals('-a',$argv[$optind]);
    }

    public function testLongOption()
    {
        $cl = new Arguments();
        $argv = array('test.php','--long','test');
        $this->assertEquals(
            array(
                'long' => false,
            ),
            $cl->getOpt($argv,null,array('long'),$optind)
        );
        $this->assertEquals(2,$optind);
        $this->assertEquals('test',$argv[$optind]);

        $argv = array('test.php','-a','--long','test');
        $this->assertEquals(
            array(
                'a' => false,
                'long' => false,
            ),
            $cl->getOpt($argv,'a',array('long'),$optind)
        );
        $this->assertEquals(3,$optind);
        $this->assertEquals('test',$argv[$optind]);
    }

    /**
     * @expectedException        Rindow\Console\Exception\InvalidCommaindLineOptionException
     * @expectedExceptionMessage "--none" is unknown option.
     */
    public function testUnknownLongOption()
    {
        $cl = new Arguments();
        $argv = array('test.php','--none','test');
        $this->assertEquals(
            array(
            ),
            $cl->getOpt($argv,null,array('long'),$optind)
        );
    }

    public function testMustHasValueOfLongOption()
    {
        $cl = new Arguments();
        $argv = array('test.php','--long=vx','test');
        $this->assertEquals(
            array(
                'long' => 'vx',
            ),
            $cl->getOpt($argv,'a',array('long:'),$optind)
        );
        $this->assertEquals(2,$optind);
        $this->assertEquals('test',$argv[$optind]);

        $argv = array('test.php','--long','vx','test');
        $this->assertEquals(
            array(
                'long' => 'vx',
            ),
            $cl->getOpt($argv,'a',array('long:'),$optind)
        );
        $this->assertEquals(3,$optind);
        $this->assertEquals('test',$argv[$optind]);
    }

    /**
     * @expectedException        Rindow\Console\Exception\InvalidCommaindLineOptionException
     * @expectedExceptionMessage Option "-long" needs a value.
     */
    public function testLongOptionNoValueFailure()
    {
        $cl = new Arguments();
        $argv = array('test.php','--long');
        $this->assertEquals(
            array(
            ),
            $cl->getOpt($argv,null,array('long:'),$optind)
        );
    }

    public function testOptionalValueOfLongOption()
    {
        $cl = new Arguments();
        $argv = array('test.php','--long=vx','test');
        $this->assertEquals(
            array(
                'long' => 'vx',
            ),
            $cl->getOpt($argv,'a',array('long::'),$optind)
        );
        $this->assertEquals(2,$optind);
        $this->assertEquals('test',$argv[$optind]);

        $argv = array('test.php','--long','vx','test');
        $this->assertEquals(
            array(
                'long' => false,
            ),
            $cl->getOpt($argv,'a',array('long::'),$optind)
        );
        $this->assertEquals(2,$optind);
        $this->assertEquals('vx',$argv[$optind]);

        $argv = array('test.php','--long=vx','test');
        $this->assertEquals(
            array(
                'long' => 'vx',
            ),
            $cl->getOpt($argv,'a',array('long'),$optind)
        );
        $this->assertEquals(2,$optind);
        $this->assertEquals('test',$argv[$optind]);
    }

    public function testCommandLineObject()
    {
        $cl = new Arguments(array('test.php','-a','test'),'a');
        $this->assertEquals(array('test.php','-a','test'),$cl->getArgv());
        $this->assertEquals('test.php',$cl->getCommand());
        $this->assertEquals(array('a'=>false),$cl->getOptions());
        $this->assertEquals(array('test'),$cl->getArguments());

        $cl = new Arguments(array('test.php','subcom','-a','test'),'a');
        $this->assertEquals('test.php',$cl->shift());
        $this->assertEquals(array('subcom','-a','test'),$cl->getArgv());
        $this->assertEquals('subcom',$cl->getCommand());
        $this->assertEquals(array('a'=>false),$cl->getOptions());
        $this->assertEquals(array('test'),$cl->getArguments());

        $cl = new Arguments(array('test.php'),'a');
        $this->assertEquals('test.php',$cl->shift());
        $this->assertFalse($cl->getCommand());
    }
}
