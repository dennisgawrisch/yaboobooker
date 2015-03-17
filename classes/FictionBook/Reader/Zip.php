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
class FictionBook_Reader_Zip extends FictionBook_Reader_Proxy {
    public function __construct($filename) {
        $archive = new ZipArchive;
        if (!$archive->open($filename)) {
            throw new FictionBook_Reader_Exception("Could not open ZIP archive");
        }
        if ($archive->numFiles < 1) {
            throw new FictionBook_Reader_Exception("ZIP archive is empty");
        }
        for ($i = 0; $i < $archive->numFiles; $i++) {
            $stat = $archive->statIndex($i);
            if (FALSE === $stat) {
                throw new FictionBook_Reader_Exception("Could not obtain information about the file in the archive");
            }
            if (preg_match("/\\.fb2$/", $stat["name"])) {
                $contents = $archive->getFromIndex($i);
                if (FALSE === $contents) {
                    throw new FictionBook_Reader_Exception("Could not get contents of the file in the archive");
                }
                $this->reader = new FictionBook_Reader_Xml_String($contents);
                break;
            }
        }
        if (empty($this->reader)) {
            throw new FictionBook_Reader_Exception("Could not find .fb2 file in the archive");
        }
    }
}