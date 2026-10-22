import { createRoot } from "react-dom/client";
import ShopFilter from "./ShopFilter.jsx";

const container = document.getElementById("ai-zippy-shop-filter");

if (container) {
	const config = JSON.parse(container.dataset.config || "{}");
	createRoot(container).render(<ShopFilter config={config} />);
}
