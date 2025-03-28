import React, { useContext, useState, useEffect, useRef } from "react";
import { MainContext } from './context/MainContext';

const Root = React.memo((props) => {
	const [globalState, setGlobalState] = useState({
        currentChat: [],
    });
    globalState.cceAgent = props.cceAgent;
	const btnRef = useRef(null);
	const chatInputRef = useRef(null);

	useEffect(() => {
		// クリーンアップ処理
		return () => {
		};
	}, []);

    return (
        <MainContext.Provider value={globalState}>
            <p><button type="button" className="px2-btn px2-btn--primary cont-btn-create-index" ref={btnRef} onClick={function(event){
                const elm = btnRef.current;
                px2style.loading();
                elm.setAttribute('disabled', true);

                props.cceAgent.gpi({
                    'command': 'ping'
                }, function(res){
                    console.log('---- res:', res);
                    if(res.result){
                        alert('疎通確認しました。');
                    }else{
                        alert('[ERROR] 疎通に失敗しました。');
                    }
                    px2style.closeLoading();
                    elm.removeAttribute('disabled');
                });

            }}>疎通確認する</button></p>

            <div className="chat-ui">
                <div className="chat-messages">
                    {globalState.currentChat.length > 0 ? (
                        globalState.currentChat.map((message, index) => (
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
                            
                            setGlobalState(prevState => ({
                                ...prevState,
                                currentChat: [...prevState.currentChat, newMessage]
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

        </MainContext.Provider>
    );
});

export default Root;
