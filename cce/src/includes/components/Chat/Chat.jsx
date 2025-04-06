import React, { useContext, useState, useEffect, useRef } from "react";
// import { MainContext } from '../../context/MainContext';
import ChatOperator from './includes/ChatOperator.js';
import {marked} from 'marked';
import {LocalFileUtils} from '../../../includes/utils/LocalFileUtils/LocalFileUtils.js';

const Chat = React.memo((props) => {
	// const globalState = useContext(MainContext);
	const chatId = props.chatId || null;

	const [localState, setLocalState] = useState({
		chatId: chatId,
		isInitialized: false,
		log: [],
		files: [],
	});
	const chatInputRef = useRef(null);
	const selectModelRef = useRef(null);
	const sendButtonRef = useRef(null);

	const localFileUtils = new LocalFileUtils();

	const generateNewChatId = () => {
		const now = new Date();
		const year = now.getFullYear();
		const month = String(now.getMonth() + 1).padStart(2, '0');
		const date = String(now.getDate()).padStart(2, '0');
		const YYYYMMDD = `${year}${month}${date}`;
		const randomString = Math.random().toString(36).substring(2, 10);
		return `${YYYYMMDD}-${randomString}`;
	};

	function markdownToHtml(content) {
		marked.setOptions({
			renderer: new marked.Renderer(),
			gfm: true,
			headerIds: false,
			tables: true,
			breaks: false,
			pedantic: false,
			sanitize: false,
			smartLists: true,
			smartypants: false,
			xhtml: true
		});
		return marked.parse(content);
	}

	useEffect(() => {
		const chatId = props.chatId || generateNewChatId();

		if(!props.chatId){
			setLocalState(prevState => ({
				...prevState,
				chatId: chatId,
				isInitialized: true,
				log: [],
				files: [],
			}));

		}else{
			props.cceAgent.gpi({
				'command': 'chat-init',
				"chat_id": chatId,
			}, function(res, error){
				if(error || !res.result){
					alert('[ERROR] Failed to initialize chat.');
					return;
				}
				setLocalState(prevState => ({
					...prevState,
					chatId: chatId,
					isInitialized: true,
					log: res.chatLog.messages,
					files: [],
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
							<div key={index} className={`cce-assistant-chat__message ${message.role == "assistant" ? 'cce-assistant-chat__message--assistant' : 'cce-assistant-chat__message--user'}`}>
								<div className="cce-assistant-chat__message-avatar">
									{message.role == "assistant" ? '🤖' : '👤'}
								</div>
								<div className="cce-assistant-chat__message-content">
									<span className="cce-assistant-chat__message-time">{message.datetime}</span>
									<div
										 className="cce-assistant-chat__message-content-body"
										dangerouslySetInnerHTML={{ 
											__html: (markdownToHtml(message.content[0].text || message.content))
										}}
									/>
								</div>
							</div>
						))
					) : (
						<div className="cce-assistant-chat__empty-chat">
							<p>There are no messages yet. Ask a question!</p>
						</div>
					)}
				</div>

				<div className="cce-assistant-chat__input">
					<form
						onSubmit={async (e) => {
							e.preventDefault();
							const inputElement = chatInputRef.current;
							const buttonElement = sendButtonRef.current;
							const selectModelElement = selectModelRef.current;

							const userMessage = inputElement.value.trim();
							const model = selectModelElement.value.trim();

							if (userMessage) {

								const newMessage = {
									content: (localState.files.length ? [
										{
											type: "text",
											text: userMessage,
										},
										...(localState.files.map(file => ({
											type: "image_url",
											image_url: {
												url: file.base64,
											},
										}))),
									] : userMessage),
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
								chatOperator.sendMessage(newMessage.content, model)
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
												files: [],
											}));
											resolve();
										});
									})
									.catch((error) => {
										console.error(error);
										alert('[ERROR] Failed to generate message.');
									})
									.finally(() => {
										px2style.closeLoading();
										inputElement.removeAttribute('disabled');
										buttonElement.removeAttribute('disabled');

										inputElement.value = '';
										inputElement.focus();

										props.onupdatechat({
											currentChatId: localState.chatId,
										});
									});
							}
						}}
						onDrop={async (event) => {
							event.preventDefault();
							const tmpNewFiles = [];
							for(let i = 0; event.dataTransfer.files.length > i; i ++){
								tmpNewFiles.push(event.dataTransfer.files[i]);
							}
							const newFiles = await Promise.all(tmpNewFiles.map(async (fileInfo)=>{
								const base64 = await localFileUtils.readLocalFile(fileInfo);
								return {
									base64: base64,
								};
							}));
							setLocalState(prevState => ({
								...prevState,
								files: prevState.files.concat(newFiles),
							}));
						}}>
						<div className="px2-p">
							<div className="px2-input-group px2-input-group--fluid">
								<textarea
									type="text"
									name="userMessage"
									placeholder="Input message ..."
									className="px2-input cce-assistant-chat__input-field"
									ref={chatInputRef}
								></textarea>
								<button type="submit" className="px2-btn px2-btn--primary" ref={sendButtonRef}>Send</button>
							</div>
						</div>
						{localState.files.length ?
							<div className="cce-assistant-chat-attachfiles">
								<ul>
								{localState.files.map((file, index) => {
									return (
										<li key={index}><img src={file.base64} alt="" /></li>
									);
								})}
								</ul>
							</div>
						: <></>}
						<div className="px2-p">
							<select name="model" className="px2-input" ref={selectModelRef}>
								{Object.keys(props.models.chat).map((model, index) => {
									return (
										<option key={index} value={model}>{props.models.chat[model].label}</option>
									);
								})}
							</select>
						</div>
					</form>
				</div>
			</div>
		</>
	);
});

export default Chat;
