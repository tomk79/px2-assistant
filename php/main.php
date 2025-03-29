<?php
/**
 * px2-assistant
 */
namespace tomk79\pickles2\assistant;

/**
 * main.php
 */
class main {

	/** Picklesオブジェクト */
	private $px;

	/** データディレクトリ */
	private $realpath_data_dir;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;

		$this->realpath_data_dir = $this->px->get_realpath_homedir() . '_sys/ram/data/px2-assistant/';
		if(!is_dir($this->realpath_data_dir)){
			$this->px->fs()->mkdir_r($this->realpath_data_dir);
		}
	}

	/**
	 * $px を取得する
	 */
	public function px(){
		return $this->px;
	}

	/**
	 * データディレクトリのパスを取得する
	 * @return string データディレクトリのパス
	 */
	public function get_realpath_data_dir(){
		return $this->realpath_data_dir;
	}

	/**
	 * チャット機能を取得する
	 * @return object $chat
	 */
	public function chat() {
		$chat = new chat($this);
		return $chat;
	}
}
