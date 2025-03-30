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
	 * チャットを初期化する
	 * @param object $chat_id チャットID
	 * @return object 返答メッセージ
	 */
	public function init($chat_id) {
		if( !$this->is_valid_chat_id($chat_id) ){
			return (object) array(
				"error" => "Invalid chat ID.",
				"chat_id" => null,
				"messages" => array(),
			);
		}

		$realpath_chatlog_json = $this->realpath_data_dir.'chatlog/'.urlencode($chat_id).'.json';
		$chatlog = (object) array(
			"chat_id" => $chat_id,
			"messages" => array(),
		);
		if( !is_dir(dirname($realpath_chatlog_json)) ){
			$this->px->fs()->mkdir_r(dirname($realpath_chatlog_json));
		}
		if( is_file($realpath_chatlog_json) ){
			$chatlog = json_decode( $this->px->fs()->read_file($realpath_chatlog_json) );
		}

		return $chatlog;
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
				"content" => "Invalid chat ID.",
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
			(object) array(
				'role' => 'user',
				'content' => $message->content ?? '',
				'datetime' => gmdate('Y-m-d\TH:i:s\Z'),
			)
		);

		ob_start(); ?>
[System message]
You are a helpful assistant.

You have access to the following tools:

calculator: A calculator for performing arithmetic operations, args: {"expression":{"type":"string","description":"The mathematical expression to evaluate."}}
weather: Get the current weather in a given location, args: {"location":{"type":"string","description":"The city and state, e.g. San Francisco, CA"}}

Use the following format:

Thought: I need to solve this problem step-by-step.
Action: the action to take, should be one of [calculator, weather]
Action Input: the input to the action
Observation: the result of the action
... (this Thought/Action/Action Input/Observation can repeat N times)
Thought: I know the final answer
Final Answer: the final answer to the original input question

Begin!

[User message]
<?= $message->content ?>
<?php
		$systemMessage = ob_get_clean();

		$promptMessages = array();
		foreach($chatlog->messages as $message){
			array_push($promptMessages, (object) array(
				'role' => $message->role,
				'content' => $message->content,
				'datetime' => $message->datetime,
			));
		}
		// array_push($promptMessages, (object) array(
		// 	'role' => 'user',
		// 	'content' => $systemMessage,
		// ));

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
							"model" => "gpt-4o-mini",
							// "model" => "gpt-3.5-turbo",
							"messages" => $promptMessages,
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
					"content" => $result->error->message,
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
					"role" => "assistant",
					"content" => $answerMessage->content,
				];
			}
			
		} catch (\Exception $e) {
			return (object) [
				"type" => "error",
				"content" => "Error calling OpenAI API: " . $e->getMessage(),
			];
		}

		return (object) array(
			"type" => "error",
			"content" => "Error!",
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
