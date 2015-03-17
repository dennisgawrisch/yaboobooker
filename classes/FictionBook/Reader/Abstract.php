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
abstract class FictionBook_Reader_Abstract implements FictionBook_Reader_Interface {
    protected $code, $dom;

    public function getCode() {
        return $this->code;
    }

    public function getDom() {
        return $this->dom;
    }
}