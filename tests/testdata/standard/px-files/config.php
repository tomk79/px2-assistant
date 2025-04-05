<?php
/**
 * config.php template
 */
return call_user_func( function(){

	if( is_file(__DIR__.'/../../../../.env') ){
		$dotenv = \Dotenv\Dotenv::createImmutable( __DIR__.'/../../../../' );
		$dotenv->load();
	}

	// initialize

	/** コンフィグオブジェクト */
	$conf = new stdClass;


	// project

	/** サイト名 */
	$conf->name = 'px2-assistant';
	/** コピーライト表記 */
	$conf->copyright = 'Tomoya Koyanagi';
	/** ドメイン(本番環境のドメイン) */
	$conf->domain = null;
	/** コンテンツルートディレクトリ */
	$conf->path_controot = '/';

	/** デフォルトの言語 */
	$conf->default_lang = 'ja';

	/** 対応する言語 */
	$conf->accept_langs = array('ja', 'en');


	// paths

	/** トップページのパス(デフォルト "/") */
	$conf->path_top = '/';
	/** パブリッシュ先ディレクトリパス */
	$conf->path_publish_dir = './px-files/dist/';
	/** 公開キャッシュディレクトリ */
	$conf->public_cache_dir = '/caches/';
	/** リソースディレクトリ(各コンテンツに対して1:1で関連付けられる)のパス */
	$conf->path_files = '{$dirname}/{$filename}_files/';
	/** Contents Manifesto のパス */
	$conf->contents_manifesto = '/common/contents_manifesto.ignore.php';


	/**
	 * commands
	 *
	 * Pickles2 が認識するコマンドのパスを設定します。
	 * コマンドのパスが通っていない場合は、絶対パスで設定してください。
	 */
	$conf->commands = new stdClass;
	$conf->commands->php = 'php';

	/** php.ini のパス。主にパブリッシュ時のサブクエリで使用する。 */
	$conf->path_phpini = null;


	/**
	 * directory index
	 *
	 * `directory_index` は、省略できるファイル名のリストを設定します。
	 * 複数指定可能です。
	 *
	 * この一覧にリストされたファイル名に対するリンクは、ファイル名なしのURLと同一視されます。
	 * ファイル名が省略されたアクセス(末尾が `/` の場合)に対しては、
	 * 最初のファイル名と同じものとして処理します。
	 */
	$conf->directory_index = array(
		'index.html'
	);


	/**
	 * paths_proc_type
	 *
	 * パスのパターン別に処理方法を設定します。
	 *
	 * - ignore = 対象外パス。Pickles 2 のアクセス可能範囲から除外します。このパスにへのアクセスは拒絶され、パブリッシュの対象からも外されます。
	 * - direct = 物理ファイルを、ファイルとして読み込んでから加工処理を通します。 (direct以外の通常の処理は、PHPファイルとして `include()` されます)
	 * - pass = 物理ファイルを、そのまま無加工で出力します。 (デフォルト)
	 * - その他 = extension名
	 *
	 * パターンは先頭から検索され、はじめにマッチした設定を採用します。
	 * ワイルドカードとして "*"(アスタリスク) が使用可能です。
	 *
	 * extensionは、 `$conf->funcs->processor` に設定し、設定した順に実行されます。
	 * 例えば、 '*.html' => 'html' にマッチしたリクエストは、
	 * $conf->funcs->processor->html に設定したプロセッサのリストに沿って、上から順に処理されます。
	 */
	$conf->paths_proc_type = array(
		'/.htaccess' => 'ignore' ,
		'/.px_execute.php' => 'ignore' ,
		'/px-files/*' => 'ignore' ,
		'*.ignore/*' => 'ignore' ,
		'*.ignore.*' => 'ignore' ,
		'/composer.json' => 'ignore' ,
		'/composer.lock' => 'ignore' ,
		'/README.md' => 'ignore' ,
		'/vendor/*' => 'ignore' ,
		'*/.DS_Store' => 'ignore' ,
		'*/Thumbs.db' => 'ignore' ,
		'*/.svn/*' => 'ignore' ,
		'*/.git/*' => 'ignore' ,
		'*/.gitignore' => 'ignore' ,

		'/pass/*' => 'pass' ,

		'*.html' => 'html' ,
		'*.htm' => 'html' ,
		'*.css' => 'css' ,
		'*.js' => 'js' ,
		'*.png' => 'pass' ,
		'*.jpg' => 'pass' ,
		'*.gif' => 'pass' ,
		'*.svg' => 'pass' ,
	);


	/**
	 * paths_enable_sitemap
	 *
	 * サイトマップのロードを有効にするパスのパターンを設定します。
	 * ワイルドカードとして "*"(アスタリスク) が使用可能です。
	 *
	 * サイトマップ中のページ数が増えると、サイトマップのロード自体に時間を要する場合があります。
	 * サイトマップへのアクセスが必要ないファイルでは、この処理はスキップするほうがよいでしょう。
	 *
	 * 多くの場合では、 *.html と *.htm 以外ではロードする必要はありません。
	 */
	$conf->paths_enable_sitemap = array(
		'*.html',
		'*.htm',
	);


	// system

	/** ファイルに適用されるデフォルトのパーミッション */
	$conf->file_default_permission = '775';
	/** ディレクトリに適用されるデフォルトのパーミッション */
	$conf->dir_default_permission = '775';
	/** ファイルシステムの文字セット。ファイル名にマルチバイト文字を使う場合に参照されます。 */
	$conf->filesystem_encoding = 'UTF-8';
	/** 出力文字エンコーディング名 */
	$conf->output_encoding = 'UTF-8';
	/** 出力改行コード名 (cr|lf|crlf) */
	$conf->output_eol_coding = 'lf';
	/** セッション名 */
	$conf->session_name = 'PXSID';
	/** セッションの有効期間 */
	$conf->session_expire = 1800;
	/** PX Commands のウェブインターフェイスからの実行を許可 */
	$conf->allow_pxcommands = 1;
	/** タイムゾーン */
	$conf->default_timezone = 'Asia/Tokyo';



	// -------- functions --------

	$conf->funcs = new stdClass;

	/**
	 * funcs: Before sitemap
	 *
	 * サイトマップ読み込みの前に実行するプラグインを設定します。
	 */
	$conf->funcs->before_sitemap = array(
		// px2-clover
		\tomk79\pickles2\px2clover\register::clover(array(
			"protect_preview" => true, // プレビューに認証を要求するか？
		)),

		// PX=clearcache
		picklesFramework2\commands\clearcache::register() ,

		 // PX=config
		picklesFramework2\commands\config::register() ,

		 // PX=phpinfo
		picklesFramework2\commands\phpinfo::register() ,

		// sitemapExcel
		tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec(),
	);

	/**
	 * funcs: Before content
	 *
	 * サイトマップ読み込みの後、コンテンツ実行の前に実行するプラグインを設定します。
	 */
	$conf->funcs->before_content = array(

		// PX=api
		picklesFramework2\commands\api::register(),

		// PX=publish (px2-publish-ex)
		tomk79\pickles2\publishEx\publish::register(array(
			'publish_vendor_dir' => false,
		)),

		// PX=px2dthelper
		tomk79\pickles2\px2dthelper\main::register(),
	);


	/**
	 * processor
	 *
	 * コンテンツの種類に応じた加工処理の設定を行います。
	 * `$conf->funcs->processor->{$paths_proc_typeに設定した処理名}` のように設定します。
	 * それぞれの処理は配列で、複数登録することができます。処理は上から順に実行されます。
	 *
	 * Tips: テーマは、html に対するプロセッサの1つとして実装されています。
	 */
	$conf->funcs->processor = new stdClass;

	$conf->funcs->processor->html = array(

		// ページ内目次を自動生成する
		picklesFramework2\processors\autoindex\autoindex::exec(),

		// px2-path-resolver - 共通コンテンツのリンクやリソースのパスを解決する
		//   このAPIは、サイトマップCSV上で path と content が異なるパスを参照している場合に、
		//   相対的に記述されたリンクやリソースのパスがあわなくなる問題を解決します。
		'tomk79\pickles2\pathResolver\main::resolve_common_contents()',

		// テーマ
		'theme'=>'tomk79\pickles2\multitheme\theme::exec('.json_encode([
			'param_theme_switch'=>'THEME',
			'cookie_theme_switch'=>'THEME',
			'path_theme_collection'=>'./px-files/themes/',
			'attr_bowl_name_by'=>'data-contents-area',
			'default_theme_id' => '2024-03-09'
		]).')',

		// Apache互換のSSIの記述を解決する
		picklesFramework2\processors\ssi\ssi::exec(),

		// 属性 data-contents-area を削除する
		'tomk79\pickles2\remove_attr\main::exec('.json_encode(array(
			"attrs"=>array(
				'data-contents-area',
			) ,
		)).')',

		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		picklesFramework2\processors\encodingconverter\encodingconverter::exec(),
	);

	$conf->funcs->processor->css = array(
		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		picklesFramework2\processors\encodingconverter\encodingconverter::exec(),
	);

	$conf->funcs->processor->js = array(
		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		picklesFramework2\processors\encodingconverter\encodingconverter::exec(),
	);

	$conf->funcs->processor->md = array(
		// Markdown文法を処理する
		picklesFramework2\processors\md\ext::exec(),

		// html のデフォルトの処理を追加
		$conf->funcs->processor->html ,
	);

	$conf->funcs->processor->scss = array(
		// SCSS文法を処理する
		picklesFramework2\processors\scss\ext::exec(),

		// css のデフォルトの処理を追加
		$conf->funcs->processor->css ,
	);


	/**
	 * funcs: Before output
	 *
	 * 最終出力の直前で実行される処理を設定します。
	 * この処理は、拡張子によらずすべてのリクエストが対象です。
	 * (HTMLの場合は、テーマの処理の後のコードが対象になります)
	 */
	$conf->funcs->before_output = array(
		// px2-path-resolver - 相対パス・絶対パスを変換して出力する
		//   options
		//     string 'to':
		//       - relate: 相対パスへ変換
		//       - absolute: 絶対パスへ変換
		//       - pass: 変換を行わない(default)
		//     bool 'supply_index_filename':
		//       - true: 省略されたindexファイル名を補う
		//       - false: 省略できるindexファイル名を削除
		//       - null: そのまま (default)
		'tomk79\pickles2\pathResolver\main::exec('.json_encode(array(
			'to' => 'absolute' ,
			'supply_index_filename' => false
		)).')' ,

	);


	// -------- config for Plugins. --------
	// その他のプラグインに対する設定を行います。
	$conf->plugins = new stdClass;

	/** config for Pickles 2 Desktop Tool. */
	$conf->plugins->px2dt = new stdClass;

	/**
	 * GUIエディタのエンジンの種類
	 * - `legacy` = 旧GUI編集ツール。(廃止)
	 * - `broccoli-html-editor` = NodeJSで実装された broccoli-html-editor を使用。
	 * - `broccoli-html-editor-php` = PHPで実装された broccoli-html-editor を使用。
	 */
	$conf->plugins->px2dt->guiEngine = 'broccoli-html-editor-php';

	/** broccoliモジュールセットの登録 */
	$conf->plugins->px2dt->paths_module_template = [];
	$conf->plugins->px2dt->path_module_templates_dir = "./px-files/modules/";

	/** コンテンツエリアを識別するセレクタ(複数の要素がマッチしてもよい) */
	$conf->plugins->px2dt->contents_area_selector = '[data-contents-area]';

	/** コンテンツエリアのbowl名を指定する属性名 */
	$conf->plugins->px2dt->contents_bowl_name_by = 'data-contents-area';

	/** パブリッシュのパターンを登録 */
	$conf->plugins->px2dt->publish_patterns = array(
		array(
			'label'=>'すべて',
			'paths_region'=> array('/'),
			'paths_ignore'=> array(),
			'keep_cache'=>false
		),
		array(
			'label'=>'リソース類',
			'paths_region'=> array('/caches/','/common/'),
			'paths_ignore'=> array(),
			'keep_cache'=>true
		),
		array(
			'label'=>'すべて(commonを除く)',
			'paths_region'=> array('/'),
			'paths_ignore'=> array('/common/'),
			'keep_cache'=>false
		),
	);

	/** config for GUI Editor. */
	$conf->plugins->px2dt->guieditor = new stdClass;

	/** GUI編集データディレクトリ */
	// $conf->plugins->px2dt->guieditor->path_data_dir = '{$dirname}/{$filename}_files/guieditor.ignore/';

	/** GUI編集リソース出力先ディレクトリ */
	// $conf->plugins->px2dt->guieditor->path_resource_dir = '{$dirname}/{$filename}_files/resources/';


	$conf->plugins->px2dt->custom_console_extensions = array(
	    'px2-assistant' => array(
			'class_name' => \tomk79\pickles2\assistant\cce\main::register(array(
				"models" => array(
					"chat" => array(
						"openai-gpt-3.5-turbo" => array(
							"url" => "https://api.openai.com/v1/chat/completions",
							"model" => "gpt-3.5-turbo",
							"label" => "OpenAI gpt-3.5-turbo",
							"api_key" => "OPENAI_API_KEY",
							"org_id" => "OPENAI_ORG_ID",
						),
						"openai-gpt-4o-mini" => array(
							"url" => "https://api.openai.com/v1/chat/completions",
							"model" => "gpt-4o-mini",
							"label" => "OpenAI gpt-4o-mini",
							"api_key" => "OPENAI_API_KEY",
							"org_id" => "OPENAI_ORG_ID",
						),
						"google-gemini-2.0-flash" => array(
							"url" => "https://generativelanguage.googleapis.com/v1beta/openai/chat/completions",
							"model" => "gemini-2.0-flash",
							"label" => "Google gemini-2.0-flash",
							"api_key" => "GEMINI_API_KEY",
						),
						"anthoropic-claude-3-5-sonnet-20241022" => array(
							"url" => "https://api.anthropic.com/v1/messages",
							"model" => "claude-3-5-sonnet-20241022",
							"label" => "Anthoropic claude-3-5-sonnet-20241022",
							"api_key" => "ANTHOROPIC_API_KEY",
							"anthropic_version" => "2023-06-01",
						),
						"ollama-deepseek-r1:14b" => array(
							"url" => "http://localhost:11434/v1/chat/completions",
							"model" => "deepseek-r1:14b",
							"label" => "Ollama deepseek-r1:14b",
						),
						"ollama-deepseek-r1:7b" => array(
							"url" => "http://localhost:11434/v1/chat/completions",
							"model" => "deepseek-r1:7b",
							"label" => "Ollama deepseek-r1:7b",
						),
						"ollama-llama3.2:3B" => array(
							"url" => "http://localhost:11434/v1/chat/completions",
							"model" => "llama3.2:3B",
							"label" => "Ollama llama3.2:3B",
						),
						"ollama-gemma3:4B" => array(
							"url" => "http://localhost:11434/v1/chat/completions",
							"model" => "gemma3:4B",
							"label" => "Ollama gemma3:4B",
						),
						"ollama-mistral:7B" => array(
							"url" => "http://localhost:11434/v1/chat/completions",
							"model" => "mistral:7B",
							"label" => "Ollama mistral:7B",
						),
					),
				),
			)),
			'capability' => array('manage'),
		),
	);

	// -------- PHP Setting --------

	/**
	 * `memory_limit`
	 *
	 * PHPのメモリの使用量の上限を設定します。
	 * 正の整数値で上限値(byte)を与えます。
	 *
	 *     例: 1000000 (1,000,000 bytes)
	 *     例: "128K" (128 kilo bytes)
	 *     例: "128M" (128 mega bytes)
	 *
	 * -1 を与えた場合、無限(システムリソースの上限まで)に設定されます。
	 * サイトマップやコンテンツなどで、容量の大きなデータを扱う場合に調整してください。
	 */
	// @ini_set( 'memory_limit' , -1 );

	/**
	 * `display_errors`, `error_reporting`
	 *
	 * エラーを標準出力するための設定です。
	 *
	 * PHPの設定によっては、エラーが発生しても表示されない場合があります。
	 * もしも、「なんか挙動がおかしいな？」と感じたら、
	 * 必要に応じてこれらのコメントを外し、エラー出力を有効にしてみてください。
	 *
	 * エラーメッセージは問題解決の助けになります。
	 */
	@ini_set('display_errors', 1);
	@ini_set('error_reporting', E_ALL);


	return $conf;
} );
