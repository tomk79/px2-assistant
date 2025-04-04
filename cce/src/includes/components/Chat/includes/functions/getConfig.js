class getConfig {
	#chatId;
	#cceAgent;

	constructor(chatId, cceAgent) {
		this.#chatId = chatId;
		this.#cceAgent = cceAgent;
	}

	async run (args) {
		return new Promise((resolve, reject) => {
			const now = new Date().toISOString();
			console.log('--- getConfig:', now);
			resolve(now);
		});

	}
};

export default getConfig;
