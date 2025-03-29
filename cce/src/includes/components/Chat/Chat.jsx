import React, { useContext, useState, useEffect, useRef } from "react";
import { MainContext } from '../../context/MainContext';

const Chat = React.memo((props) => {
	const globalState = useContext(MainContext);
	const [localState, setLocalState] = useState({
		chatId: '20250329-gcba9wei', // TODO: ここはクライアント側で動的に生成するようにする
		isInitialized: false,
		log: [],
	});
	const chatInputRef = useRef(null);
	const sendButtonRef = useRef(null);

	useEffect(() => {
		props.cceAgent.gpi({
			'command': 'chat-init',
			"chat_id": localState.chatId,
		}, function(res, error){
			console.log('---- res:', res);
			if(error || !res.result){
				alert('[ERROR] 失敗しました。');
			}
			setLocalState(prevState => ({
				...prevState,
				log: [
					...prevState.log,
					...res.chatLog.messages,
				],
				isInitialized: true,
			}));
		});

		// clean up
		return () => {
		};
	}, [localState.chatId]);

	if(!localState.isInitialized){
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
					<form onSubmit={(e) => {
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

							// ここでAIの応答を生成するロジックを追加できます
							// 例: API呼び出しなど
							px2style.loading();
							inputElement.setAttribute('disabled', true);
							buttonElement.setAttribute('disabled', true);

							props.cceAgent.gpi({
								'command': 'chat-comment',
								'message': {
									"chat_id": localState.chatId,
									"text": userMessage,
								},
							}, function(res, error){
								console.log('---- res:', res);
								if(error || !res.result){
									alert('[ERROR] 失敗しました。');
								}

								setLocalState(prevState => ({
									...prevState,
									log: [
										...prevState.log, {
											content: res.answer.content,
											role: "assistant",
											datetime: new Date().toISOString(),
										},
									],
								}));

								px2style.closeLoading();
								inputElement.removeAttribute('disabled');
								buttonElement.removeAttribute('disabled');

								inputElement.value = '';
								inputElement.focus();
							});
						}
					}}>
						<input
							type="text"
							name="userMessage"
							placeholder="メッセージを入力..."
							className="px2-input cce-assistant-chat__input-field"
							ref={chatInputRef}
						/>
						<button type="submit" className="px2-btn px2-btn--primary" ref={sendButtonRef}>送信</button>
					</form>
				</div>
			</div>
		</>
	);
});

export default Chat;
