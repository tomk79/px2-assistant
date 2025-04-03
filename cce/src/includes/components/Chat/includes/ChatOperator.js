import clearcache from './functions/clearcache.js';
import publish from './functions/publish.js';

class ChatOperator {
	#chatId;
	#cceAgent;
	#functions = {};

	constructor(chatId, cceAgent) {
		this.#chatId = chatId;
		this.#cceAgent = cceAgent;
		this.#functions['clearcache'] = new clearcache(chatId, cceAgent);
		this.#functions['publish'] = new publish(chatId, cceAgent);
	}

	async sendMessage (userMessage, model, type, calledFunctionName, calledFunctionId) {
		return new Promise((resolve, reject) => {
			if (!userMessage) {
				reject('No message given.');
			}
			this.#cceAgent.gpi({
				'command': 'chat-comment',
				'message': {
					"chat_id": this.#chatId,
					"type": type || "question",
					"name": calledFunctionName || "",
					"call_id": calledFunctionId || "",
					"content": userMessage,
				},
				'model': model || '',
			}, async (res, error) => {
				if(error || !res.result){
					console.log('error:', error);
					reject(`Failed to send message.`);
					return;
				}
				if(res.answer.type == "function_call"){
					let result = '';
					if( this.#functions[res.answer.function] ){
						result = await this.#functions[res.answer.function].run(res.answer.args)
							.catch(e => e);
					}else{
						result = '[Error] undefined function.';
					}
					this.sendMessage(result, model, 'function_call', res.answer.function, res.answer.call_id)
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
