# Handoff: Monica CRM — "Blocks" frontend overhaul

## Overview

A full-shell redesign of the Monica personal-CRM frontend: permanent left icon rail, centred
search, a block-based dashboard, and a light/dark theme built on a moss-green accent. Visual
language borrows from a TUI: square frames, monospace labels and figures, hairline rules, and
buttons that physically press.

Scope covered by the design: Dashboard, Contacts (list + label filtering), Contact detail,
Tasks, Calendar, Journals, Groups, Companies, Files, Reports, Vault settings, Vaults picker,
the Add-new menu, the Add/Edit form modal, and the success toast.

## About the design files

The files in this bundle are **design references created in HTML** — prototypes that show
intended look and behavior. They are **not production code to copy**. The task is to recreate
them inside Monica's existing environment: Laravel + Inertia + **Vue 3 SFCs** under
`resources/js/`, styled with Tailwind and the existing CSS custom properties in
`resources/css/app.css`. Reuse Monica's existing components (`Shared/Form/PrettyButton.vue`,
`Shared/Icons/*.vue`, `Layouts/Layout.vue`) rather than introducing new primitives.

`CRM Blocks.dc.html` is a single-file prototype. Its markup is a template dialect with
`{{ … }}` holes and `<sc-for>` / `<sc-if>` control flow, and its logic lives in the
`class Component` script at the bottom. Read it as a spec: the inline `style="…"` values are
exact and authoritative; the template syntax is not something to port.

## Fidelity

**High-fidelity.** Colors, type, spacing, and interactions are final. Recreate pixel-accurately
using Monica's Tailwind setup and token variables. Where a value here conflicts with a Tailwind
default, the value here wins — several are deliberately off-scale (e.g. 82px rows, 2px radii,
11px mono labels).

## Design tokens

Declared once on the root element; dark mode is the same set re-valued under
`[data-theme="dark"]`. Map these onto Monica's existing `--color-*` custom properties.

| Token                | Light                                                              | Dark                         | Used for                                            |
| -------------------- | ------------------------------------------------------------------ | ---------------------------- | --------------------------------------------------- |
| `--bg`               | `#F6FAFD`                                                          | `#131517`                    | page background                                     |
| `--bar`              | `rgba(246,250,253,.92)`                                            | `rgba(19,21,23,.94)`         | sticky header (over `backdrop-filter: blur(10px)`)  |
| `--rail`             | `rgba(238,246,251,.92)`                                            | `rgba(23,25,28,.94)`         | icon rail, dashboard right column                   |
| `--card`             | `#FFFFFF`                                                          | `#1A1D20`                    | cards, rows, inputs, chips                          |
| `--line`             | `#EAEEF4`                                                          | `#282C30`                    | all 1px borders                                     |
| `--divider`          | `#EBEBEB`                                                          | `#282C30`                    | row separators inside cards                         |
| `--ink`              | `#092C4C`                                                          | `#EDEEEF`                    | primary text                                        |
| `--ink2`             | `#526477`                                                          | `#B3B8BC`                    | secondary text                                      |
| `--muted`            | `#7E92A2`                                                          | `#868C91`                    | labels, timestamps, meta                            |
| `--faint`            | `#D6E1E6`                                                          | `#4A5055`                    | disabled glyphs, avatar fallback                    |
| `--primary`          | `#5F7A45`                                                          | `#93B172`                    | moss accent: fills, links, active states            |
| `--primary-soft`     | `#E9EFDD`                                                          | `#262B20`                    | accent tint (active label, today cell, filter chip) |
| `--on-primary`       | `#FFFFFF`                                                          | `#131517`                    | **text on a moss fill** — flips with theme          |
| `--on-primary-dim`   | `rgba(255,255,255,.74)`                                            | `rgba(19,21,23,.72)`         | secondary text on a moss fill                       |
| `--primary-strong`   | `#4A6B33`                                                          | `#4A6B33`                    | moss on a fixed white surface (hero CTA)            |
| `--stamp`            | `#092C4C`                                                          | `#4B5257`                    | offset shadow + border on pressable buttons         |
| `--logo`             | `#092C4C`                                                          | `#93B172`                    | reserved                                            |
| `--field`            | `#FFFFFF`                                                          | `#171A1C`                    | form inputs                                         |
| `--hover`            | `#F1F6FB`                                                          | `#202427`                    | row hover                                           |
| `--scrim`            | `rgba(9,44,76,.28)`                                                | `rgba(8,9,10,.62)`           | modal backdrop                                      |
| `--shadow`           | `none`                                                             | `none`                       | intentionally none — cards use borders              |
| `--pop`              | `0 12px 40px rgba(9,44,76,.14)`                                    | `0 12px 40px rgba(0,0,0,.5)` | floating layers only                                |
| `--mint` / `--green` | `#D8F3E6` / `#5C913B`                                              | `#1D2B26` / `#77B255`        | positive tint + its glyph                           |
| `--blush` / `--pink` | `#FDE1E4` / `#EA596E`                                              | `#2E2326` / `#EA596E`        | negative tint + its glyph                           |
| `--sun` / `--yellow` | `#FFF3D6` / `#F4900C`                                              | `#302B1D` / `#F4900C`        | warning tint + its glyph                            |
| `--mono`             | `"JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, monospace` | same                         | all labels, meta, figures                           |

