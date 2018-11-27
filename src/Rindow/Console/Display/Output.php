<?php
namespace Rindow\Console\Display;

class Output
{
    protected $encoding;
    protected $translator;
    protected $stream;

    public function __construct()
    {
        $this->stream = STDOUT;
    }

    public function setStream($stream)
    {
        $this->stream = $stream;
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function printf()
    {
        $args = func_get_args();
        if(count($args)==0)
            throw new Exception\InvalidArgumentException('Argument 1 must be specified format.');
        $format = array_shift($args);
        if($this->translator) {
            $format = $this->translator->translate($format);
        }
        array_unshift($args, $format);
        $this->write(call_user_func_array('sprintf',$args));
    }

    public function printfln()
    {
        $args = func_get_args();
        call_user_func_array(array($this,'printf'),$args);
        $this->write("\n");
    }

    public function writeln($text)
    {
        $this->write($text."\n");
    }

    public function write($text)
    {
        if($this->encoding)
            $text = mb_convert_encoding($text,$this->encoding);
        $this->writeRaw($text);
    }

    public function writeRaw($text)
    {
        fwrite($this->stream, $text);
    }
}
