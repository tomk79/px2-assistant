import { renderReactApp } from './includes/main.jsx';

window.assistantCceFront = function(cceAgent){
	let $elm = cceAgent.elm();

	renderReactApp($elm, cceAgent);
}