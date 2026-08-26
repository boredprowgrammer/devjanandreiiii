# LORCAPP UI Design System

A guide to the visual language used on the Launchpad, and how to apply it consistently
across every screen, app, and component in LORCAPP.

The short version: **restraint over decoration.** Every choice below exists to make the
interface read as a professional records system — calm, legible, and quiet — rather than
a generic AI-generated dashboard. When in doubt, remove an element before you add one.

---

## 1. Core Principle

> **Color and weight carry meaning. Structure carries hierarchy. Nothing decorates for its own sake.**

Concretely, that means:

- One accent color, used sparingly, only for things the user can act on.
- Two semantic colors (success / error), used only for state — never as decoration.
- Hierarchy comes from type size, weight, and spacing — not from color, shadow, or gradients.
- If two elements look different, there must be a reason a user would care about. If there
  isn't, make them look the same.

Before adding any new visual treatment, ask: **"What is this communicating, and could I
communicate it with less?"**

---

## 2. Color System

All colors are defined as Tailwind tokens so they stay consistent everywhere. Never
introduce a one-off hex value in a component — extend the token set instead (see §8).

| Token | Light value | Dark value | Use for |
|---|---|---|---|
| `ink-950` | `#14181F` | — | App background (dark mode) |
| `ink-900` | `#1B212C` | — | Primary text (dark mode), surfaces (dark mode) |
| `ink-800` | `#242C39` | — | Avatar fills, secondary surfaces |
| `ink-700` | `#3A4453` | — | Body text, icons |
| `ink-500` | `#67707E` | — | Secondary/muted text, labels |
| `ink-300` | `#A6ADB8` | — | Disabled text, inactive icons |
| `paper-50` | `#FFFFFF` | — | Card / surface background |
| `paper-100` | `#F6F5F2` | — | App background (light mode) |
| `paper-200` | `#EAE8E2` | — | Subtle dividers, hover fills |
| `accent-500/600` | `#8A6B34` / `#71581F` | — | Links, focus states, active/selected — **the only decorative color** |
| `good-500/600` | `#3F7A5C` / `#336249` | — | Success state only |
| `bad-500/600` | `#A2453F` / `#873832` | — | Error / destructive state only |

### Rules

1. **No category colors.** Don't assign a different color per app, module, or content
   type "for visual variety" (the original launchpad design did this — blue for one app,
   purple for another, etc. — and it's exactly what makes an interface look generated
   rather than designed). One neutral tile treatment for icons, everywhere.
2. **`accent` is not decorative.** Use it only where the user can click, and only when you
   want to draw the eye to the single most important action on the screen. If a screen
   has more than one accent-colored element competing for attention, that's a sign
   something else should be visual hierarchy (size/weight) instead.
3. **`good`/`bad` are state, not brand.** Reserve them for success/error alerts, destructive
   actions (delete, logout), and status indicators tied to real data (e.g. "approved" /
   "rejected"). Never use them to make a card "pop."
4. **Neutrals do the heavy lifting.** Most of any screen should be built from `ink` and
   `paper` shades. If a component looks flat, fix it with spacing or a border — not color.

---

## 3. Typography

Two typefaces, each with one job:

| Typeface | Role | When to use |
|---|---|---|
| **Source Serif 4** (`font-display`) | Editorial, human | Page-level and section-level headings only (e.g. "Welcome back, Maria") |
| **Inter** (`font-sans`, default) | Functional, neutral | Everything else — nav, labels, body copy, buttons, tables, forms |

### Rules

- **Serif is rare on purpose.** If a screen has more than one serif headline, you're
  probably using it as decoration rather than as a hierarchy marker. Reserve it for the
  single most important heading on a page.
- **No mixed-weight noise.** Stick to `font-medium` (500) and `font-semibold` (600) for
  emphasis. Avoid `font-bold` (700)+ except in truly rare cases (e.g. a badge count) —
  it reads as shouting next to this palette.
