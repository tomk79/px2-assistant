class clearcache {
	#chatId;
	#cceAgent;

	constructor(chatId, cceAgent) {
		this.#chatId = chatId;
		this.#cceAgent = cceAgent;
	}

	async run (args) {
		return new Promise((resolve, reject) => {
			if(!confirm('Are you sure you want to clear the cache?')){
				reject('User cancelled.');
				return;
			}
			this.#cceAgent.pxCmd('/?PX=clearcache',
				{
					"progress": function(data, error){
						console.log('--- progress:', data, error);
					},
				},
				function(pxCmdStdOut, error){
					if(error){
						reject(error);
						return;
					}
					resolve(pxCmdStdOut);
				});
		});

	}
};

export default clearcache;
