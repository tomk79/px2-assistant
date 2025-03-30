class ChatOperator {
    #chatId;
    #cceAgent;

    constructor(chatId, cceAgent) {
        this.#chatId = chatId;
        this.#cceAgent = cceAgent;
    }

    async sendMessage (userMessage) {
        return new Promise((resolve, reject) => {
            if (!userMessage) {
                reject('No message given.');
            }
            this.#cceAgent.gpi({
                'command': 'chat-comment',
                'message': {
                    "chat_id": this.#chatId,
                    "content": userMessage,
                },
            }, function(res, error){
                console.log('---- res:', res);
                if(error || !res.result){
                    alert('[ERROR] 失敗しました。');
                }

                resolve(res.answer);
            });
        });

    }
};

export default ChatOperator;