- **Uppercase, tracked labels are a section marker, not a style.** Use the pattern
  `text-xs font-medium uppercase tracking-wider text-ink-500` only for section eyebrows
  (e.g. "Applications", "Administration"). Don't sprinkle it onto body copy, card titles,
  or buttons — it stops meaning "section" the moment it shows up everywhere.
- **Size scale.** Use this scale and nothing finer-grained:

  | Class | Use |
  |---|---|
  | `text-[26px]` (serif) | Page welcome / hero heading |
  | `text-base` | Card titles, primary UI labels |
  | `text-sm` | Body copy, nav items, list rows |
  | `text-xs` | Secondary/meta text, section eyebrows, timestamps |

---

## 4. Iconography

- **Style:** outline/line icons only (1.6–1.8 stroke weight), never filled/solid icon sets,
  never duotone or gradient icons.
- **Treatment:** every functional icon sits in a flat neutral tile —
  `bg-ink-900/5 dark:bg-paper-100/10`, `text-ink-700 dark:text-paper-200`, `rounded-lg`.
  No colored tiles, no gradients, no per-item color coding.
- **Size follows role**, not decoration:

  | Context | Tile | Glyph |
  |---|---|---|
  | Primary nav / feature card (e.g. launchpad apps) | `w-14 h-14` | `w-7 h-7` |
  | Secondary list icon (if ever needed) | `w-9 h-9` | `w-4.5 h-4.5` |
  | Inline UI icon (header, buttons, alerts) | — | `w-4–5 h-4–5` |

  Pick the smallest size that's still comfortable to tap/click (44px minimum touch target
  including padding on mobile), and use the *same* size for every icon at that role across
  the whole app. A "Requests" icon and a "Transfers" icon should always match in size.

---

## 5. Surfaces, Borders & Elevation

- **No drop shadows on static content.** Cards, list rows, and panels are defined by a
  1px border (`border border-ink-900/10 dark:border-paper-100/10`), not by shadow. Reserve
  shadow (`shadow-xl`/`shadow-2xl`) exclusively for things that float above content:
  dropdown menus, modals, the page loader.
- **Radius scale — pick one per element type and stay consistent:**

  | Radius | Use |
  |---|---|
  | `rounded-md` (6px) | Buttons, inputs, small tiles, dropdown items |
  | `rounded-lg` (8px) | Cards, icon tiles, modals, list containers |
  | `rounded-full` | Avatars, status dots, badge pills only |

  Never mix `rounded-xl`/`rounded-2xl`/`rounded-3xl` into this scale — they read as
  "generic SaaS" and don't match anything else in the system.
- **Hover state = border, not motion.** On interactive cards, darken the border
  (`hover:border-ink-900/25`) and optionally nudge a trailing arrow
  (`group-hover:translate-x-0.5`). Avoid `scale`, `-translate-y`, or shadow-pop hovers —
  they add visual noise without adding information.

---

## 6. Spacing

Use a single spacing rhythm so density feels intentional, not accidental:

- **Section gap:** `mb-12` between major page sections (e.g. "Applications" →
  "Administration").
- **Section label to content:** `mb-4` between an eyebrow label and what follows.
- **Card padding:** `p-4`–`p-5` depending on content density (denser lists can use `p-4`;
  feature cards with larger icons use `p-5`).
- **Grid gaps:** `gap-3`–`gap-4` between cards in a grid; `gap-2`–`gap-3` inside a
  card between icon/text/affordance.

If a new component doesn't fit neatly into this rhythm, round to the nearest step above
rather than inventing a new spacing value.

---

## 7. Motion

Motion is a whisper, not a gesture:

- Transitions: `transition-colors` or `transition-all duration-150`. Nothing longer than
  ~200ms for hover/interaction states.
- Modals/dropdowns may use a short scale+fade entrance (`scale-95 → scale-100`,
  `opacity-0 → opacity-100`), because they're genuinely appearing/disappearing.
- Static content (cards, list rows, buttons) should **never** scale, bounce, or lift on
  hover. A border color change and, where relevant, a 2px icon nudge is enough.

