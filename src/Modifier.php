<?php
    namespace PiperLang;

    /**
     * PiperLang - Is a compact and efficient PHP library designed to
     * provide localization capabilities for your web application.
     *
     * @package  PiperLang\PiperLang
     *
     * @author   Jacob Jørgensen
     *
     * @license  MIT
     *
     * @version  2.0.0
     */
    class Modifier extends PiperLang {
        /**
         * Modify constructor.
         *
         * Calls the parent PiperLang class constructor
         * first and then execute the custom code.
         *
         * @see PiperLang::__construct() - For the parent constructor.
         */
        public function __construct() {
            parent::__construct();
            // Add your custom code here.
        }
    }
