<?php
Class Zend_Log_Writer_Logfile extends Zend_Log_Writer_StreamRotatable {

    protected $_logdir;
    protected $_logfileFormat = '';
    protected $_Logfile = '';
    
    public function __construct($logdir, $logfileFormat) {
        $this->setLogDir($logdir);
        $this->setLogfileFormat($logfileFormat);
        parent::__construct();
    }
    
    public function setLogdir($dir) {
        if (is_dir($dir)) {
            return ($this->_logdir = $dir);
        }
        return false;
    }
    public function setLogfileFormat($format) {
        return ($this->_logfileFormat = $format);
    }
    
    public function formatLogFilename($param) {
        $out = $this->_logfileFormat;
        if (empty($out)) {
            return null;
        }
        foreach($param as $key => $value) {
            if ((is_object($value) && !method_exists($value,'__toString'))
                || is_array($value)) {
                $value = gettype($value);
            }
            $out = str_replace("%$key%", $value, $out);
        }
        return $out;
    }
    
    public function formatLogFile($param) {
        $fn = $this->formatLogFilename($param);
        if (null !== $fn) {
            return $this->_logdir . DIRECTORY_SEPARATOR . $fn;
        }
        return null;
    }
    
    public function setLogfile($file) {
        return ($this->_logfile = $file);    
    }
    
    public function getLogfile() {
        return $this->_logfile;
    }
    
    public function rotate($param, $mode=NULL) {
       $f = $this->formatLogFile($param);
       $this->setLogfile($f);
       return parent::rotate($f, $mode);
    }
}