---

## 8. Component Patterns

Use these as templates. When you build a new screen, reach for one of these first instead
of inventing a new pattern.

### 8.1 Feature card (grid of destinations — e.g. Launchpad apps)
```html
<a href="..." class="group flex items-center gap-4 p-5 bg-paper-50 dark:bg-ink-900
   border border-ink-900/10 dark:border-paper-100/10 rounded-lg
   hover:border-ink-900/25 dark:hover:border-paper-100/25 transition-colors duration-150">
  <div class="w-14 h-14 shrink-0 rounded-lg bg-ink-900/5 dark:bg-paper-100/10
       flex items-center justify-center text-ink-700 dark:text-paper-200">
    <svg class="w-7 h-7" ...></svg>
  </div>
  <div class="min-w-0 flex-1">
    <h3 class="font-medium text-base text-ink-900 dark:text-paper-50">Title</h3>
  </div>
  <svg class="w-4 h-4 text-ink-300 dark:text-ink-500
       group-hover:text-ink-700 dark:group-hover:text-paper-200
       group-hover:translate-x-0.5 transition-all shrink-0">...</svg>
</a>
```
Use this for anything that navigates the user somewhere: apps, top-level records, a
document, a case file. Keep titles to 2–3 words; if a card needs a longer explanation,
that's a sign it needs its own page, not more text on the tile.

### 8.2 List row (ledger-style — e.g. Administration, Quick Links, table rows)
```html
<div class="border border-ink-900/10 dark:border-paper-100/10 rounded-lg
     divide-y divide-ink-900/10 dark:divide-paper-100/10
     bg-paper-50 dark:bg-ink-900 overflow-hidden">
  <a href="..." class="flex items-center px-4 py-2.5 text-sm
     text-ink-800 dark:text-paper-200
     hover:bg-ink-900/5 dark:hover:bg-paper-100/5 transition-colors">
    Label
  </a>
  <!-- repeat, divide-y draws the separators -->
</div>
```
Use for flat lists of actions/settings where an icon+description card would be
overkill (admin menus, settings, secondary links).

### 8.3 Alert / inline message
```html
<div class="px-4 py-3 bg-paper-50 dark:bg-ink-900 border border-bad-500/30
     border-l-[3px] border-l-bad-600 rounded-md flex items-start gap-3">
  <svg class="w-4.5 h-4.5 text-bad-600 mt-0.5 flex-shrink-0">...</svg>
  <div class="flex-1">
    <p class="text-sm font-medium text-ink-900 dark:text-paper-50">Headline</p>
    <p class="text-sm text-ink-500 dark:text-paper-300 mt-0.5">Detail text.</p>
  </div>
</div>
```
Swap `bad` for `good` on success. Left border color is the *only* signal of type —
don't also tint the whole background a strong color.

### 8.4 Badge / status tag
```html
<span class="inline-block px-1.5 py-0.5 text-[10px] font-medium rounded
     bg-bad-500/10 text-bad-600">URGENT</span>
```
Flat background at low opacity, text in the solid color, uppercase, no border. Use
`good`/`bad`/`accent`/neutral only — never invent a new hue for a new status.

### 8.5 Modal
```html
<div class="bg-paper-50 dark:bg-ink-900 rounded-lg shadow-2xl border
     border-ink-900/10 dark:border-paper-100/10 w-full max-w-2xl">
  <div class="px-5 py-4 border-b border-ink-900/10 dark:border-paper-100/10"><!-- header --></div>
  <div class="p-4"><!-- content --></div>
  <div class="px-5 py-3.5 border-t border-ink-900/10 dark:border-paper-100/10"><!-- actions --></div>
</div>
```
Header/footer are separated by hairline borders, not background color changes.
Primary action button is filled `ink` (or `paper` in dark mode); secondary is
bordered/outline. Never use `accent` for a "Cancel" or "Close" button.

### 8.6 Buttons

