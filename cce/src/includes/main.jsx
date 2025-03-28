import React, { useContext, useState, useEffect } from "react";
import ReactDOM from "react-dom";
import Root from "./Root.jsx";

export function renderReactApp(targetElement, cceAgent) {
	// Use the global React and ReactDOM objects
	ReactDOM.render(
		<Root
			cceAgent={cceAgent} />,
		targetElement
	);
}
