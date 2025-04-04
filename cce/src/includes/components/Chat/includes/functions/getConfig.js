class getConfig {
	#chatId;
	#cceAgent;

	constructor(chatId, cceAgent) {
		this.#chatId = chatId;
		this.#cceAgent = cceAgent;
	}

	async run (args) {
		return new Promise((resolve, reject) => {
			this.#cceAgent.pxCmd('/?PX=api.get.config',
				{
					"progress": function(data, error){
						console.log('--- progress:', data, error);
					},
				},
				function(pxCmdStdOut, error){
					console.log('--- getConfig:', pxCmdStdOut, error);
					if(error){
						reject(error);
						return;
					}
					resolve(JSON.stringify(pxCmdStdOut));
				});
		});

	}
};

export default getConfig;
