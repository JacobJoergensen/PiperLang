<?php
	namespace PiperLang;

	/**
	 * PiperLang - IS A COMPACT AND EFFICIENT PHP FRAMEWORK DESIGNED TO
	 * PROVIDE LOCALIZATION CAPABILITIES FOR YOUR WEB APPLICATION.
	 *
	 * @package    PiperLang\PiperLang
	 * @author     Jacob Jørgensen
	 * @license    MIT
	 * @version    1.3.0
	 */
	class Modifier extends PiperLang {
		/**
		 * MODIFY CONSTRUCTOR.
		 *
		 * CALLS THE PARENT PiperLang CLASS CONSTRUCTOR
		 * FIRST, AND THEN EXECUTE THE CUSTOM CODE.
		 *
		 * @see PiperLang::__construct() - FOR THE PARENT CONSTRUCTOR.
		 */
		public function __construct() {
			parent::__construct();
			// ADD YOUR CUSTOM CODE HERE.
		}
	}
