import React, { useContext, useState, useEffect, useRef } from "react";
import { MainContext } from '../../context/MainContext';

const Chat = React.memo((props) => {
	const globalState = useContext(MainContext);
	const [localState, setLocalState] = useState({
		chatId: '20250329-gcba9wei', // TODO: ここはクライアント側で動的に生成するようにする
		log: [],
	});
	const chatInputRef = useRef(null);
	const sendButtonRef = useRef(null);

	useEffect(() => {
		// クリーンアップ処理
		return () => {
		};
	}, []);

	return (
		<>
			<div className="cce-assistant-chat">
				<div className="cce-assistant-chat__messages">
					{localState.log.length > 0 ? (
						localState.log.map((message, index) => (
							<div key={index} className={`cce-assistant-chat__message ${message.isBot ? 'cce-assistant-chat__message--user' : 'cce-assistant-chat__message--assistant'}`}>
								<div className="cce-assistant-chat__message-avatar">
									{message.isBot ? '🤖' : '👤'}
								</div>
								<div className="cce-assistant-chat__message-content">
									<p>{message.text}</p>
									<span className="cce-assistant-chat__message-time">{message.timestamp}</span>
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
								text: userMessage,
								isBot: false,
								timestamp: new Date().toLocaleTimeString(),
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
											text: res.answer.text,
											isBot: true,
											timestamp: new Date().toLocaleTimeString(),
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
