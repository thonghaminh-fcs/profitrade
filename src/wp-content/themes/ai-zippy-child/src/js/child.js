// =============================================================================
// Child theme entry — S.Mart shared behaviours (owner: child.js per
// docs/build/block-map.json): mobile nav, cart drawer, slot picker
// (delivery state), scroll reveal, image fallback. Vanilla JS, no deps.
// =============================================================================

document.documentElement.classList.add("sm-js");

// -----------------------------------------------------------------------------
// Delivery state — {slot: number, express: boolean} persisted in sessionStorage,
// shared by delivery-bar, mini-cart and checkout-fulfilment.
// -----------------------------------------------------------------------------
const DELIVERY_KEY = "sm-delivery";
const DEFAULT_SLOT = 2;

export function getDelivery() {
  try {
    const raw = sessionStorage.getItem(DELIVERY_KEY);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (typeof parsed.slot === "number") {
        return { slot: parsed.slot, express: !!parsed.express };
      }
    }
  } catch (_) {
    /* fall through to default */
  }
  return { slot: DEFAULT_SLOT, express: false };
}

export function setDelivery(next) {
  const state = { ...getDelivery(), ...next };
  try {
    sessionStorage.setItem(DELIVERY_KEY, JSON.stringify(state));
  } catch (_) {
    /* storage unavailable — state stays in-memory via the DOM */
  }
  renderDelivery(state);
  document.dispatchEvent(
    new CustomEvent("sm:delivery", { detail: state })
  );
  return state;
}

function slotLabel(select, index) {
  const opt = select.options[index];
  return opt ? opt.textContent.trim() : "";
}

function deliveryNote(state) {
  if (state.express) return "Express: arrives within 60 minutes";
  const select = document.querySelector("[data-sm-slot]");
  const label = select ? slotLabel(select, state.slot) : "";
  return `Delivering today, ${label.toLowerCase()}`;
}

function renderDelivery(state) {
  document.querySelectorAll("[data-sm-slot]").forEach((select) => {
    if (select.value !== String(state.slot)) select.value = String(state.slot);
    const wrap = select.closest("[data-sm-slot-wrap]");
    if (wrap) wrap.style.opacity = state.express ? ".45" : "";
  });
  document.querySelectorAll("[data-sm-express]").forEach((btn) => {
    const on = state.express;
    btn.classList.toggle("is-on", on);
    btn.setAttribute("aria-checked", on ? "true" : "false");
  });
  document.querySelectorAll("[data-sm-note]").forEach((el) => {
    el.textContent = deliveryNote(state);
  });
}

function initSlotPicker() {
  const state = getDelivery();
  renderDelivery(state);

  document.addEventListener("change", (e) => {
    const select = e.target.closest("[data-sm-slot]");
    if (!select) return;
    setDelivery({ slot: Number(select.value), express: false });
  });

  document.addEventListener("click", (e) => {
    const toggle = e.target.closest("[data-sm-express]");
    if (!toggle) return;
    e.preventDefault();
    setDelivery({ express: !getDelivery().express });
  });
}

// -----------------------------------------------------------------------------
// Mobile nav — hamburger before the nav flips aria-expanded and .is-open.
// -----------------------------------------------------------------------------
function initMobileNav() {
  const burger = document.querySelector(".sm-header__burger");
  const nav = document.querySelector("#sm-header-nav");
  if (!burger || !nav) return;

  const setOpen = (open) => {
    burger.setAttribute("aria-expanded", open ? "true" : "false");
    nav.classList.toggle("is-open", open);
  };

  burger.addEventListener("click", () => {
    setOpen(burger.getAttribute("aria-expanded") !== "true");
  });

  nav.addEventListener("click", (e) => {
    if (e.target.closest("a")) setOpen(false);
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".sm-header__row")) setOpen(false);
  });
}

// Active nav pill = current path (mockup logic.js:76-78).
function initNavActive() {
  const nav = document.querySelector("#sm-header-nav");
  if (!nav) return;
  const path = window.location.pathname.replace(/\/+$/, "") || "/";
  nav.querySelectorAll("a").forEach((a) => {
    const href = a.getAttribute("href").replace(/\/+$/, "") || "/";
    const active =
      href === path || (href !== "/" && path.startsWith(href + "/"));
    a.classList.toggle("is-active", active);
  });
}

// -----------------------------------------------------------------------------
// Cart drawer — generic open/close for the S.Mart mini-cart drawer.
// Markup hooks (used by the mini-cart block): .sm-drawer, .sm-drawer-overlay,
// [data-sm-drawer-open], [data-sm-drawer-close].
// -----------------------------------------------------------------------------
function initCartDrawer() {
  const setOpen = (open) => {
    document.querySelectorAll(".sm-drawer").forEach((drawer) => {
      drawer.classList.toggle("is-open", open);
      drawer.setAttribute("aria-hidden", open ? "false" : "true");
    });
    document.querySelectorAll(".sm-drawer-overlay").forEach((overlay) => {
      overlay.classList.toggle("is-visible", open);
    });
    document.body.classList.toggle("sm-drawer-lock", open);
    if (open) {
      document.dispatchEvent(new CustomEvent("sm:drawer-open"));
    }
  };

  document.addEventListener("click", (e) => {
    if (e.target.closest("[data-sm-drawer-open]")) {
      e.preventDefault();
      setOpen(true);
      return;
    }
    if (
      e.target.closest("[data-sm-drawer-close]") ||
      e.target.closest(".sm-drawer-overlay")
    ) {
      e.preventDefault();
      setOpen(false);
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") setOpen(false);
  });
}

// -----------------------------------------------------------------------------
// Scroll reveal — [data-reveal] fades in once on intersection.
// -----------------------------------------------------------------------------
function initScrollReveal() {
  if (!("IntersectionObserver" in window)) return;
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          io.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12 }
  );
  document.querySelectorAll("[data-reveal]").forEach((el) => io.observe(el));
}

// -----------------------------------------------------------------------------
// Image error fallback — broken images swap to product-fallback.jpg
// (url localised as window.smAssets.fallbackImg).
// -----------------------------------------------------------------------------
function initImageFallback() {
  window.addEventListener(
    "error",
    (e) => {
      const img = e.target;
      const fallback = window.smAssets && window.smAssets.fallbackImg;
      if (
        img instanceof HTMLImageElement &&
        fallback &&
        img.src !== fallback &&
        !img.dataset.smFallbackApplied
      ) {
        img.dataset.smFallbackApplied = "1";
        img.src = fallback;
      }
    },
    true
  );
}

initSlotPicker();
initMobileNav();
initNavActive();
initCartDrawer();
initScrollReveal();
initImageFallback();
