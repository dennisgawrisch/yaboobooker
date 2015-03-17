<?php
/*
 * Yaboobooker
 * Copyright © 2009–2010 bes island <besisland@besisland.name>
 *
 * This program is distributed under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 3.
 *
 * See other credits and licensing information in CREDITS,
 * and the copy of the GNU General Public License in LICENSE.
*/
class FictionBook_Reader_Xml_String extends FictionBook_Reader_Abstract {
    public function __construct($string) {
        $this->code = $string;
    }

    public function load() {
        $this->dom = new DOMDocument;
        if (!$this->dom->loadXml($this->code)) {
            $this->dom = NULL;
        }
    }
}