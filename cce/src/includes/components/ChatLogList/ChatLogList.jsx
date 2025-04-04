import React, { useContext, useState, useEffect, useRef } from "react";

const ChatLogList = React.memo((props) => {
	const [localState, setLocalState] = useState({
		chatLogList: [],
		isInitialized: false,
	});

	useEffect(() => {
		props.cceAgent.gpi({
			'command': 'get-chatlog-list',
		}, function(res, error){
			console.log('---- res:', res);
			if(error || !res.result){
				alert('[ERROR] Failed to get chat log list.');
				return;
			}
			setLocalState(prevState => ({
				...prevState,
				chatLogList: res.chatLogList,
				isInitialized: true,
			}));
		});

		// clean up
		return () => {
		};
	}, [props.chatUpdatedAt]);

	if(!localState.isInitialized){
		return (
			<div className="cce-assistant-chatlog-list">
				<div className="cce-assistant-chatlog-list__loading">
					<p>Loading...</p>
				</div>
			</div>
		);
	}

	return (
		<>
			<div className="cce-assistant-chatlog-list">
				<ul className="cce-assistant-chatlog-list__chatlog-list">
					<li>
						<button type="button" onClick={() => {props.onStartNewChat();}}><span>New chat</span></button>
					</li>
					{localState.chatLogList.length > 0 ? (
						localState.chatLogList.map((chatLog, index) => (
							<li key={index}>
								<button
									type="button"
									className={`${props.currentChatId == chatLog.chat_id ? 'is-current' : ''}`}
									onClick={() => {props.onOpenChat(chatLog.chat_id);}}><span>{chatLog.title}</span></button>
							</li>
						))
					) : <></>}
				</ul>
			</div>
		</>
	);
});

export default ChatLogList;
