class ChatOperator {
    #chatId;
    #cceAgent;

    constructor(chatId, cceAgent) {
        this.#chatId = chatId;
        this.#cceAgent = cceAgent;
    }

    async sendMessage (userMessage, type) {
        return new Promise((resolve, reject) => {
            if (!userMessage) {
                reject('No message given.');
            }
            this.#cceAgent.gpi({
                'command': 'chat-comment',
                'message': {
                    "chat_id": this.#chatId,
                    "type": type || "question",
                    "content": userMessage,
                },
            }, (res, error) => {
                console.log('---- res:', res);
                if(error || !res.result){
                    alert('[ERROR] 失敗しました。');
                }
                if(res.answer.type == "function_call"){
                    this.sendMessage('晴れ/時々曇り', 'function_call') // TODO: 実際のfunctionを呼び出す
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
