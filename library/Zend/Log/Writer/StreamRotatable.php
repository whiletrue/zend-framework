<?php
Class Zend_Log_Writer_StreamRotatable extends Zend_Log_Writer_Stream {

    
    /**
     * Class Constructor
     *  
     * @param  streamOrUrl          Stream or URL to open as a stream
     * @param  mode                 Mode, only applicable if a URL is given
     * @throws Zend_Log_Exception
     */
    public function __construct($streamOrUrl=null, $mode=NULL) {
        if (!empty($streamOrUrl)) {
            $this->open($streamOrUrl, $mode);
        }
        $this->_formatter = new Zend_Log_Formatter_Simple();
    }
    
        
    /**
     * Rotate stream
     * 
     * @param   newStreamUrl    New stream or url
     * @param   mode            Mode
     * @throws  Zend_Log_Exception
     */
    public function rotate($newStreamOrUrl, $mode=NULL) {
        $this->shutdown();
        return $this->open($newStreamOrUrl, $mode);    
    }
    
    
    /**
     * Open the stream resource or url
     * 
     * @param  streamOrUrl          Stream or URL to open as a stream
     * @param  mode                 Mode, only applicable if a URL is given
     * @throws Zend_Log_Exception
     */
    public function open($streamOrUrl, $mode=NULL) {
        // Setting the default
        if ($mode === NULL) {
            $mode = 'a';
        }
        
        if (is_resource($streamOrUrl)) {
            if (get_resource_type($streamOrUrl) != 'stream') {
                require_once 'Zend/Log/Exception.php';
                throw new Zend_Log_Exception('Resource is not a stream');
            }

            if ($mode != 'a') {
                require_once 'Zend/Log/Exception.php';
                throw new Zend_Log_Exception('Mode cannot be changed on existing streams');
            }

            $this->_stream = $streamOrUrl;
        } else {
            if (is_array($streamOrUrl) && isset($streamOrUrl['stream'])) {
                $streamOrUrl = $streamOrUrl['stream'];
            }

            if (! $this->_stream = @fopen($streamOrUrl, $mode, false)) {
                require_once 'Zend/Log/Exception.php';
                $msg = "\"$streamOrUrl\" cannot be opened with mode \"$mode\"";
                throw new Zend_Log_Exception($msg);
            }
        }
    }

}