**Critical rule:** never put `#fff` on a moss fill. `--primary` is _light_ moss in dark mode, so
white text on it fails contrast. Always use `--on-primary` / `--on-primary-dim`. Likewise the
hero CTA is a fixed `#FFFFFF` surface in both themes with `--primary-strong` text — it sits on
the moss block, so it must not follow `--card`.

### Type scale

Two families. **Inter** carries content: names 700/16px, section titles 700/18–20px, body
400/16px with 28px line-height, contact-detail name 700/30px with 38px line-height, hero name
700/18px with 26px line-height. **JetBrains Mono** carries everything machine-ish: section
headers and field labels 500/11px, `letter-spacing: .12–.16em`, `text-transform: uppercase`;
timestamps, counts, and roles 400/11–12px; button labels 500/12px uppercase with
`letter-spacing: .1em`; big figures 700/34px (hero) and 700/52px with 58px line-height
(stat tiles), `letter-spacing: -.03em`.

Minimum body size is 11px and only for mono uppercase meta. Never below.

### Geometry

Radius is `2px` almost everywhere — cards, rows, buttons, chips, avatars, inputs, icon tiles.
The only exceptions are the hero's decorative glow (`50%`) and the metric bars (`0`). Borders
are always exactly 1px. Card padding is 24px; list rows are 18px/20px; contact rows are 82px
tall with 24px gaps.

## Layout shell

```
┌──────┬──────────────────────────────────────────────────┐
│ rail │ header (90px, sticky, z-index 20)                │
│ 90px ├──────────────────────────────────────────────────┤
│      │ main                                             │
└──────┴──────────────────────────────────────────────────┘
```

App root: `display:flex; min-height:100vh; min-width:1180px`, carries `data-theme`, and
transitions `background .25s ease, color .25s ease`.

**Icon rail** — 90px wide, `position:sticky; top:0; height:100vh`, `overflow-y:auto` with
`scrollbar-width:none` so the footer controls stay reachable at any height. Top: a 90px square
button holding the Monica mark on a white 46px tile (opens the Vaults screen). Middle: nine
50×50 nav buttons, 10px gap, `border-radius:2px`; active gets `background:var(--card)` +
`border-color:var(--line)`, inactive is transparent with `color:var(--muted)`. Bottom, pushed
by `margin-top:auto`: theme toggle, settings, logout — same 50×50 treatment, logout hovers to
`--pink`.

