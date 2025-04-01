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
	 * @param array $functionCallingPromptMessages プロンプトメッセージ
	 * @param array $options オプション
	 * @return object 返答メッセージ
	 */
	public function send_chat_message($modelName, $functionCallingPromptMessages, $options = array()) {
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

		$headers = array();
		array_push($headers, 'Content-Type: application/json');
		if( preg_match('/^https\:\/\/api\.openai\.com/', $selectedModel->url) ){
			if( strlen($_ENV['OPEN_AI_SECRET'] ?? '') ){
				array_push($headers, 'Authorization: Bearer '.($_ENV['OPEN_AI_SECRET']));
			}
			if( strlen($_ENV['OPEN_AI_ORG_ID'] ?? '') ){
				array_push($headers, 'OpenAI-Organization: '.($_ENV['OPEN_AI_ORG_ID'] ?? null));
			}
		}

		set_time_limit(60 * 60);

		// リクエストを実行
		$response = file_get_contents(
			$selectedModel->url,
			false,
			stream_context_create(array(
				'http' => array(
					'method' => 'POST',
					'header' => implode("\r\n", $headers),
					'timeout' => 60 * 3,
					'content' => json_encode(array(
						"model" => $selectedModel->model,
						"messages" => $functionCallingPromptMessages,
						"temperature" => 0.7,
						"max_tokens" => 1000,
					)),
					'ignore_errors' => true,
				)
			))
		);
		$result = json_decode($response);

		set_time_limit(30);

		return $result;
	}
}
