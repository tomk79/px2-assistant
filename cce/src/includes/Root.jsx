import React, { useContext, useState, useEffect, useRef } from "react";
import { MainContext } from './context/MainContext';
import Ping from './components/Ping/Ping.jsx';
import Chat from './components/Chat/Chat.jsx';

const Root = React.memo((props) => {
	const [globalState, setGlobalState] = useState({
    });
    globalState.cceAgent = props.cceAgent;

	useEffect(() => {
		// クリーンアップ処理
		return () => {
		};
	}, []);

    return (
        <MainContext.Provider value={globalState}>
            <Ping
                cceAgent={props.cceAgent} />
            <Chat
                cceAgent={props.cceAgent} />
        </MainContext.Provider>
    );
});

export default Root;
