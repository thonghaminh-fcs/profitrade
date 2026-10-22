import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { createRoot } from "react-dom/client";
import ProductGrid from "./shop-filter/components/ProductGrid.jsx";
import Pagination from "./shop-filter/components/Pagination.jsx";
import { fetchProducts } from "./shop-filter/api.js";
import useWishlist from "./shop-filter/useWishlist.js";

const WISHLIST_PER_PAGE = 12;

function WishlistApp({ config }) {
	const wishlist = useWishlist();
	const [products, setProducts] = useState([]);
	const [loading, setLoading] = useState(() => wishlist.ids.length > 0);
	const [error, setError] = useState(false);
	const [retryKey, setRetryKey] = useState(0);
	const [page, setPage] = useState(1);
	const [pages, setPages] = useState(0);
	const [confirmation, setConfirmation] = useState(null);
	const dialogRef = useRef(null);
	const cancelButtonRef = useRef(null);
	const wishlistKey = wishlist.ids.join(",");
	const messages = config.i18n || {};
	const confirmOpen = confirmation !== null;
	const removingProduct = confirmation?.type === "remove" ? confirmation.product : null;

	useEffect(() => {
		let cancelled = false;
		const requestedIds = wishlist.ids;

		if (requestedIds.length === 0) {
			setProducts([]);
			setPages(0);
			setPage(1);
			setLoading(false);
			setError(false);
			return undefined;
		}

		setLoading(true);
		setError(false);

		fetchProducts(
			{
				include: requestedIds.join(","),
				page,
				per_page: WISHLIST_PER_PAGE,
			},
			config.restUrl,
		)
			.then((data) => {
				if (cancelled) return;

				const nextPages = Number(data.pages) || 0;
				setPages(nextPages);

				if (nextPages > 0 && page > nextPages) {
					setPage(nextPages);
					return;
				}

				const productsById = new Map(
					(data.products || []).map((product) => [Number(product.id), product]),
				);
				setProducts(
					requestedIds
						.map((id) => productsById.get(id))
						.filter(Boolean),
				);
			})
			.catch(() => {
				if (!cancelled) setError(true);
			})
			.finally(() => {
				if (!cancelled) setLoading(false);
			});

		return () => {
			cancelled = true;
		};
	}, [wishlistKey, retryKey, config.restUrl, page]);

	useEffect(() => {
		const lastPage = Math.max(1, Math.ceil(wishlist.ids.length / WISHLIST_PER_PAGE));
		if (page > lastPage) setPage(lastPage);
	}, [page, wishlist.ids.length]);

	const countLabel = useMemo(() => {
		const noun = wishlist.ids.length === 1 ? messages.item : messages.items;
		return `${wishlist.ids.length} ${noun || "items saved"}`;
	}, [messages.item, messages.items, wishlist.ids.length]);

	const handleRemoveRequest = useCallback((product) => {
		setConfirmation({ type: "remove", product });
	}, []);

	const handleClear = useCallback(() => {
		setConfirmation({ type: "clear" });
	}, []);

	const closeConfirmation = useCallback(() => {
		setConfirmation(null);
	}, []);

	const handleConfirm = useCallback(() => {
		if (confirmation?.type === "remove") {
			wishlist.remove(confirmation.product.id);
		} else {
			wishlist.clear();
		}
		setConfirmation(null);
	}, [confirmation, wishlist.clear, wishlist.remove]);

	useEffect(() => {
		if (!confirmOpen) return undefined;

		const previousFocus = document.activeElement;
		const handleKeyDown = (event) => {
			if (event.key === "Escape") {
				closeConfirmation();
				return;
			}

			if (event.key !== "Tab" || !dialogRef.current) return;

			const focusable = dialogRef.current.querySelectorAll("button");
			const first = focusable[0];
			const last = focusable[focusable.length - 1];

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last?.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first?.focus();
			}
		};

		document.body.classList.add("az-wishlist-modal-open");
		document.addEventListener("keydown", handleKeyDown);
		cancelButtonRef.current?.focus();

		return () => {
			document.body.classList.remove("az-wishlist-modal-open");
			document.removeEventListener("keydown", handleKeyDown);
			previousFocus?.focus();
		};
	}, [closeConfirmation, confirmOpen]);

	if (error) {
		return (
			<div className="az-wishlist__state" role="alert">
				<h2>{messages.error || "We could not load your wishlist."}</h2>
				<button type="button" onClick={() => setRetryKey((value) => value + 1)}>
					{messages.retry || "Try again"}
				</button>
			</div>
		);
	}

	if (!loading && wishlist.ids.length === 0) {
		return (
			<div className="az-wishlist__state az-wishlist__state--empty">
				<svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true">
					<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
				</svg>
				<h2>{messages.emptyTitle || "Your wishlist is empty"}</h2>
				<p>{messages.emptyMessage || "Save products you love and they will appear here."}</p>
				<a className="az-wishlist__shop-link" href={config.shopUrl}>
					{messages.shopButton || "Browse products"}
				</a>
			</div>
		);
	}

	return (
		<>
			{!loading && (
				<div className="az-wishlist__toolbar">
					<p aria-live="polite">{countLabel}</p>
					<button type="button" onClick={handleClear}>
						{messages.clear || "Clear wishlist"}
					</button>
				</div>
			)}

			<ProductGrid
				products={products}
				loading={loading}
				viewMode="grid"
				perPage={WISHLIST_PER_PAGE}
				wishlistEnabled
				wishlistMode="remove"
				wishlistRemoveLabel={messages.removeLabel || "Remove %s from wishlist"}
				onWishlistRequest={handleRemoveRequest}
			/>

			{!loading && pages > 1 && (
				<Pagination current={page} total={pages} onChange={setPage} />
			)}

			{confirmOpen && (
				<div
					className="az-wishlist-modal"
					onMouseDown={(event) => {
						if (event.target === event.currentTarget) closeConfirmation();
					}}
				>
					<div
						ref={dialogRef}
						className="az-wishlist-modal__dialog"
						role="dialog"
						aria-modal="true"
						aria-labelledby="az-wishlist-confirm-title"
						aria-describedby="az-wishlist-confirm-message"
					>
						<button
							className="az-wishlist-modal__close"
							type="button"
							onClick={closeConfirmation}
							aria-label={messages.cancel || "Cancel"}
						>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
								<line x1="18" y1="6" x2="6" y2="18" />
								<line x1="6" y1="6" x2="18" y2="18" />
							</svg>
						</button>
						<div className="az-wishlist-modal__icon" aria-hidden="true">
							{removingProduct ? (
								<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
									<line x1="18" y1="6" x2="6" y2="18" />
									<line x1="6" y1="6" x2="18" y2="18" />
								</svg>
							) : (
								<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
									<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
								</svg>
							)}
						</div>
						<h2 id="az-wishlist-confirm-title">
							{removingProduct
								? messages.removeTitle || "Remove from wishlist?"
								: messages.clearTitle || "Clear your wishlist?"}
						</h2>
						<p id="az-wishlist-confirm-message">
							{removingProduct
								? (messages.removeMessage || 'Remove “%s” from your wishlist?').replace("%s", removingProduct.name)
								: messages.clearMessage || "This will remove every saved product. This action cannot be undone."}
						</p>
						<div className="az-wishlist-modal__actions">
							<button
								ref={cancelButtonRef}
								className="az-wishlist-modal__cancel"
								type="button"
								onClick={closeConfirmation}
							>
								{messages.cancel || "Cancel"}
							</button>
							<button
								className="az-wishlist-modal__confirm"
								type="button"
								onClick={handleConfirm}
							>
								{removingProduct
									? messages.confirmRemove || "Remove product"
									: messages.confirmClear || "Clear wishlist"}
							</button>
						</div>
					</div>
				</div>
			)}
		</>
	);
}

const container = document.getElementById("ai-zippy-wishlist");

if (container) {
	let config = {};
	try {
		config = JSON.parse(container.dataset.config || "{}");
	} catch {
		config = {};
	}

	createRoot(container).render(<WishlistApp config={config} />);
}
