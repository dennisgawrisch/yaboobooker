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
class FictionBook_Validator {
    private $dom, $namespace, $errors;

    public function __construct(DOMDocument $dom) {
        $this->dom = $dom;
    }

    private function addLibxmlErrors($errors = NULL) {
        if (is_null($errors)) {
            $errors = libxml_get_errors();
        }
        foreach ($errors as $error) {
            $this->errors []= array(
                "line"      => $error->line,
                "message"   => trim(str_replace("{{$this->namespace}}", "", $error->message)),
            );
        }
    }

    public function isValid() {
        $this->errors = array();
        libxml_use_internal_errors(TRUE);
        $errors = libxml_get_errors();
        if (!empty($errors)) {
            $this->addLibxmlErrors($errors);
            return FALSE;
        } else {
            $this->namespace = $this->dom->documentElement->getAttribute("xmlns");

            if (preg_match("!^http://www\\.gribuser\\.ru/xml/fictionbook/(.+)$!", $this->namespace, $matches)) {
                $fictionbook_version = $matches[1];
            } else {
                $fictionbook_version = "2.0"; // just in case
            }

            $schema_directory = SCHEMAS_DIR . DIRECTORY_SEPARATOR . $fictionbook_version;
            if (!file_exists($schema_directory)) {
                $this->errors []= array(
                    "line"      => 1,
                    "message"   => "Unknown FictionBook version: $fictionbook_version",
                );
                return FALSE;
            }

            if ($this->dom->schemaValidate($schema_directory . DIRECTORY_SEPARATOR . "FictionBook.xsd")) {
                $this->extraValidate();
                return empty($this->errors);
            } else {
                $this->addLibxmlErrors();
                return FALSE;
            }
        }
    }

    public function getValidationErrors() {
        return $this->errors;
    }

    /**
     * Функция взята из валидатора, предложенного на http://lib.rus.ec/soft
     * @todo переработать
     */
    private function extraValidate() {
        $xpath = new DOMXpath($this->dom);
        $xpath->registerNamespace("xlink", "http://www.w3.org/1999/xlink");
        $xpath->registerNamespace("l", "http://www.w3.org/1999/xlink");
        $href_list = array();
        $hrefs = array();
        $elements = $xpath->query("//*[@xlink:href|@l:href]");
        if (!is_null($elements)) {
        foreach ($elements as $element) {
        $name = $element->nodeName;
        $type = $element->getAttribute("type");
        $link = $element->getAttribute("l:href");
        if (!$link) {
        $link = $element->getAttribute("xlink:href");
        }
        array_push($href_list, array("name"=>$name,
        "type"=>$type,
        "link"=>$link));
        array_push($hrefs, $link);
        }
        }
        $id_list = array();
        $ids = array();
        $elements = $xpath->query("//*[@id]");
        if (!is_null($elements)) {
        foreach ($elements as $element) {
        $name = $element->nodeName;
        $id = $element->getAttribute("id");
        array_push($id_list, array("name"=>$name, "id"=>$id));
        array_push($ids, $id);
        }
        }
        foreach ($href_list as $h) {
        $name = $h["name"];
        $type = $h["type"];
        $link = $h["link"];
        if (!$link) {
        $this->errors []= array("line" => 0, "message" => "Empty link.");
        continue;
        }
        if (substr($link, 0, 1) != "#") {
        if ($name == "image") {
        # внешнее изображение
        $this->errors []= array("line" => 0, "message" => "External image: $link.");
        }
        if ($type == "note") {
        # сноска на внешний источник
        $this->errors []= array("line" => 0, "message" => "External note: $link.");
        }
        if (substr($link, 0, strlen("http:")) != "http:" &&
        substr($link, 0, strlen("https:")) != "https:" &&
        substr($link, 0, strlen("ftp:")) != "ftp:" &&
        substr($link, 0, strlen("mailto:")) != "mailto:") {
        # плохая внешняя ссылка
        $this->errors []= array("line" => 0, "message" => "Bad external link: $link.");
        }
        } else {
        # href вез соответствующего id
        $ln = substr($link, 1);
        if (!in_array($ln, $ids)) {
        $this->errors []= array("line" => 0, "message" => "Bad internal link: $link.");
        }
        }
        }
        # все ли изображения прилинкованы и будут использованы
        foreach ($id_list as $tag) {
        $name = $tag["name"];
        $id = $tag["id"];
        if ($name == "binary" && !in_array("#".$id, $hrefs)) {
        $this->errors []= array("line" => 0, "message" => "Not linked image: $id.");
        }
        }
    }
}