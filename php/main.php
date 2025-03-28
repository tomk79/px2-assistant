<?php
/**
 * px2-assistant
 */
namespace tomk79\pickles2\assistant;

/**
 * main.php
 */
class main {

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
	}

	public function px(){
		return $this->px;
	}
}
