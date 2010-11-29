<?php
Class Zend_Log_Writer_Logfile extends Zend_Log_Writer_StreamRotatable {

    protected $_rotateDir;
    protected $_rotateFormat = '';
    protected $_logfile = '';
    
    /**
     * Constructor
     * 
     * @param logfile               logfile
     * @param rotateDir             directory where to move rotated logfile
     * @param rotateFormat      filename format for rotated logfile
     * @param mode                  mode
     * @throws Zend_Log_Exception
     */
    public function __construct($logfile, $rotateDir, $rotateFormat, $mode=null) {
        parent::__construct($logfile, $mode);
        $this->setRotateDir($rotateDir);
        $this->setRotateFormat($rotateFormat);
        $this->setLogfile($logfile);
    }
    
    
    public function setRotateDir($dir) {
        if (is_dir($dir)) {
            return ($this->_rotateDir = $dir);
        }
        return false;
    }
    
    
    public function setRotateFormat($format) {
        return ($this->_rotateFormat = $format);
    }
    
    
    public function formatRotateFilename($param) {
        $out = $this->_rotateFormat;
        
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
    
    
    public function formatRotateFile($param) {
        $fn = $this->formatRotateFilename($param);
        if (null !== $fn) {
            return $this->_rotateDir . DIRECTORY_SEPARATOR . $fn;
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
        $this->shutdown();
        $logf = $this->getLogfile();
        if (file_exists($logf)) {
            $rotateFile = $this->formatRotateFile($param);
            $rotateDir = dirname($rotateFile);
            if (!is_dir($rotateDir) && !mkdir($rotateDir, 0755, true)) {
                require_once 'Zend/Log/Exception.php';
                $msg = "Rotate dir \"$rotateDir\" can not be created!";
                throw new Zend_Log_Exception($msg);
            }
            rename($logf, $rotateFile);    
        }
        $this->open($logf, $mode);
    }
}