Nav order and glyphs: Dashboard `grid`, Contacts `people`, Calendar `calendar`, Journals `note`,
Groups `group`, Companies `buildings`, Tasks `task`, Reports `chart`, Files `folder`. Contacts
and Groups **must not** share a glyph.

**Header** — 90px tall, three tracks with 24px gap and 24px side padding:

1. Left: `<h1>` in mono 700/20px prefixed by a moss `▮` (U+25AE), then the vault breadcrumb
   pill (`michael.dubois / Family & friends`, mono 12px, 32px tall, 1px border), then — on
   contact detail only — an "All contacts" back button.
2. Centre: the search field, 50px tall, 1px border, `search` glyph, mono 13px input, and a
   `/` key hint chip on the right (mono 11px, 1px border, `--bg` fill).
3. Right: the add button (50×50, moss fill, pressable) and the 50px account avatar.

Search placement is screen-dependent, and this is deliberate: when the left track is short the
outer tracks are both `flex:1` so the field lands dead centre; on contact detail — where the
back button widens the left track — the right track becomes `flex:none` and the field
right-aligns beside the add button. Values: left `flex:1;min-width:0`; search
`flex:0 1 440px;max-width:440px;min-width:220px` (380/200 on contact detail); right `flex:1`
normally, `flex:none` on contact detail. The breadcrumb's last span carries
`min-width:0;overflow:hidden;text-overflow:ellipsis` so it degrades honestly rather than
clipping.

## Screens

### Dashboard

Three columns: 230px left (Favorites, Last updated — avatar + name lists), fluid centre,
417px right rail on `--rail` with a left border.

The centre opens with the **block row** — the signature of this design:

- **Hero block**, `flex:1.15;min-width:300px`, `background:var(--primary)`, 24px padding, 28px
  gap, `position:relative;overflow:hidden`. A decorative 300×300 circle sits at `left:56px;
top:150px` with `opacity:.5` and
  `linear-gradient(180deg, rgba(255,255,255,.25) 0%, rgba(255,255,255,0) 65%)`. Content: mono
  uppercase "NEXT REMINDER" + a 10px white dot; a 52px square avatar (2px radius) with name and
  role; two figure pairs (`DATE / 17 Aug`, `HE TURNS / 68`) at mono 700/34px, 40px gap; and a
  footer row (`flex-wrap:wrap; gap:12px 16px`) with a shrinkable caption and the "SEE CONTACT"
  CTA.
- **Two stat tiles** stacked in a `flex:1;min-width:240px` column, 24px gap. Each: mono
  uppercase label, a 52px mono figure, and an 88px circle of tint (`--mint`/`--blush`) holding a
  34px glyph. Feed the Contacts figure from the same source as the contacts list — the design
  previously hardcoded a number that contradicted the list, which is a real bug to avoid.

Below: a centred segmented control (three tabs, 48px tall, 4px-padded 1px-bordered container,
active tab `--primary-soft` on `--primary`) switching between **Activity** (feed cards: header
row with avatar, name, action, right-aligned timestamp; body at 16px/28px), **Life events**
(timeline with a 12px moss dot and a 1px connector), and **Life metrics** (cards with a mono
figure and 12 flat bars at `--primary` `opacity:.75` on a hairline baseline).

Right rail: "How am I doing?" (7 tinted 52px mood tiles with mono day letters), "Reminders for
the next 30 days" and "Tasks due soon" (bordered lists, 16px/18px rows).

Every panel header is the same unit: a 15–16px glyph, a mono uppercase title, `padding-bottom:8px`,
a `--line` bottom border, `margin-bottom:12px`.

### Contacts

230px left column of labels; fluid table.

Label list: an 8px colored square, the name in mono 13px, the count in mono 11px. The active
label (or "View all" when unfiltered) gets `background:var(--primary-soft)` with `2px 6px`
padding and `-6px` side margins so the highlight bleeds to the column edge. **Counts must be
derived** from the contact data, not authored — they now label a live filter.

