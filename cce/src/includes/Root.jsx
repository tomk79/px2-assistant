import React, { useContext, useState, useEffect, useRef } from "react";
import { MainContext } from './context/MainContext';
import Ping from './components/Ping/Ping.jsx';
import ChatLogList from './components/ChatLogList/ChatLogList.jsx';
import Chat from './components/Chat/Chat.jsx';

const Root = React.memo((props) => {
	const [globalState, setGlobalState] = useState({
		currentChatId: null,
	});
	globalState.cceAgent = props.cceAgent;

	useEffect(() => {
		// クリーンアップ処理
		return () => {
		};
	}, []);

	return (
		<MainContext.Provider value={globalState}>
			{/* <Ping
				cceAgent={props.cceAgent} /> */}
			<div className="cce-assistant-root-layout">
				<div className="cce-assistant-root-layout__sidebar">
					<ChatLogList
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
				<div className="cce-assistant-root-layout__main">
					<Chat
						chatId={globalState.currentChatId}
						cceAgent={props.cceAgent} />
				</div>
			</div>
		</MainContext.Provider>
	);
});

export default Root;
