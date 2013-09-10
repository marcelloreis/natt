<?php
App::uses('PaginatorHelper', 'View/Helper');

class AppPaginatorHelper extends PaginatorHelper{
    public function numbers($options = array()) {
        $output = parent::numbers($options);

        // get the current page number, and create a link with it
        $current = $this->current();
        $currentLink = $this->link("&raquo;&nbsp;{$current}&nbsp;&laquo;", array('page' => $current), array('escape' => false));

        // if you're using cake pre 2.1 you cannot change the current class with
        // the options array, so it will always be "current"
        $find = "<li class=\"current\">{$current}</li>";
        $replace = "<li class=\"current\">{$currentLink}</li>"; 

        $output = str_replace($find, $replace, $output);

        return $output;
    }
}