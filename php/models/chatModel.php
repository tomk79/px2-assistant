<?php
/**
 * chatModel
 */
namespace tomk79\pickles2\assistant\models;

/**
 * chatModel.php
 */
class chatModel {

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
	 * チャットメッセージを送信する
	 *
	 * @param string $modelName モデル名
	 * @param array $promptOrigMessages プロンプトメッセージ
	 * @param array $options オプション
	 * @return object 返答メッセージ
	 */
	public function send_message($modelName, $promptOrigMessages, $options = array()) {
		$options = $options ?? array();
		$options['temperature'] = $options['temperature'] ?? 0;
		$options['max_tokens'] = $options['max_tokens'] ?? 2000;

		$promptMessages = json_decode(json_encode($promptOrigMessages));

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
			// Anthropic API
			$api_type = 'anthropic';
			$api_key = $_ENV[$selectedModel->api_key ?? 'ANTHROPIC_API_KEY'] ?? $selectedModel->api_key ?? null;
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
				if($api_type == 'anthropic'){
					if( $promptMessage->tool_calls ?? null ){
						unset($promptMessage->tool_calls);
							// NOTE: Anthropic API では、`tool_calls` を与えるとエラーになる。
					}
					if( $promptMessage->tool_call_id ?? null ){
						unset($promptMessage->tool_call_id);
							// NOTE: Anthropic API では、`tool_call_id` を与えるとエラーになる。
					}
					if( is_array($promptMessage->content) ){
						foreach($promptMessage->content as $promptMessageContent){
							if( $promptMessageContent->type == 'image_url' ){
								// NOTE: Anthropic API では、`image_url` は受け取れない。
								$promptMessageContent->type = 'image';
								preg_match('/^data\:([a-zA-Z0-9\-\_]+\/[a-zA-Z0-9\-\_]+)\;([a-zA-Z0-9\-\_]+)\,(.*)$/', $promptMessageContent->image_url->url ?? '', $tmpMatched);
								$promptMessageContent->source = (object) array(
									"type" => $tmpMatched[2],
									"media_type" => $tmpMatched[1],
									"data" => $tmpMatched[3],
								);
								unset($promptMessageContent->image_url);
								unset($tmpMatched);
							}
						}
					}
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
		if($api_type == 'anthropic'){
			$result->choices = array();
			if($result->type == "error"){
				array_push($result->choices, (object) array(
					"message" => (object) array(
						"role" => $result->role ?? 'assistant',
						"content" => '[ERROR] '.$result->error->message,
						"refusal" => null,
						"annotations" => []
					),
				));
			}else{
				array_push($result->choices, (object) array(
					"message" => (object) array(
						"role" => $result->role ?? 'assistant',
						"content" => $result->content[0]->text ?? '',
						"refusal" => null,
						"annotations" => []
					),
				));
			}
			unset($result->role, $result->content);
		}

		return $result;
	}
}
