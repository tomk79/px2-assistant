import React, { useContext, useState, useEffect, useRef } from "react";
import { MainContext } from '../../context/MainContext';

const Chat = React.memo((props) => {
	const globalState = useContext(MainContext);
    const [localState, setLocalState] = useState({
        chatId: null,
        log: [],
    });
	const chatInputRef = useRef(null);

	useEffect(() => {
		// クリーンアップ処理
		return () => {
		};
	}, []);

    return (
        <>
            <div className="chat-ui">
                <div className="chat-messages">
                    {localState.log.length > 0 ? (
                        localState.log.map((message, index) => (
                            <div key={index} className={`chat-message ${message.isUser ? 'user-message' : 'assistant-message'}`}>
                                <div className="message-avatar">
                                    {message.isUser ? '👤' : '🤖'}
                                </div>
                                <div className="message-content">
                                    <p>{message.text}</p>
                                    <span className="message-time">{message.timestamp}</span>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="empty-chat">
                            <p>まだメッセージはありません。何か質問してみましょう！</p>
                        </div>
                    )}
                </div>
                
                <div className="chat-input">
                    <form onSubmit={(e) => {
                        e.preventDefault();
                        const inputElement = chatInputRef.current;
                        const userMessage = inputElement.value.trim();
                        
                        if (userMessage) {
                            const newMessage = {
                                text: userMessage,
                                isUser: true,
                                timestamp: new Date().toLocaleTimeString()
                            };
                            
                            setLocalState(prevState => ({
                                ...prevState,
                                log: [...prevState.log, newMessage],
                            }));
                            
                            // ここでAIの応答を生成するロジックを追加できます
                            // 例: API呼び出しなど
                            
                            inputElement.value = '';
                            inputElement.focus();
                        }
                    }}>
                        <input
                            type="text"
                            name="userMessage"
                            placeholder="メッセージを入力..."
                            className="px2-input chat-input-field"
                            ref={chatInputRef}
                        />
                        <button type="submit" className="px2-btn px2-btn--primary">送信</button>
                    </form>
                </div>
            </div>
        </>
    );
});

export default Chat;
