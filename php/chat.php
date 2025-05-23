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

	/** モデル管理オブジェクト */
	private $chatModel;

	/** データディレクトリ */
	private $realpath_data_dir;

	/**
	 * constructor
	 * @param object $main メインオブジェクト
	 */
	public function __construct( $main ){
		$this->main = $main;
		$this->px = $this->main->px();
		$this->chatModel = new models\chatModel($main);

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
			"temporary_system_prompts" => array(),
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
	 * @param string $model モデル名
	 * @return object 返答メッセージ
	 */
	public function generate_answer($message, $model) {

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
			"temporary_system_prompts" => array(),
		);
		if( !is_dir(dirname($realpath_chatlog_json)) ){
			$this->px->fs()->mkdir_r(dirname($realpath_chatlog_json));
		}
		if( is_file($realpath_chatlog_json) ){
			$chatlog = json_decode( $this->px->fs()->read_file($realpath_chatlog_json) );
		}

		$messageContents = $message->content ?? array();
		if( is_string($messageContents) ){
			$messageContents = array(
				(object) array(
					"type" => "text",
					"text" => $messageContents,
				),
			);
		}

		if($message->type == "function_call"){
			// --------------------
			// Function Calling の結果を受け取った場合
			array_push(
				$chatlog->temporary_system_prompts,
				(object) array(
					'role' => 'tool',
					'tool_call_id' => $message->call_id,
					'name' => $message->function ?? null,
					'content' => $messageContents,
					'datetime' => gmdate('Y-m-d\TH:i:s\Z'),
				)
			);
		}else{
			// --------------------
			// ユーザーの問い合わせメッセージを受け取った場合
			array_push(
				$chatlog->messages,
				(object) array(
					'role' => 'user',
					'content' => $messageContents,
					'datetime' => gmdate('Y-m-d\TH:i:s\Z'),
				)
			);

			// Function Calling Prompt を作成する
			array_push(
				$chatlog->temporary_system_prompts,
				(object) array(
					'role' => 'system',
					'content' => $this->mk_systemprompt_for_function_calling($messageContents),
					'datetime' => gmdate('Y-m-d\TH:i:s\Z'),
				)
			);
		}

		$functionCallingPromptMessages = array();
		foreach(array_merge( $chatlog->messages, $chatlog->temporary_system_prompts ) as $message){
			$message_row = (object) array(
				'role' => $message->role,
				'content' => $message->content ?? '',
			);
			if( isset($message->name) ){
				$message_row->name = $message->name;
			}
			if( isset($message->tool_call_id) ){
				$message_row->tool_call_id = $message->tool_call_id;
			}
			if( isset($message->tool_calls) ){
				$message_row->tool_calls = $message->tool_calls;
			}
			array_push($functionCallingPromptMessages, $message_row);
		}

		try {
			// リクエストを実行
			$result = $this->chatModel->send_message(
				$model,
				$functionCallingPromptMessages
			);

			// レスポンスを解析
			if (isset($result->error)) {
				$chatlog->temporary_system_prompts = array();
				$this->px->fs()->save_file($realpath_chatlog_json, json_encode($chatlog, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
				return (object) [
					"type" => "error",
					"content" => $result->error->message,
				];
			}
			if (isset($result->choices[0]->message->content)) {

				$answerMessage = $result->choices[0]->message;
				$answerMessage->model = $model;
				$answerMessage->datetime = gmdate('Y-m-d\TH:i:s\Z');

				$parsed_answer = $this->parse_systemanswer($answerMessage->content);
				if( $parsed_answer->type == 'function_call' ){
					$function_call_id = 'call_'.bin2hex(random_bytes(10));
					foreach($parsed_answer->functions as $parsed_answer_function){
						array_push(
							$chatlog->temporary_system_prompts,
							(object) array(
								"role" => "assistant",
								"content" => $answerMessage->content,
								"tool_calls" => array(
									(object) array(
										"id" => $function_call_id,
										"type" => "function",
										"function" => (object) array(
											"name" => $parsed_answer_function->function,
											"arguments" => json_encode($parsed_answer_function->args),
										),
									),
								),
							),
						);
					}
					$this->px->fs()->save_file($realpath_chatlog_json, json_encode($chatlog, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

					return (object) [
						"type" => "function_call",
						"role" => "assistant",
						"functions" => $parsed_answer->functions,
						"call_id" => $function_call_id,
					];
				}

				$answerMessage->content = $parsed_answer->content ?? $answerMessage->content;

				array_push(
					$chatlog->messages,
					$answerMessage
				);
				$chatlog->temporary_system_prompts = array();
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

	private function get_function_list(){
		$fncJsonBaseDir = __DIR__.'/../data/functions/';
		$fncJsons = $this->px->fs()->ls($fncJsonBaseDir);
		$function_list = (object) array();
		foreach($fncJsons as $fncJsonName){
			$fncJson = json_decode($this->px->fs()->read_file($fncJsonBaseDir.$fncJsonName));
			if( !isset($fncJson->name) || !isset($fncJson->description) ){
				continue;
			}
			$function_list->{$fncJson->name} = $fncJson;
		}
		return $function_list;
	}

	private function mk_systemprompt_for_function_calling($messageContents){
		ob_start(); ?>
[System message]
You are a helpful assistant.

You have access to the following tools:

<?php
	$function_list = $this->get_function_list();
	$function_name_list = array();
	foreach($function_list as $function){
		echo '- '.$function->name.': '.$function->description.', args: '.json_encode($function->parameters->properties)."\n";
		array_push($function_name_list, $function->name);
	}
?>

If you need to use any tool, use the following format:

```
<FunctionCalling>
  <Thought>I need to solve this problem step-by-step.</Thought>
  <Function>the function to take, should be one of [<?= implode(', ', $function_name_list) ?>]</Function>
  <Args>the input to the function as a JSON object.</Args>
</FunctionCalling>
```

Then the tool provides the output in the next message.

Else if, you can answer the question directly, use the following format:

```
<FinalAnswer>the answer to the user's question</FinalAnswer>
```

You can also ask the user for more information if needed.

Begin!

[User message]
<?php
	if( is_string($messageContents) ){
		echo ($messageContents ?? '')."\n";
	}elseif( is_array($messageContents)){
		foreach($messageContents as $messageContent){
			echo ($messageContent->text ?? '')."\n";
		}
	}
?>
<?php
		$systemMessage = ob_get_clean();
		return $systemMessage;
	}

	private function parse_systemanswer($answer){
		$functions = array();
		if(preg_match_all('/<FunctionCalling>(.*?)<\/FunctionCalling>/si', $answer, $matchedAll)){
			foreach($matchedAll[1] as $matchedTagStr){
				preg_match('/<Thought>(.*?)<\/Thought>/si', $matchedTagStr, $matched);
				$thought = '';
				if( isset($matched[1]) && strlen($matched[1]) ){
					$thought = trim($matched[1]);
				}
				preg_match('/<Function>(.*?)<\/Function>/si', $matchedTagStr, $matched);
				$function = '';
				if( isset($matched[1]) && strlen($matched[1]) ){
					$function = trim($matched[1]);
				}
				preg_match('/<Args>(.*?)(?:<\/Args>|\`\`\`*\s*$|$)/si', $matchedTagStr, $matched);
				$args = (object) array();
				if( isset($matched[1]) && strlen($matched[1]) ){
					$args = json_decode($matched[1]);
					if( !is_object($args) ){
						$args = (object) array();
					}
				}
				array_push($functions, (object) array(
					'thought' => $thought,
					'function' => $function,
					'args' => $args,
				));
			}
		}

		preg_match('/<FinalAnswer>(.*?)(?:<\/FinalAnswer>|\`\`\`*\s*$|$)/si', $answer, $matched);
		$final_answer = '';
		if( isset($matched[1]) && strlen($matched[1]) ){
			$final_answer = trim($matched[1]);
		}

		if( count($functions) ){
			return (object) array(
				'type' => 'function_call',
				'functions' => $functions,
			);
		}
		if( $final_answer ){
			return (object) array(
				'type' => 'answer',
				'content' => $final_answer,
			);
		}

		return (object) array(
			'type' => 'answer',
			'content' => $answer,
		);
	}

	/**
	 * チャットリストを取得する
	 * @return object $chatlog_list
	 */
	public function get_chatlog_list() {
		$chatFileList = $this->px->fs()->ls($this->realpath_data_dir.'chatlog/');
		$chatlog_list = array();
		foreach($chatFileList as $chatFile){
			$chatId = preg_replace('/^(.+)\.json$/si', '$1', $chatFile);

			$chatContent = json_decode( file_get_contents($this->realpath_data_dir.'chatlog/'.$chatFile) );
			$title = '...';
			$updated_at = null;
			if(count($chatContent->messages)){
				if( is_string($chatContent->messages[0]->content ?? null) ){
					$title = mb_substr($chatContent->messages[0]->content, 0, 48);
					if (mb_strlen($chatContent->messages[0]->content) > 48) {
						$title .= '...';
					}
				}elseif( is_array($chatContent->messages[0]->content ?? null) ){
					$title = mb_substr($chatContent->messages[0]->content[0]->text, 0, 48);
					if (mb_strlen($chatContent->messages[0]->content[0]->text) > 48) {
						$title .= '...';
					}
				}
				$updated_at = $chatContent->messages[count($chatContent->messages)-1]->datetime;
			}

			array_push($chatlog_list, (object) array(
				'chat_id' => $chatId,
				'title' => $title,
				'updated_at' => $updated_at,
			));
		}

		// Sort chatlog list by updated_at in descending order (newest first)
		usort($chatlog_list, function($a, $b) {
			if (!isset($a->updated_at) && !isset($b->updated_at)) {
				return 0;
			}
			if (!isset($a->updated_at)) {
				return 1;
			}
			if (!isset($b->updated_at)) {
				return -1;
			}
			return strcmp($b->updated_at, $a->updated_at);
		});

		return $chatlog_list;
	}
}
