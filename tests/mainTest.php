<?php

/**
 * Test
 */
class mainTest extends PHPUnit\Framework\TestCase {
	private $fs;

	/**
	 * setup
	 */
	public function setUp() : void{
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * プレビュー表示時のテスト
	 */
	public function testPreview(){

		// トップページの出力コードを検査
		$indexHtml = $this->passthru( [
			'php',
			__DIR__.'/testdata/standard/.px_execute.php' ,
			'-u', 'Mozilla/0.5',
			'/index.html' ,
		] );
		$this->assertTrue( !!preg_match('/\<h1\>HOME\<\/h1\>/si', $indexHtml) );

		// パブリッシュ
		$this->passthru( [
			'php',
			__DIR__.'/testdata/standard/.px_execute.php' ,
			'/?PX=publish.run' ,
		] );

		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testdata/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}


	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}
}