Header: "Contacts in this vault · N" (Inter 700/20px, count in mono `--muted`), plus a clearable
filter chip when a filter is active — mono uppercase, `--primary-soft` fill, 1px moss border,
reading `--label=close-friends` with a `close` glyph. Right side: a sort control styled as a
flag (`--sort=last-updated`, lowercase mono) and the pressable "ADD A CONTACT" button.

Table: a 56px header row on `--bg` with mono uppercase column labels (Name / Labels / Last
activity), then 82px rows — 46px square avatar, name 700/16px with mono role beneath, label
chips, mono timestamp, and a trailing `arrow-right` in `--faint`. Row hover is `--hover`; the
row navigates to contact detail.

**Label chips are filter links.** Chip: 28px tall, `12px` side padding, 1px moss border,
transparent fill, mono 11px uppercase; on hover it inverts to a moss fill with `--on-primary`.
Clicking filters the list and **must not** navigate into the contact — stop propagation. When a
filter matches nothing, the table shows a centred empty state (28px `tag` glyph + mono line).

### Contact detail

300px left column (110px avatar, name at 700/30px, pronouns/age, label chips, then Important
dates / Work / Family panels, then a column of small mono text actions ending in a `--pink`
delete). Fluid right column: a "Quick facts" card (3-column grid), an underline tab row
(Contact information / Social / Life events / Information — active gets `--ink` text and a 2px
moss bottom border), then the module stack.

Modules, in order: Notes, Calls & meetings, Reminders, Tasks, Relationships, Goals, Gifts,
Photos, Documents. **Loans and Pets are deliberately absent** — they live in Vault settings.
Each module has a header (glyph + mono uppercase title + a small "add" button) and one of three
bodies: note cards (title bar / body / footer with an emotion chip, date, author), bordered row
lists (44px square tint tile + two-line label + trailing meta + `more` glyph), or a dashed empty
state.

Contact-detail label chips filter the contacts list, same as the table chips.

### Tasks, Calendar, Reports, Settings, Vaults, card grids

- **Tasks** — max-width 1000px; three groups (Overdue in `--pink`, This week, Completed) each a
  bordered list. Checkboxes are 20px squares with a 1.5px border; done rows fill with
  `--primary`, show a `check` glyph in `--on-primary`, and strike the label.
- **Calendar** — 7-column grid, 104px min-height cells, 1px dividers, mono day numbers; events
  are small tinted chips. Today's cell is `--primary-soft` with moss text.
- **Reports** — auto-fill grid, `minmax(320px,1fr)`, 20px gap; each card lists label/value rows
  over 6px `--primary` progress bars.
- **Settings** — max-width 900px; mono uppercase group titles; rows with a label, a hint, and a
  44×24 toggle (knob 18px, `left` 3px→23px, 180ms). Three groups: vault tabs, contact-page
  modules (Loans and Pets live here, off by default), appearance.
- **Vaults** and the Journals/Groups/Companies/Files grids share one card: 52–56px tint tile,
  title, description, mono meta; hover raises `border-color` to `--primary`. The grids are
  `auto-fill, minmax(280–320px, 1fr)` with 20px gaps.

## Interactions & behavior

**The press.** Important buttons reproduce Monica's `PrettyButton`: square, 1px `--stamp`
border, `box-shadow: 4px 4px 0 var(--stamp)`, and on hover the shadow collapses to `none` while
the button takes `transform: translate(4px, 4px)` — it moves into its own shadow.
Transition: `transform .15s cubic-bezier(.4,0,.2,1), box-shadow .15s cubic-bezier(.4,0,.2,1)`.
Applied to: header add, "Add a contact", "Add a task", card-grid CTAs, the form's save button,
and the hero CTA (which stamps in `--primary-strong` instead). **In the real codebase, use
`PrettyButton.vue` directly** — it already implements exactly this.

Everything else is quiet: rows tint to `--hover`, cards raise `border-color` to `--primary`,
text buttons underline, secondary buttons shift text color. No lift, no scale.

