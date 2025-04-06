class publish {
	#chatId;
	#cceAgent;

	constructor(chatId, cceAgent) {
		this.#chatId = chatId;
		this.#cceAgent = cceAgent;
	}

	async run (args) {
		return new Promise((resolve, reject) => {
			if(!confirm('Are you sure you want to run publishing?')){
				reject('User cancelled.');
				return;
			}
			this.#cceAgent.pxCmd('/?PX=publish.run',
				{
					"progress": function(data, error){
						// console.log('--- progress:', data, error);
					},
					"timeout": 12 * 60 * 60 * 1000,
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

export default publish;
