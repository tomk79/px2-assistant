window.assistantCceFront = function(cceAgent){
	let $elm = cceAgent.elm();

	$elm.innerHTML = `
		<p><button type="button" class="px2-btn px2-btn--primary cont-btn-create-index">疎通確認する</button></p>
	`;

	$elm.querySelector('button')
		.addEventListener('click', function(){
			const elm = this;
			px2style.loading();
			elm.setAttribute('disabled', true);

			cceAgent.gpi({
				'command': 'ping'
			}, function(res){
				console.log('---- res:', res);
				if(res.result){
					alert('疎通確認しました。');
				}else{
					alert('[ERROR] 疎通に失敗しました。');
				}
				px2style.closeLoading();
				elm.removeAttribute('disabled');
			});
		});
}