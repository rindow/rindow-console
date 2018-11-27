<?php
namespace RindowTest\Console\Display\OutputTest;

use PHPUnit\Framework\TestCase;
use Rindow\Console\Display\Output;

class TestTranslator
{
	public function translate($text)
	{
		return 'translated('.$text.')';
	}
}

class Test extends TestCase
{
	public function getOutput()
	{
		$output = new Output();
		$output->setStream(fopen('php://temp', 'w+b'));
		return $output;
	}

    public function getContents($output)
    {
        $stream = $output->getStream();
        fseek($stream, 0);
        $text = '';
        while ($t=fread($stream, 8192)) {
            $text .= $t;
        }
        return $text;
    }

    public function testWriteln()
    {
    	$output = $this->getOutput();	
    	$output->writeln('foo');
    	$output->writeln('bar');
    	$contents = $this->getContents($output);
    	$this->assertEquals("foo\nbar\n",$contents);
    }

    public function testPrintfln()
    {
    	$output = $this->getOutput();	
    	$output->printfln('1:%s,2:%s','foo','bar');
    	$contents = $this->getContents($output);
    	$this->assertEquals("1:foo,2:bar\n",$contents);
    }

    public function testTranslator()
    {
    	$output = $this->getOutput();
    	$output->setTranslator(new TestTranslator());
    	$output->printfln('1:%s,2:%s','foo','bar');
    	$contents = $this->getContents($output);
    	$this->assertEquals("translated(1:foo,2:bar)\n",$contents);
    }
}