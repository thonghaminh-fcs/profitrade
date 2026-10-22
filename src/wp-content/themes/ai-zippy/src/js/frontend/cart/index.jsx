import { createRoot } from "react-dom/client";
import CartApp from "./CartApp.jsx";
import "./cart.scss";

const container = document.getElementById("ai-zippy-cart");

if (container) {
	const checkoutUrl = container.dataset.checkoutUrl || "/checkout";
	const shopUrl = container.dataset.shopUrl || "/shop";
	createRoot(container).render(
		<CartApp checkoutUrl={checkoutUrl} shopUrl={shopUrl} />,
	);
}
