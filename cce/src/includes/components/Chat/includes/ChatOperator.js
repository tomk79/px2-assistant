import clearcache from './functions/clearcache.js';
import getConfig from './functions/getConfig.js';
import publish from './functions/publish.js';
import whatTimeIsIt from './functions/whatTimeIsIt.js';

class ChatOperator {
	#chatId;
	#cceAgent;
	#functions = {};

	constructor(chatId, cceAgent) {
		this.#chatId = chatId;
		this.#cceAgent = cceAgent;
		this.#functions['clearcache'] = new clearcache(chatId, cceAgent);
		this.#functions['getConfig'] = new getConfig(chatId, cceAgent);
		this.#functions['publish'] = new publish(chatId, cceAgent);
		this.#functions['whatTimeIsIt'] = new whatTimeIsIt(chatId, cceAgent);
	}

	async sendMessage (userMessage, model, type, calledFunctionName, calledFunctionId) {
		return new Promise((resolve, reject) => {
			if (!userMessage || !userMessage.length) {
				reject('No message given.');
			}
			this.#cceAgent.gpi({
				'command': 'chat-comment',
				'message': {
					"chat_id": this.#chatId,
					"type": type || "question",
					"name": calledFunctionName || "",
					"call_id": calledFunctionId || "",
					"content": userMessage || [],
				},
				'model': model || '',
			}, async (res, error) => {
				if(error || !res.result){
					console.log('error:', error);
					reject(`Failed to send message.`);
					return;
				}
				if(res.answer.type == "function_call"){
					let result = [];
					for(let i = 0; i < res.answer.functions.length; i++){
						if( this.#functions[res.answer.functions[i].function] ){
							result.push({
								"type": "text",
								"text": await this.#functions[res.answer.functions[i].function].run(res.answer.functions[i].args)
									.catch(e => e),
							});
						}else{
							result.push({
								"type": "text",
								"text": '[Error] undefined function.',
							});
						}
					}
					this.sendMessage(result, model, 'function_call', res.answer.functions[0].function, res.answer.call_id)
						.then((answer) => {
							resolve(answer);
						});
					return;
				}

				resolve(res.answer);
			});
		});

	}
};

export default ChatOperator;
