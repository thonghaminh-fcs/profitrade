// Inquiry form (docs/build/blocks/contact-form.md, logic.js:139-140): submit
// posts to admin-ajax (action sm_contact_submit, handler in the theme's
// inc/contact-form.php) with the nonce printed by render.php; the success pane
// only appears when the server confirms the mail was sent, otherwise per-field
// errors are shown. "Send another" clears the fields and swaps the form back.
// PHP ships the form as the no-JS / editor state, so the first paint never jumps.
document.querySelectorAll(".sm-contact-form").forEach((card) => {
	const form = card.querySelector(".sm-contact-form__form");
	const success = card.querySelector(".sm-contact-form__success");
	const heading = card.querySelector(".sm-contact-form__success-title");
	const reset = card.querySelector(".sm-contact-form__reset");
	const firstField = card.querySelector(".sm-contact-form__input");
	const submit = card.querySelector(".sm-contact-form__submit");

	if (!form || !success) {
		return;
	}

	const show = (pane) => {
		const sent = pane === "success";
		form.hidden = sent;
		success.hidden = !sent;
	};

	function clearErrors() {
		card
			.querySelectorAll(".sm-contact-form__error")
			.forEach((el) => el.remove());
		form
			.querySelectorAll("[aria-invalid]")
			.forEach((el) => el.removeAttribute("aria-invalid"));
	}

	function fieldError(name, message) {
		const input = form.elements.namedItem(name);
		const scope = input ? input.closest(".sm-contact-form__field") : null;
		const anchor = scope || submit;
		if (!anchor || !message) return;
		const span = document.createElement("span");
		span.className = "sm-contact-form__error";
		span.setAttribute("role", "alert");
		span.textContent = message;
		if (scope) {
			scope.appendChild(span);
			if (input) input.setAttribute("aria-invalid", "true");
		} else {
			form.insertBefore(span, submit);
		}
	}

	async function send() {
		const body = new FormData(form);
		body.append("action", "sm_contact_submit");
		body.append("nonce", card.dataset.nonce || "");
		const res = await fetch(card.dataset.ajaxUrl || "", {
			method: "POST",
			body,
			credentials: "same-origin",
		});
		return res.json();
	}

	form.addEventListener("submit", async (event) => {
		event.preventDefault();
		if (!form.checkValidity()) {
			form.reportValidity();
			return;
		}
		clearErrors();
		if (submit) submit.disabled = true;
		let json;
		try {
			json = await send();
		} catch (_) {
			json = null;
		}
		if (submit) submit.disabled = false;

		if (json && json.success) {
			form.reset();
			show("success");
			if (heading) heading.focus();
			return;
		}

		const errors = (json && json.data && json.data.errors) || {
			form: "Sorry, your message could not be sent. Please try again.",
		};
		Object.keys(errors).forEach((key) => fieldError(key, errors[key]));
		const firstError = card.querySelector(".sm-contact-form__error");
		if (firstError) firstError.scrollIntoView({ block: "center" });
	});

	if (reset) {
		reset.addEventListener("click", () => {
			form.reset();
			clearErrors();
			show("form");
			if (firstField) firstField.focus();
		});
	}
});
