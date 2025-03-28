import React, { useContext, useState, useEffect, useRef } from "react";
import { MainContext } from './context/MainContext';

const Root = React.memo((props) => {
	const [globalState, setGlobalState] = useState({});
	const btnRef = useRef(null);

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
        </MainContext.Provider>
    );
});

export default Root;