| Type | Classes |
|---|---|
| Primary | `bg-ink-900 dark:bg-paper-50 text-paper-50 dark:text-ink-900 hover:bg-ink-800 dark:hover:bg-paper-200` |
| Secondary | `bg-paper-50 dark:bg-ink-800 border border-ink-900/15 dark:border-paper-100/15 text-ink-800 dark:text-paper-200 hover:bg-ink-900/5` |
| Destructive text link | `text-bad-600 dark:text-bad-500 hover:bg-bad-500/5` |

All buttons: `rounded-md`, `text-sm font-medium`, `px-3.5–4 py-1.5–2`. No accent-colored
buttons except for the single primary action of a flow that isn't otherwise the default
"submit" (rare — most primary actions should just be `ink`, not `accent`).

---

## 9. Dark Mode

Dark mode is not an inverted color scheme — it's the same hierarchy in ink instead of
paper. When adding a new component:

1. Every `ink-*` surface/text class needs a `paper-*` dark equivalent, and vice versa.
2. Borders go from `ink-900/10` to `paper-100/10` — always around 10% opacity for hairlines,
   ~15–25% for something that needs to read as a stronger boundary (e.g. hover state).
3. `accent`, `good`, and `bad` stay the same hue in both modes (adjust opacity of their
   backgrounds if needed, e.g. `bg-good-500/10`), so status meaning never changes between
   themes.
4. Test every new component in both modes before shipping — the most common bug in this
   system is a class that was only given a light-mode treatment.

---

## 10. Accessibility

- Maintain body text contrast of at least 4.5:1 — `ink-700`/`ink-800` on `paper-50` and
  `paper-200` on `ink-900` both pass; don't go lighter than `ink-500` for anything other
  than genuinely secondary/meta text.
- Icon-only buttons (bell, dark-mode toggle, close) always get a `title` or `aria-label`.
- Interactive rows/cards are real `<a>`/`<button>` elements, never a `<div onclick>`, so
  keyboard and screen-reader navigation work for free.
- Focus states: don't remove browser/Tailwind focus rings. If you need to restyle one,
  use the `accent` color so focus is visually distinct from hover.

---

## 11. How to Apply This to a New Screen or Component

Work through these questions, in order, before writing markup:

1. **What is the one thing on this screen the user is most likely to want to do?**
   That's your only candidate for an `accent`-colored element.
2. **What are the categories of content here?** (list of items, a single record, a form,
   a dashboard summary) — pick the matching pattern from §8 rather than designing from
   scratch.
3. **Does every visual difference on this screen mean something?** If two things look
   different but there's no reason a user needs to distinguish them, make them match.
4. **Have I introduced any new color, radius, shadow, or font weight?** If yes, stop —
   express it with an existing token instead. If the system genuinely can't express it,
   that's a signal to extend the token set deliberately (see below), not to freehand a
   value inside one component.
5. **Does it work in dark mode, at mobile width, and with a screen reader?**

### Extending the system (when you actually need to)

If a real gap exists — e.g. a new semantic state like "pending" — add it as a new named
token in `tailwind.config` (`pending-500/600`) with a light and dark value, document it
in the table in §2, and reuse it everywhere that state appears. Never add a one-off color
inline in a single component's class list.

---

## 12. Anti-Patterns (what this system is deliberately not)

Avoid drifting back toward these — they're the default look this system was designed to
replace:

- ❌ A different accent color per app/card/category "for visual interest"
- ❌ Gradient icon tiles or gradient buttons
- ❌ `rounded-xl`/`rounded-2xl` + big soft drop shadows on every card
- ❌ Hover states that scale, lift, and shadow-pop simultaneously
- ❌ Mono/uppercase/letter-spaced labels used everywhere instead of just section headers
- ❌ Subtitle copy under every title "for context" when the title already says it
- ❌ Bold, saturated color as the primary way to show hierarchy instead of size/weight/spacing

If a new screen starts to resemble this list, that's the signal to simplify, not to
add a design review comment and move on.