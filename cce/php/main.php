<?php
/**
 * px2-assistant
 */
namespace tomk79\pickles2\assistant\cce;

/**
 * main.php
 */
class main {

	/** $px */
	private $px;

	/** $options */
	private $options;

	/** $cceAgent */
	private $cceAgent;

	/**
	 * コンストラクタ
	 * @param object $px Pickles 2 オブジェクト
	 * @param object $options 設定オプション
	 * @param object $cceAgent 管理画面拡張エージェントオブジェクト
	 */
	public function __construct($px, $options, $cceAgent){
		$this->px = $px;
		$this->options = $options;
		$this->cceAgent = $cceAgent;
	}

	/**
	 * 管理機能名を取得する
	 */
	public function get_label(){
		return 'Assistant';
	}

	/**
	 * フロントエンド資材の格納ディレクトリを取得する
	 */
	public function get_client_resource_base_dir(){
		return __DIR__.'/../front/';
	}

	/**
	 * 管理画面にロードするフロント資材のファイル名を取得する
	 */
	public function get_client_resource_list(){
		$rtn = array();
		$rtn['css'] = array();
		array_push($rtn['css'], 'assistantCceFront.css');
		$rtn['js'] = array();
		array_push($rtn['js'], 'assistantCceFront.js');
		return $rtn;
	}

	/**
	 * 管理画面を初期化するためのJavaScript関数名を取得する
	 */
	public function get_client_initialize_function(){
		return 'window.assistantCceFront';
	}

	/**
	 * General Purpose Interface (汎用API)
	 */
	public function gpi($request){
		$assistant = new \tomk79\pickles2\assistant\main($this->px);

		switch($request->command){
			case 'chat-comment':
				return array(
					"result" => true,
					"message" => "OK.",
					"answer" => array(
						"type" => "answer",
						"text" => $request->message->text."; dummy answer!",
					),
				);

			case 'ping':
				return array(
					"result" => true,
					"message" => "OK.",
				);

		}
		return false;
	}
}