<?php
/**
 * px2-assistant
 */
namespace tomk79\pickles2\assistant;

/**
 * chat.php
 */
class chat {

	/** Picklesオブジェクト */
	private $px;

	/** メインオブジェクト */
	private $main;

	/** データディレクトリ */
	private $realpath_data_dir;

	/**
	 * constructor
	 * @param object $main メインオブジェクト
	 */
	public function __construct( $main ){
		$this->main = $main;
		$this->px = $this->main->px();

		$this->realpath_data_dir = $this->main->get_realpath_data_dir();
	}

	/**
	 * チャットの返答を生成する
	 * @param object $message チャットメッセージ
	 * @return object 返答メッセージ
	 */
	public function generate_answer($message) {

		try {
			// リクエストを実行
			$response = file_get_contents(
				'https://api.openai.com/v1/chat/completions',
				false,
				stream_context_create(array(
					'http' => array(
						'method' => 'POST',
						'header' => implode("\r\n", array(
							'Content-Type: application/json',
							'Authorization: Bearer '.($_ENV['OPEN_AI_SECRET'] ?? null),
							'OpenAI-Organization: '.($_ENV['OPEN_AI_ORG_ID'] ?? null),
						)),
						'timeout' => 60 * 3,
						'content' => json_encode(array(
							"model" => "gpt-3.5-turbo",
							"messages" => [
								['role' => 'user', 'content' => $message->text]
							],
							"temperature" => 0.7,
							"max_tokens" => 1000,
						)),
						'ignore_errors' => true,
					)
				))
			);
			
			// レスポンスを解析
			$result = json_decode($response);
			if (isset($result->error)) {
				return (object) [
					"type" => "error",
					"text" => $result->error->message,
				];
			}
			if (isset($result->choices[0]->message->content)) {
				return (object) [
					"type" => "answer",
					"text" => $result->choices[0]->message->content,
				];
			}
			
		} catch (\Exception $e) {
			return (object) [
				"type" => "error",
				"text" => "Error calling OpenAI API: " . $e->getMessage(),
			];
		}

		return (object) array(
			"type" => "error",
			"text" => "Error!",
		);
	}
}
