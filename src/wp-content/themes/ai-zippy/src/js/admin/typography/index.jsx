import { createRoot, StrictMode } from "@wordpress/element";
import App from "./App.jsx";
import "./admin-typography.scss";

const mount = document.getElementById("ai-zippy-typography-app");
if (mount) {
	createRoot(mount).render(
		<StrictMode>
			<App />
		</StrictMode>,
	);
}
