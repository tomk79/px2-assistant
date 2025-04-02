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
	 * CCEを登録する
	 * @param object $options 設定オプション
	 */
	public static function register ($options = null) {
		return '\tomk79\pickles2\assistant\cce\main('.json_encode($options).')';
	}

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
		array_push($rtn['css'], 'assistantCceFront--auto.css');
		array_push($rtn['css'], 'assistantCceFront--light.css');
		array_push($rtn['css'], 'assistantCceFront--dark.css');
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
		$assistant = new \tomk79\pickles2\assistant\main($this->px, $this->options);

		switch($request->command){
			case 'bootup-information':
				return array(
					"result" => true,
					"message" => "OK.",
					"options" => $this->options,
				);

			case 'chat-init':
				$chatlog = $assistant->chat()->init($request->chat_id);
				if($chatlog->chat_id != $request->chat_id){
					return array(
						"result" => false,
						"message" => $request->error,
						"chatLog" => null,
					);
				}

				return array(
					"result" => true,
					"message" => "OK.",
					"chatLog" => $chatlog,
				);

			case 'chat-comment':
				$answer = $assistant->chat()->generate_answer($request->message, $request->model);

				return array(
					"result" => true,
					"message" => "OK.",
					"answer" => $answer,
				);

			case 'get-chatlog-list':
				$chatLogList = $assistant->chat()->get_chatlog_list();

				return array(
					"result" => true,
					"message" => "OK.",
					"chatLogList" => $chatLogList,
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