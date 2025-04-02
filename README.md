# px2-assistant

[Pickles 2](https://pickles2.com/) にAIアシスタントを追加する管理画面拡張です。


## Usage - 使い方

### インストール

```
composer require tomk79/px2-assistant
```

### セットアップ

`px-files/config.php` の `$conf->plugins->px2dt->custom_console_extensions` に、 `px2-assistant` の設定を追加する。

```php
	$conf->plugins->px2dt->custom_console_extensions = array(
	    'px2-assistant' => array(
			'class_name' => \tomk79\pickles2\assistant\cce\main::register(array(
				"models" => array(
					"chat" => array(
						"openai-gpt-3.5-turbo" => array(
							"url" => "https://api.openai.com/v1/chat/completions",
							"model" => "gpt-3.5-turbo",
							"label" => "OpenAI gpt-3.5-turbo",
						),
                        ...
					),
				),
			)),
			'capability' => array('manage'),
		),
	);
```

OpenAI のモデルを使用するには、環境変数 `$_ENV['OPEN_AI_SECRET']`、 `$_ENV['OPEN_AI_ORG_ID']` にアクセスキーをセットしてください。

```ini
# .env
OPEN_AI_SECRET="xx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
OPEN_AI_ORG_ID="org-xxxxxxxxxxxxxx"
```

## 更新履歴 - Change log

### tomk79/px2-assistant v0.1.0 (リリース日未定)

- Initial Release



## ライセンス - License

MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
