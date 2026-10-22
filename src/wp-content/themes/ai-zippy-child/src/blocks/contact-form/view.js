// Inquiry form (docs/build/blocks/contact-form.md, logic.js:139-140): a valid
// submit swaps the card to the success pane without a reload, "Send another"
// clears the fields and swaps the form back. PHP ships the form as the no-JS /
// editor state, so the first paint never jumps.
document.querySelectorAll(".sm-contact-form").forEach((card) => {
	const form = card.querySelector(".sm-contact-form__form");
	const success = card.querySelector(".sm-contact-form__success");
	const heading = card.querySelector(".sm-contact-form__success-title");
	const reset = card.querySelector(".sm-contact-form__reset");
	const firstField = card.querySelector(".sm-contact-form__input");

	if (!form || !success) {
		return;
	}

	const show = (pane) => {
		const sent = pane === "success";
		form.hidden = sent;
		success.hidden = !sent;
	};

	form.addEventListener("submit", (event) => {
		event.preventDefault();
		if (!form.checkValidity()) {
			form.reportValidity();
			return;
		}
		show("success");
		if (heading) {
			heading.focus();
		}
	});

	if (reset) {
		reset.addEventListener("click", () => {
			form.reset();
			show("form");
			if (firstField) {
				firstField.focus();
			}
		});
	}
});
