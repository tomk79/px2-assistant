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

	/** 設定オプション */
	private $options;

	/** データディレクトリ */
	private $realpath_data_dir;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $options 設定オプション
	 */
	public function __construct( $px, $options ){
		$this->px = $px;
		$this->options = $options;

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
	 * $options を取得する
	 */
	public function options(){
		return $this->options;
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
