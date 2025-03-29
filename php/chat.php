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

		if( !$this->is_valid_chat_id($message->chat_id) ){
			return (object) array(
				"type" => "error",
				"text" => "Invalid chat ID.",
			);
		}

		$realpath_chatlog_json = $this->realpath_data_dir.'chatlog/'.urlencode($message->chat_id).'.json';
		$chatlog = (object) array(
			"chat_id" => $message->chat_id,
			"messages" => array(),
		);
		if( !is_dir(dirname($realpath_chatlog_json)) ){
			$this->px->fs()->mkdir_r(dirname($realpath_chatlog_json));
		}
		if( is_file($realpath_chatlog_json) ){
			$chatlog = json_decode( $this->px->fs()->read_file($realpath_chatlog_json) );
		}

		array_push(
			$chatlog->messages,
			array(
				'role' => 'user',
				'content' => $message->text,
				'datetime' => gmdate('Y-m-d\TH:i:s\Z'),
			)
		);

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
							"messages" => $chatlog->messages,
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

				$answerMessage = $result->choices[0]->message;
				$answerMessage->datetime = gmdate('Y-m-d\TH:i:s\Z');
				array_push(
					$chatlog->messages,
					$answerMessage
				);
				$this->px->fs()->save_file($realpath_chatlog_json, json_encode($chatlog, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

				return (object) [
					"type" => "answer",
					"text" => $answerMessage->content,
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

	/**
	 * チャットIDが有効かどうかを確認する
	 * @param string $chat_id チャットID
	 * @return bool 有効な場合はtrue、無効な場合はfalse
	 */
	private function is_valid_chat_id($chat_id) {
		if(!is_string($chat_id)){
			return false;
		}
		if(!strlen($chat_id)){
			return false;
		}
		if(!preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})\-([a-zA-Z0-9]{8})$/si', $chat_id, $matched)){
			return false;
		}
		$parsed = array(
			'year' => intval($matched[1]),
			'month' => intval($matched[2]),
			'day' => intval($matched[3]),
			'random' => $matched[4],
		);
		
		// Check if the date is valid (e.g., not 2025-13-40)
		if(!checkdate($parsed['month'], $parsed['day'], $parsed['year'])){
			return false;
		}
		
		return true;
	}
}
