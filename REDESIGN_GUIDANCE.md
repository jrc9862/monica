# Monica UI Restyle — Design Brief

## Vibe / Overall Direction

Dark, dense, personal. Should feel like a tool built for one person — not an enterprise
product and not a toy. Stone grey base with a washed-out green-grey accent. Retro/bit
aesthetic applied selectively to headings and structural elements (borders, dividers,
decorative chrome) while the overall layout stays functional and usable daily. Think:
a well-worn terminal that got a tasteful redesign, not a retro game UI.

---

## Reference Apps

| App                    | What I like about it                                                                                 |
| ---------------------- | ---------------------------------------------------------------------------------------------------- |
| NanoCorp (nanocorp.so) | Pixel/bit aesthetic, chunky borders, hard shadows, no border-radius, structured layout               |
| shadcn/ui components   | Clean component baseline — badge, button-group, calendar, combobox, command, data-table, date-picker |

---

## Visual References

### Reference 1 — NanoCorp

- **Source:** https://www.nanocorp.so
- **What I like:** The bit/pixel UI style — chunky hard-edged borders, dithered or
  structural decorative elements, retro game menu energy. Want this applied to headings
  and design chrome, not the whole UI.

### Reference 2 — shadcn/ui Component Library

- **Source:** https://ui.shadcn.com/docs/components/radix/
- **What I like:** Clean, well-structured components. These are the baseline — badge,
  button-group, calendar, combobox, command palette, data table, date picker. All should
  be restyled with the bit aesthetic applied to borders and labels.

### Reference 3 — Current Monica UI

- **Source:** https://www.monicahq.com
- **What I like:** Nothing visually — it feels dated. Keep the spacing/density of
  contact cards as-is since the information architecture works, just restyle everything
  on top.

---

## Color Direction

- **Background:** Stone grey — dark, muted, not pure black. Think `#1c1917` / `stone-900`
- **Surface / cards:** One step lighter — `#292524` / `stone-800`
- **Text:** White / near-white — `#fafaf9` / `stone-50`
- **Accent / highlight:** Washed-out green-grey — desaturated, muted. Not a bright
  green, not a grey — the overlap between the two. **Do not hardcode a hex value yet.**
  Before proceeding with implementation, generate 4-5 candidate swatches as a visual
  preview and wait for explicit approval of one before applying it across the codebase.
- **Borders:** Hard, single-pixel, slightly lighter than surface. No gradients.
- **Shadows:** Hard offset shadows (pixel-style), no blur — e.g. `4px 4px 0px #000`
- **Vibe:** Dark. No light mode required initially.

---

## Typography

- **Headings & labels:** Silkscreen (Google Font) — bitmap/pixel style, used for
  section headers, nav labels, badges, and structural UI labels
- **Body / data:** Clean sans-serif — Inter or system-ui. Readable at small sizes for
  dense data display
- **No serif anywhere**
- **Monospace:** Use for dates, IDs, timestamps — DM Mono or similar

---

## Design System to Draw From

- [x] **shadcn/ui** — component baseline, restyled with bit aesthetic
- Tailwind CSS — stay within Monica's existing stack (Vue + Tailwind)
- No framework switch

---

## Density & Layout

- [x] **Dense** — more information on screen than NanoCorp, closer to Linear/Notion density
- Contact card spacing should match current Monica defaults — don't compress further
- Sidebar should be tight and structured

---

## Chunky / Minimal Toggle

Implement a **global UI density toggle** accessible from settings or a persistent
header control:

- **Chunky mode:** Full bit aesthetic — Silkscreen font on all headings, heavy pixel
  borders, hard shadows, structural decorative elements visible
- **Minimal mode:** Same color palette and layout, but borders thin down, shadows
  removed, headings switch to Inter. Cleaner for daily use.
- Toggle state should persist per user (localStorage or user preference in DB)

---

## Specific Component Notes

### Sidebar / Navigation

- Structured, menu-screen feel in chunky mode — pixel borders between nav sections,
  Silkscreen labels
- In minimal mode: clean vertical nav, thin borders only
- Active state: accent green-grey fill, white text
- Icons: simple, geometric — no rounded or illustrated icon sets

### Contact Cards / List View

- Follow current Monica spacing as baseline
- Restyle: stone surface, hard borders, Silkscreen for contact name label in chunky mode
- Last contacted date should be prominent — monospace font, visually distinct
- Status/relationship tags as shadcn-style badges with bit treatment (square corners,
  pixel border)

### Activity Feed / Notes

- Dense list, minimal chrome
- Timestamps in monospace, left-aligned
- Note entries should feel like log entries — structured, not card-based

### Dashboard / Home Screen

- Upcoming reminders front and center
- Data table style (shadcn data-table) for contact list — sortable columns
- Shadcn command palette (`cmd+k`) for quick contact search

### Calendar / Date Picker

- shadcn calendar component, restyled: square day cells, pixel borders in chunky mode,
  accent color for selected dates
- No rounded pill shapes anywhere

### Badges

- Square corners only — `border-radius: 0`
- Silkscreen font in chunky mode
- Variants: default (stone), accent (green-grey), warning (muted amber), destructive (muted red)

---

## Telegram Notification Format

Structured messages with inline action buttons via Telegram Bot API:

```
📇 FOLLOW-UP REMINDER
─────────────────────
Contact: Sarah Chen
Last contacted: 47 days ago
Note: "Catch up on her new role"

[✓ Mark Contacted]  [⏩ Snooze 2 weeks]  [⏩ Snooze 1 month]
```

- Bot should be configured via `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` in `.env`
- Inline keyboard buttons update the reminder state in Monica directly via callback
- Snooze options: 1 week, 2 weeks, 1 month
- "Mark Contacted" should log a basic interaction entry in Monica and clear the reminder
- **Implementation note:** The Telegram bot listener and the Monica API integration must
  be built as a single unit. The bot needs to both send outbound reminder messages AND
  receive and process the inline button callbacks — meaning it must call Monica's
  internal API to update reminder state and log interactions. Do not implement one
  without the other; a half-built integration that sends but cannot receive callbacks
  is not useful.

---

## What to Keep

- Monica's contact information architecture and field structure
- Existing spacing/density of contact cards (restyle only, don't reflow)
- All existing functionality — this is a restyle, not a rebuild

## What to Definitely Change

- Everything visual — fonts, colors, borders, shadows, button styles
- Feels dated — no soft shadows, no rounded cards, no blue/white default palette
- Replace all rounded corners with square or near-square (2px max radius)
- Remove any gradient backgrounds

---

## Constraints

- Vue.js + Tailwind CSS — no framework switch
- Must remain fully functional — restyle only
- Must degrade gracefully on mobile (PWA use on iOS/Android)
- Silkscreen font via Google Fonts CDN
- Inter via Google Fonts CDN
- Toggle state must persist across sessions
