<?php

class SimpleXMLExtended extends SimpleXMLElement {
    public function addCData($nodename,$cdata_text) {
        $node = $this->addChild($nodename); //Added a nodename to create inside the function
        $node = dom_import_simplexml($node);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }
}

$tablenames = array('layout', 'page', 'page_part', 'page_tag', 'permission',
                    'plugin_settings', 'setting', 'snippet', 'tag', 'user', 'user_permission');


$xmltext = '<?xml version="1.0" encoding="UTF-8"?><wolfcms></wolfcms>';
$xmlobj = new SimpleXMLExtended($xmltext);
$xmlobj->addAttribute('version', '0.5.5');

global $__CMS_CONN__;
Record::connection($__CMS_CONN__);

$lasttable = '';

foreach ($tablenames as $tablename) {
    $table = Record::query('SELECT * FROM '.TABLE_PREFIX.$tablename);

    while($entry = $table->fetchObject()) {
        if ($lasttable !== $tablename) {
            $lasttable = $tablename;
            $child = $xmlobj->addChild($tablename.'s');
        }
        $subchild = $child->addChild($tablename);
//        if (isset($entry->id)) {
//            $subchild->addAttribute('id', $entry->id);
//        }
        while (list($key, $val) = each($entry)) {
//            if ($key !== 'id') {
                if ($key === 'content' || $key === 'content_html') {
                    $subchild->addCData($key,$val);
                }
                else {
                    $subchild->addChild($key,$val);
                }
//            }
        }
    }
}


use_helper('Zip');

//$zip = new Zip();
//$zip->clear();
//$zip->addFile($xmlobj->asXML(), 'wolfcms-backup.xml');
//$zip->download('wolfcms-backup.zip');



print header("Content-type: text/plain");
print "--------------------[ EXPORTING TO XML]--------------------------\n\r";
print $xmlobj->asXML(); // Place future code ABOVE this line
print "\n\r--------------------[ GENERATING IMPORT STATEMENTS ]--------------------------\n\r";

$import = $xmlobj->asXML();

$xml = simplexml_load_string($import);

// Import each table and table entry
foreach($tablenames as $tablename) {
    $container = $tablename.'s';
    if ($__CMS_CONN__->exec('TRUNCATE '.$tablename) === false) {
        echo '\r\rERROR TRUNCATING TABLE';
        exit;
    }

    if (array_key_exists($container, $xml)) {
        foreach ($xml->$container->$tablename as $element) {
            $keys = array();
            $values = array();
            foreach ($element as $key => $value) {
                $keys[] = $key;
            if ($key == 'name' && $value == 'TESTnone') { $value = 'TEST'.$value; }
            if ($key == 'name' && $value == 'Administrator') { $value = 'Martijn van der Kleijn'; }
                $values[] = $__CMS_CONN__->quote($value);
            }
            $sql = 'INSERT INTO '.$tablename.' ('.join(', ', $keys).') VALUES ('.join(', ', $values).')'."\r";
            echo $sql;

            /*
             * TODO - execute SQL for new TEST database
             */
            if ($__CMS_CONN__->exec($sql) === false) {
                echo '\r\rERROR IMPORTING TABLE CONTENTS';
                exit;
            }
        }
    }
}

?>