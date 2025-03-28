import React from "react";
import ReactDOM from "react-dom";
import Root from "./Root.jsx";

export function renderReactApp(targetElement, cceAgent) {
	ReactDOM.render(
		<Root
			cceAgent={cceAgent} />,
		targetElement
	);
}
