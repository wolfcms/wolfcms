<?php

class SimpleXMLExtended extends SimpleXMLElement
{
  public function addCData($nodename,$cdata_text)
  {
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
$xmlobj->addAttribute('version', '0.5.0');

global $__CMS_CONN__;
Record::connection($__CMS_CONN__);

foreach ($tablenames as $tablename) {
    $table = Record::query('SELECT * FROM '.TABLE_PREFIX.$tablename);

    while($entry = $table->fetchObject()) {
        $child = $xmlobj->addChild($tablename);
        if (isset($entry->id)) {
            $child->addAttribute('id', $entry->id);
        }
        while (list($key, $val) = each($entry)) {
            if ($key !== 'id') {
                if ($key === 'content' || $key === 'content_html') {
                    $child->addCData($key,$val);
                }
                else {
                    $child->addChild($key,$val);
                }
            }
        }
    }
}

print header("Content-type: text/plain") . $xmlobj->asXML(); // Place future code ABOVE this line

?>