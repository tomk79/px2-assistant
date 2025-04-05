import React, { useContext, useState, useEffect, useRef } from "react";
import { MainContext } from './context/MainContext';
import Ping from './components/Ping/Ping.jsx';
import ChatLogList from './components/ChatLogList/ChatLogList.jsx';
import Chat from './components/Chat/Chat.jsx';

const Root = React.memo((props) => {
	const [globalState, setGlobalState] = useState({
		currentChatId: null,
		chatUpdatedAt: null,
		options: null,
	});
	globalState.cceAgent = props.cceAgent;

	useEffect(() => {

		props.cceAgent.gpi({
			'command': 'bootup-information',
		}, function(res, error){
			if(error || !res.result){
				alert('[ERROR] Failed to initialize.');
				return;
			}
			setGlobalState(prevState => ({
				...prevState,
				options: res.options,
			}));
		});

		// cleanup
		return () => {
		};
	}, []);

	if(!globalState.options){
		return (<>
			<div>Loading...</div>
		</>);
	}

	return (
		<MainContext.Provider value={globalState}>
			{/* <Ping
				cceAgent={props.cceAgent} /> */}
			<div className={`cce-assistant-root-layout cce-assistant-root-layout--${globalState.cceAgent.appearance()}`}>
				<div className="cce-assistant-root-layout__main">
					<Chat
						chatId={globalState.currentChatId}
						models={globalState.options.models}
						onupdatechat={event => {
							setGlobalState(prevState => ({
								...prevState,
								currentChatId: event.currentChatId,
								chatUpdatedAt: Date.now(),
							}));
						}}
						cceAgent={props.cceAgent} />
				</div>
				<div className="cce-assistant-root-layout__sidebar">
					<ChatLogList
						currentChatId={globalState.currentChatId}
						chatUpdatedAt={globalState.chatUpdatedAt}
						onStartNewChat={() => {
							setGlobalState(prevState => ({
								...prevState,
								currentChatId: null,
							}));
						}}
						onOpenChat={(chatId) => {
							setGlobalState(prevState => ({
								...prevState,
								currentChatId: chatId,
							}));
						}}
						cceAgent={props.cceAgent} />
				</div>
			</div>
		</MainContext.Provider>
	);
});

export default Root;
