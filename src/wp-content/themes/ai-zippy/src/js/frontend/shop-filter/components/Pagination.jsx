export default function Pagination({ current, total, onChange }) {
	const pages = [];
	const maxVisible = 5;

	let start = Math.max(1, current - Math.floor(maxVisible / 2));
	let end = Math.min(total, start + maxVisible - 1);
	start = Math.max(1, end - maxVisible + 1);

	for (let i = start; i <= end; i++) {
		pages.push(i);
	}

	return (
		<nav className="sf__pagination">
			<button
				className="sf__page-btn"
				disabled={current <= 1}
				onClick={() => onChange(current - 1)}
				type="button"
			>
				&lsaquo; Prev
			</button>

			{start > 1 && (
				<>
					<button className="sf__page-btn" onClick={() => onChange(1)} type="button">
						1
					</button>
					{start > 2 && <span className="sf__page-dots">&hellip;</span>}
				</>
			)}

			{pages.map((p) => (
				<button
					key={p}
					className={`sf__page-btn ${p === current ? "is-active" : ""}`}
					onClick={() => onChange(p)}
					type="button"
				>
					{p}
				</button>
			))}

			{end < total && (
				<>
					{end < total - 1 && (
						<span className="sf__page-dots">&hellip;</span>
					)}
					<button
						className="sf__page-btn"
						onClick={() => onChange(total)}
						type="button"
					>
						{total}
					</button>
				</>
			)}

			<button
				className="sf__page-btn"
				disabled={current >= total}
				onClick={() => onChange(current + 1)}
				type="button"
			>
				Next &rsaquo;
			</button>
		</nav>
	);
}
