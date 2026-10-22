# contact-form

- Source: `.mockup/slim.html:377-408`; logic `.mockup/logic.js:139-140`
- Registration: `ai-zippy/contact-form`; root class `sm-contact-form`.

## Copy (exact)

- Form title (800 22px): `Send an inquiry`
- Fields (labels 600 13px, column gap 6px):
  - `Name` — input required, placeholder `Your name`
  - `Email` — input type email required, placeholder `you@email.com`
  - `Message` — textarea required rows 5, placeholder `How can we help?`
- Submit: `Send message` (red pill, 700 15px, `padding:14px 28px`, hover `#E00F0F`, align-self start)
- Success pane (replaces form after send): ✓ blob 60px bg `#39FF14` radius A with `✓` 800 26px; `Message sent` (800 24px); body (`#4B5563`, /1.6): `Thanks for reaching out. We'll reply by email within one working day.`; button `Send another` (bg `#F3F4F6`, 600 14px, `padding:10px 18px`, pill) resets to the form.

## Style

- Card: white, border `#EEF0F3`, radius 28px, padding 28px; form column gap 14px.
- Inputs: border `1px solid #E5E7EB`, bg `#F9FAFB`, radius 14px, `padding:13px 14px`, 15px, `outline-color:#111827`; textarea `resize:vertical`.

## Behavior

- child.js: submit → `fetch` admin-ajax (`admin-ajax.php?action=sm_contact`, nonce rendered by block) → PHP `wp_mail` to admin email; on success swap in the success pane (mockup has no email backend — see report questions). Honeypot field for spam. No page reload.

## Attributes

| attr | default |
|---|---|
| title | `Send an inquiry` |
| successTitle | `Message sent` |
| successBody | `Thanks for reaching out. We'll reply by email within one working day.` |
| recipient | '' (empty → site admin email) |

## Responsive

- Second column of the contact grid (`minmax(min(100%,380px),1fr)`); full width under the map card on mobile.
