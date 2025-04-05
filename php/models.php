<?php
/**
 * px2-assistant
 */
namespace tomk79\pickles2\assistant;

/**
 * models.php
 */
class models {

	/** Picklesオブジェクト */
	private $px;

	/** メインオブジェクト */
	private $main;

	/** モデル設定オブジェクト */
	private $models;

	/**
	 * constructor
	 * @param object $main メインオブジェクト
	 */
	public function __construct( $main ){
		$this->main = $main;
		$this->px = $this->main->px();

		$this->models = $this->main->options()->models ?? null;
	}

	/**
	 * モデルを実行する
	 * @param string $modelName モデル名
	 * @param array $promptMessages プロンプトメッセージ
	 * @param array $options オプション
	 * @return object 返答メッセージ
	 */
	public function send_chat_message($modelName, $promptMessages, $options = array()) {
		$options = $options ?? array();
		$options['temperature'] = $options['temperature'] ?? 0;
		$options['max_tokens'] = $options['max_tokens'] ?? 2000;

		$selectedModel = $this->models->chat->{$modelName} ?? null;
		if(!$selectedModel){
			return (object) array(
				"error" => "Invalid model name.",
				"model_name" => $modelName,
				"messages" => array(),
			);
		}

		$api_type = 'unknown';

		$headers = array();
		array_push($headers, 'Content-Type: application/json');

		$url_endpoint = $selectedModel->url;

		// --------------------------------------
		// サービス別の認証情報を設定
		if( preg_match('/^https\:\/\/api\.openai\.com/', $selectedModel->url) ){
			// OpenAI API
			$api_type = 'openai';
			$api_key = $_ENV[$selectedModel->api_key ?? 'OPENAI_API_KEY'] ?? $selectedModel->api_key ?? null;
			$openai_org_id = $_ENV[$selectedModel->org_id ?? 'OPENAI_ORG_ID'] ?? $selectedModel->org_id ?? null;
			if( strlen($api_key ?? '') ){
				array_push($headers, 'Authorization: Bearer '.($api_key));
			}
			if( strlen($openai_org_id ?? '') ){
				array_push($headers, 'OpenAI-Organization: '.($openai_org_id ?? null));
			}
		}elseif( preg_match('/^https\:\/\/generativelanguage\.googleapis\.com\/[a-zA-Z0-9]+\/openai\//', $selectedModel->url) ){
			// Google Gemini API (OpenAI compatible)
			$api_type = 'openai';
			$api_key = $_ENV[$selectedModel->api_key ?? 'GEMINI_API_KEY'] ?? $selectedModel->api_key ?? null;
			if( strlen($api_key ?? '') ){
				array_push($headers, 'Authorization: Bearer '.($api_key));
			}
		}elseif( preg_match('/^https\:\/\/api\.anthropic\.com/', $selectedModel->url) ){
			// Anthoropic API
			$api_type = 'anthoropic';
			$api_key = $_ENV[$selectedModel->api_key ?? 'ANTHOROPIC_API_KEY'] ?? $selectedModel->api_key ?? null;
			if( strlen($api_key ?? '') ){
				array_push($headers, 'x-api-key: '.($api_key));
			}
			if( strlen($selectedModel->anthropic_version ?? '') ){
				array_push($headers, 'anthropic-version: '.($selectedModel->anthropic_version));
			}
		}

		// --------------------------------------
		// モデルの違いによる互換性の問題を吸収する
		if($api_type != 'openai'){
			foreach($promptMessages as $promptMessage){
				if( $promptMessage->role != 'assistant' ){
					$promptMessage->role = 'user';
						// NOTE: `system` や `tool` などの値は、Gemma3:4B, Mistral:7B など、一部のモデル(Ollama？)で扱えない場合があるので、 `user` に置換する。
						// NOTE: 逆に、OpenAI の API では、`system` や `tool` を正しく与えないとエラーを返してくる。
				}
			}
		}

		set_time_limit(60 * 60);

		// --------------------------------------
		// リクエストを実行
		$response = file_get_contents(
			$url_endpoint,
			false,
			stream_context_create(array(
				'http' => array(
					'method' => 'POST',
					'header' => implode("\r\n", $headers),
					'timeout' => 60 * 3,
					'content' => json_encode(array(
						"model" => $selectedModel->model,
						"messages" => $promptMessages,
						"temperature" => 0.7,
						"max_tokens" => 1000,
					)),
					'ignore_errors' => true,
				),
			))
		);
		$result = json_decode($response);
		set_time_limit(30);

		// --------------------------------------
		// モデルの違いによる互換性の問題を吸収する
		if($api_type == 'anthoropic'){
			$result->choices = array();
			array_push($result->choices, (object) array(
				"message" => (object) array(
					"role" => $result->role,
					"content" => $result->content[0]->text ?? '',
					"refusal" => null,
					"annotations" => []
				),
			));
			unset($result->role, $result->content);
		}

		return $result;
	}
}
