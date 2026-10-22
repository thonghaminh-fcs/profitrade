// =============================================================================
// Product tabs — Description / Additional Information
// =============================================================================

/**
 * Wire up the tab groups rendered by ProductShortcode::renderTabs().
 *
 * The markup already ships with the first tab marked `is-active` and the rest
 * carrying `hidden`, so the closed state is correct before JS runs and stays
 * correct if JS never does. This only moves that state between panels.
 *
 * Visibility is driven by the `hidden` attribute — `.zp-tabs__panel[hidden]` is
 * what the stylesheet keys off. `is-active` is kept in sync on the panels too:
 * no rule uses it today, but the PHP sets it, so leaving it stale would make the
 * DOM contradict itself.
 */
export function initTabs() {
	document.querySelectorAll("[data-tabs]").forEach((group) => {
		const tabs = [...group.querySelectorAll(".zp-tabs__tab")];
		const panels = [...group.querySelectorAll(".zp-tabs__panel")];
		if (tabs.length < 2 || panels.length < 2) return;

		if (group.dataset.tabsBound === "1") return;
		group.dataset.tabsBound = "1";

		const select = (index) => {
			tabs.forEach((tab, i) => {
				const on = i === index;
				tab.classList.toggle("is-active", on);
				tab.setAttribute("aria-selected", on ? "true" : "false");
				// Roving tabindex: only the selected tab is a tab stop, so Tab
				// moves past the whole group rather than through every tab.
				tab.tabIndex = on ? 0 : -1;
			});

			panels.forEach((panel, i) => {
				const on = i === index;
				panel.classList.toggle("is-active", on);
				panel.toggleAttribute("hidden", !on);
			});
		};

		tabs.forEach((tab, i) => {
			tab.addEventListener("click", () => select(i));

			// role="tablist" promises arrow-key navigation to screen readers, so
			// the markup is already advertising this behaviour.
			tab.addEventListener("keydown", (e) => {
				const last = tabs.length - 1;
				let next = null;

				if (e.key === "ArrowRight") next = i === last ? 0 : i + 1;
				else if (e.key === "ArrowLeft") next = i === 0 ? last : i - 1;
				else if (e.key === "Home") next = 0;
				else if (e.key === "End") next = last;
				else return;

				e.preventDefault();
				select(next);
				tabs[next].focus();
			});
		});

		// Sync tabindex with the server-rendered active tab.
		select(Math.max(0, tabs.findIndex((t) => t.classList.contains("is-active"))));
	});
}