Other behavior: rail buttons switch screens; table rows and avatar/name buttons open contact
detail; the header `+` opens a 320px add menu anchored `top:100px; right:24px` over a scrim; menu
items open the form modal (640px, `max-height:86vh`, centred, `--pop`) whose fields come from a
per-form definition with a 2-column grid and `1 / -1` spans for wide fields; saving closes the
modal and flashes a toast (top-centre, auto-dismiss at 3200ms, manual close, `crmToast` slide).

Animations: `crmFade` 250ms on screen change, `crmRise` 200–220ms on menu/modal entry,
`crmToast` 250ms. Theme change cross-fades over 250ms.

## State

| State     | Values                                                                                                       | Notes                                                                                   |
| --------- | ------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------- |
| `screen`  | dashboard, contacts, contact, calendar, journals, groups, companies, tasks, reports, files, settings, vaults | drives rail active state, header title, and search alignment                            |
| `tab`     | activity, life_events, metrics                                                                               | dashboard feed                                                                          |
| `page`    | Contact information, Social, Life events, Information                                                        | contact-detail tabs                                                                     |
| `filter`  | label name or null                                                                                           | filters the contacts list; set by any label chip, cleared by the chip's × or "View all" |
| `theme`   | light, dark                                                                                                  | persisted to `localStorage['crm-theme']`, read on mount                                 |
| `overlay` | add-menu, add-contact, add-note, add-call, add-reminder, add-task, add-journal, null                         | one overlay at a time                                                                   |
| `toast`   | message string or null                                                                                       | auto-clears after 3200ms                                                                |

Derived, not stored: the filtered contact list, all label counts, the contacts total, and the
vault meta line — all computed from the contact collection so the sidebar, heading, and rows
can never disagree.

In the real app these map to Inertia page props (contacts, labels, reminders, tasks) plus local
Vue refs for `tab`, `page`, `overlay`, `toast`, and theme. `filter` is better as a query
parameter (`?label=family`) so filtered views are linkable — the prototype keeps it in memory
only because it has no router.

## Assets

- `components/repo-icons.js` — 39 stroked 24×24 glyphs on `currentColor`, lifted from
  `monica/resources/js/Shared/Icons/*.vue`. **In the real codebase, import those Vue components
  instead of this file.** Glyphs the repo lacks (`grid`, `group`, `chart`, `folder`,
  `buildings`, `note`, `bell`, `heart`, `cake`, `gift`, `flag`, `home`, `document`, `clip`,
  `sun`, `close`, `user-check`) are drawn here in the same idiom — stroke-width 2, round caps —
  and should be added to `Shared/Icons/` as new SFCs.
- `assets/monica-mark.svg` — copied verbatim from `monica/public/img/favicon.svg`.
- `assets/user-avatar.jpg`, `avatar-3.jpg`, `face-1.jpg` — placeholder portraits. Replace with
  real avatar data; the prototype renders list avatars as CSS `background-image` rather than
  `<img>` to avoid request churn while streaming.
- Fonts: Inter (300–700) and JetBrains Mono (400/500/700) from Google Fonts.

All copy in the prototype is placeholder fiction (Clara Mendes, Rui Mendes, the Mendes family)
except the UI labels, which are final.

## Files in this bundle

- `CRM Blocks.dc.html` — the design being handed off. Open it in a browser; it runs standalone.
- `CRM.dc.html` — the earlier, rounder variant this branched from. Included for contrast only:
  same IA, indigo accent, pill buttons, circular avatars, no mono. Not the target.
- `components/repo-icons.js`, `components/Icon.js`, `components/icon-data.js` — icon runtimes.
  Only `repo-icons.js` is used by the Blocks design; the other two serve `CRM.dc.html`.
- `assets/` — the mark and placeholder portraits.
- `support.js` — the prototype runtime. Not part of the design; do not port.
