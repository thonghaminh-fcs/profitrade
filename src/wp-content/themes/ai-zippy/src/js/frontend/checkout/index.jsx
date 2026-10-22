import { createRoot } from "react-dom/client";
import CheckoutApp from "./CheckoutApp.jsx";
import "./checkout.scss";

const container = document.getElementById("ai-zippy-checkout");

if (container) {
	const cartUrl = container.dataset.cartUrl || "/cart";
	const shopUrl = container.dataset.shopUrl || "/shop";

	createRoot(container).render(
		<CheckoutApp cartUrl={cartUrl} shopUrl={shopUrl} />,
	);
}
