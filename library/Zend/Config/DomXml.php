<?php
Class Zend_Config_DomXml extends Zend_Config {
    
    protected $valueKey = '_value';
    
    
    public function __construct($configSrc, $section = null, $allowModifications = false) {
        
        if (empty($configSrc)) {
            /**
             * @see Zend_Config_Exception
             */
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception('Filename/DomDocument is not set');
        }
        
        if (!$configSrc instanceof DOMDocument) {
            $dom = new DOMDocument();
            if (!$dom->load($configSrc)) {
                throw new Zend_Config_Exception('Could not parse ' . $filename);
            }
            
            // evaluate xincludes
            $dom->xinclude();
            
        } else {
            $dom = $configSrc;
        }
        
        $dataArray = array();
        if (null === $section) {
            foreach($this->_getSections($dom) as $sectionName => $sectionElement) {
                $dataArray[$sectionName] = $this->_toArray($sectionElement);
            }
            
        } else if (is_array($section)) {
            // TODO
            
        } else {
            $dataArray = $this->_processExtends($dom, $section);
        }
        
        parent::__construct($dataArray, $allowModifications);
        $this->_loadedSection = $section;
        
    }
    
    
    /**
     * Process Sections extending others and merges 
     * the values with previously retrieved config params passed
     * as argument.
     * 
     * @param   DOMDocument $dom            config domxml
     * @param   String      $sectionName    section name
     * @param   Array       $config         config params to merge with
     */
    private function _processExtends(DOMDocument $dom, $sectionName, $config=array()) {
        
        $section = $this->_getSection($dom, $sectionName);
        if (!$section) {
            throw new Zend_Config_Exception("Section '$sectionName' cannot be found");
        }
        
        if ($section->hasAttribute('extends')) {
            $extSectionName = $section->getAttribute("extends");
            if ($extSectionName) {
                $config = $this->_processExtends($dom, $extSectionName, $config);
            }
        }
        
        $sectionArr = $this->_toArray($section);
        if (is_string($sectionArr)) {
            // Section is empty
            $sectionArr = array();
        }
        
        $config = $this->_arrayMergeRecursive($config, $sectionArr);
        return $config;
        
    }
    
    
    /**
     * Recursively serializes a domnode to an associative array
     * 
     * Element attributes are ignored.
     * 
     * @param   DOMNode $node domnode
     * @return  Array
     * @access  private
     */
    private function _toArray(DOMNode $node) {
        $value = array();
        if ($node->hasAttributes()) {
            foreach($node->attributes as $attribute) {
                // skip `extends' attribute
                if ($attribute->name === 'extends') continue;
                $value[$attribute->name] = $attribute->value;
            }
        }
        
        if ($node->hasChildNodes()) {
            foreach($node->childNodes as $child) {
                
                if (null !== $child && $child->hasChildNodes()) {
                    
                    $v = $this->_toArray($child);
                    if (isset($value[$child->nodeName])) {
                        if (!is_array($value[$child->nodeName])) {
                            $value[$child->nodeName] = array($value[$child->nodeName]);
                        }
                        
                        array_push($value[$child->nodeName], $v);
                        
                    } else {
                        $value[$child->nodeName] = $v;
                    }
                    
                } else if ($node->childNodes->length == 1) {
                    $v = trim($child->nodeValue);
                    // value containes attributes
                    if (!empty($value)) {
                        $value[$this->valueKey] = $v;
                    } else {
                        $value = $v;
                    }
                }
            }
        } 
        
        return $value;
    }
    
    
    /**
    * Get all config sections from the domdocument
    *   
    * Sections are defined in the XML as children of the root element.
    * Returns an associative array with the section-names as keys
    * and the section domelements as values
    * 
    * @param    DOMDocument $dom    config domxml
    * @return   Array
    * @access   private
    */
    private function _getSections(DOMDocument $dom) {
        $xp = new DOMXPath($dom);
        $sections = array();
        $sectionlist = $xp->auery("/*/*");
        foreach($sectionlist as $section) {
            $sections[$section->nodeName] = $section;
        }
        return $sections;
    }
    
    
    /**
    * Get a particular config section from the config domxml by name 
    * 
    * @param    DOMDocument $dom            config domxml
    * @param    String      $sectionName    config section name
    * @return   DOMNode 
    * @access   private
    */
    private function _getSection(DOMDocument $dom, $sectionName) {
        $xp = new DOMXPath($dom);
        $sectionlist = $xp->query("/*/".$sectionName);
        if ($sectionlist->length > 0) {
            return $sectionlist->item(0);
        }
        
        return null;
    }
    
    
    /** 
     * Merge two arrays recursively, overwriting keys of the same name
     * in $array1 with the value in $array2.
     *
     * @param  array $array1
     * @param  array $array2
     * @return array
     */
    protected function _arrayMergeRecursive($array1, $array2) {
        if (is_array($array1) && is_array($array2)) {
            foreach ($array2 as $key => $value) {
                if (isset($array1[$key])) {
                    $array1[$key] = $this->_arrayMergeRecursive($array1[$key], $value);
                } else {
                    $array1[$key] = $value;
                }
            }
        } else {
            $array1 = $array2;
        }
        return $array1;
    }
    
    
    
    
    
}
?>
