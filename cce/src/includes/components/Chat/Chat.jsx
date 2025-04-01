import React, { useContext, useState, useEffect, useRef } from "react";
// import { MainContext } from '../../context/MainContext';
import ChatOperator from './includes/ChatOperator.js';

const Chat = React.memo((props) => {
	// const globalState = useContext(MainContext);
	const chatId = props.chatId || null;

	const [localState, setLocalState] = useState({
		chatId: chatId,
		isInitialized: false,
		log: [],
	});
	const chatInputRef = useRef(null);
	const sendButtonRef = useRef(null);

	const generateNewChatId = () => {
		const now = new Date();
		const year = now.getFullYear();
		const month = String(now.getMonth() + 1).padStart(2, '0');
		const date = String(now.getDate()).padStart(2, '0');
		const YYYYMMDD = `${year}${month}${date}`;
		const randomString = Math.random().toString(36).substring(2, 10);
		return `${YYYYMMDD}-${randomString}`;
	};

	useEffect(() => {
		const chatId = props.chatId || generateNewChatId();
		setLocalState(prevState => ({
			...prevState,
		}));


		if(!props.chatId){
			setLocalState(prevState => ({
				...prevState,
				log: [],
				chatId: chatId,
				isInitialized: true,
			}));

		}else{
			props.cceAgent.gpi({
				'command': 'chat-init',
				"chat_id": chatId,
			}, function(res, error){
				if(error || !res.result){
					alert('[ERROR] 失敗しました。');
				}
				setLocalState(prevState => ({
					...prevState,
					log: res.chatLog.messages,
					chatId: chatId,
					isInitialized: true,
				}));
			});
		}

		// clean up
		return () => {
		};
	}, [props.chatId]);

	if(localState.chatId && !localState.isInitialized){
		return (
			<div className="cce-assistant-chat">
				<div className="cce-assistant-chat__loading">
					<p>Loading...</p>
				</div>
			</div>
		);
	}

	return (
		<>
			<div className="cce-assistant-chat">
				<div className="cce-assistant-chat__messages">
					{localState.log.length > 0 ? (
						localState.log.map((message, index) => (
							<div key={index} className={`cce-assistant-chat__message ${message.role == "assistant" ? 'cce-assistant-chat__message--user' : 'cce-assistant-chat__message--assistant'}`}>
								<div className="cce-assistant-chat__message-avatar">
									{message.role == "assistant" ? '🤖' : '👤'}
								</div>
								<div className="cce-assistant-chat__message-content">
									<p>{message.content}</p>
									<span className="cce-assistant-chat__message-time">{message.datetime}</span>
								</div>
							</div>
						))
					) : (
						<div className="cce-assistant-chat__empty-chat">
							<p>まだメッセージはありません。何か質問してみましょう！</p>
						</div>
					)}
				</div>

				<div className="cce-assistant-chat__input">
					<form onSubmit={async (e) => {
						e.preventDefault();
						const inputElement = chatInputRef.current;
						const buttonElement = sendButtonRef.current;

						const userMessage = inputElement.value.trim();

						if (userMessage) {

							const newMessage = {
								content: userMessage,
								role: "user",
								datetime: new Date().toISOString(),
							};

							setLocalState(prevState => ({
								...prevState,
								log: [...prevState.log, newMessage],
							}));

							px2style.loading();
							inputElement.setAttribute('disabled', true);
							buttonElement.setAttribute('disabled', true);

							const chatId = localState.chatId;
							const chatOperator = new ChatOperator(chatId, props.cceAgent);
							chatOperator.sendMessage(userMessage)
								.then((answer) => {
									return new Promise((resolve, reject) => {

										setLocalState(prevState => ({
											...prevState,
											log: [
												...prevState.log, {
													content: answer.content,
													role: "assistant",
													datetime: new Date().toISOString(),
												},
											],
										}));
										resolve();
									});
								})
								.catch((error) => {
									console.error(error);
									alert('[ERROR] 失敗しました。');
								})
								.finally(() => {
									px2style.closeLoading();
									inputElement.removeAttribute('disabled');
									buttonElement.removeAttribute('disabled');

									inputElement.value = '';
									inputElement.focus();
								});
						}
					}}>
						<textarea
							type="text"
							name="userMessage"
							placeholder="メッセージを入力..."
							className="px2-input cce-assistant-chat__input-field"
							ref={chatInputRef}
						></textarea>
						<button type="submit" className="px2-btn px2-btn--primary" ref={sendButtonRef}>送信</button>
					</form>
				</div>
			</div>
		</>
	);
});

export default Chat;
