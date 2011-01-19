<?php 
Class Zend_Application_Resource_Log extends Zend_Application_Resource_ResourceAbstract {
    
    protected $_logger = array();
    protected $_loggerClass = 'Zend_Log';
    
    
    public function init() {
    }
    
    
    public function getPluginLoader() {
        if ($this->_pluginLoader === null) {
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array());
        }
        return $this->_pluginLoader;
    }   
     
    
    public function setLoggerClass($class) {
        return ($htis->_loggerClass = $class);
    }
    
    public function getLoggerClass() {
        return $this->_loggerClass;
    }
    
    public function hasLogger($name) {
        return (isset($this->_logger[$name]));
    }
    
    
    public function setLogger($name, $logger) {
        return ($this->_logger[$name] = $logger);
    }
    
    
    public function getLogger($name) {
        if (!$this->hasLogger($name)) {
            $logger = $this->createLogger($name);
            if (null === $logger) {
                return null;
            }
            $this->setLogger($name, $logger);    
        }
        
        return $this->_logger[$name];
    }
    
    
    public function getLoggerOptions($name) {
        return (isset($this->_options[$name])) ? $this->_options[$name] : null;
    }
    
    
    public function createLogger($name) {
        $opts = $this->getLoggerOptions($name);
        if (null === $opts || !isset($opts['writer']['type'])) {
            return null;
        }
        
        // FIXME: allow for multiple writers
        $writerCfg = $opts['writer'];
        $params = (isset($writerCfg['params'])) ? $writerCfg['params'] : array();
        if (!is_array($params)) {
            $params = array($params);
        }
        
        $writer = $this->_createClass($writerCfg['type'], $params);
        if (null === $writer) {
            return null;
        }
        
        if (isset($writerCfg['formatter']['type'])) {
            $params = (isset($writerCfg['formatter']['params'])) ? $writerCfg['formatter']['params'] : array();
            if (!is_array($params)) {
                $params = array($params);
            }
            $formatter = $this->_createClass($writerCfg['formatter']['type'], $params);
            if (null !== $writer) {
                $writer->setFormatter($formatter);
            }
        }
        
        if (isset($writerCfg['priority'])) {
            $prio = (int) $writerCfg['priority'];
            $filter = new Zend_Log_Filter_Priority($prio);
            $writer->addFilter($filter);
        }
        
        $lc = (isset($opts['logger'])) ? $opts['logger'] : $this->getLoggerClass();
        if (!class_exists($lc)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($lc);
        }
        
        return new $lc($writer);
        
    }
    
    
    protected function _createClass($class, $opts=array()) {
        if (!class_exists($class)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($class);   
        }
        
        if (empty($opts)) {
            return new $class;
        } else {
            try {
                $ref = new ReflectionClass($class);
                return $ref->newInstanceArgs($opts);
            } catch(ReflectionException $ex) {
                error_log($ex->getMessage());
            } catch(Zend_Log_Exception $ex) {
                error_log( $ex->getMessage());
            }
        }
        return null;
    }
    
    
    

}