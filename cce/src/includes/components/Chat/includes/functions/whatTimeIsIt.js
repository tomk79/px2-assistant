class whatTimeIsIt {
	#chatId;
	#cceAgent;

	constructor(chatId, cceAgent) {
		this.#chatId = chatId;
		this.#cceAgent = cceAgent;
	}

	async run (args) {
		return new Promise((resolve, reject) => {
			const now = new Date().toISOString();
			console.log('--- whatTimeIsIt:', now);
			resolve(now);
		});

	}
};

export default whatTimeIsIt;
