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

		$this->models = $this->main->options()->models;
	}

	/**
	 * モデルを実行する
	 */
	public function send_chat_message($modelName, $functionCallingPromptMessages){
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
			array_push($headers, 'Authorization: Bearer '.($_ENV['OPEN_AI_SECRET'] ?? null));
			array_push($headers, 'OpenAI-Organization: '.($_ENV['OPEN_AI_ORG_ID'] ?? null));
		}

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
		return $result;
	}
}
