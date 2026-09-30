---
name: Campus Courier
colors:
  surface: '#f8f9ff'
  surface-dim: '#d0dbed'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e6eeff'
  surface-container-high: '#dee9fc'
  surface-container-highest: '#d9e3f6'
  on-surface: '#121c2a'
  on-surface-variant: '#3f4943'
  inverse-surface: '#27313f'
  inverse-on-surface: '#eaf1ff'
  outline: '#6f7a72'
  outline-variant: '#bec9c1'
  surface-tint: '#176b4b'
  primary: '#00462e'
  on-primary: '#ffffff'
  primary-container: '#006041'
  on-primary-container: '#8ad8b1'
  inverse-primary: '#88d6af'
  secondary: '#246b3b'
  on-secondary: '#ffffff'
  secondary-container: '#a9f4b6'
  on-secondary-container: '#2b7141'
  tertiary: '#004815'
  on-tertiary: '#ffffff'
  tertiary-container: '#00621f'
  on-tertiary-container: '#47e465'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#a4f3ca'
  primary-fixed-dim: '#88d6af'
  on-primary-fixed: '#002114'
  on-primary-fixed-variant: '#005237'
  secondary-fixed: '#a9f4b6'
  secondary-fixed-dim: '#8ed79c'
  on-secondary-fixed: '#00210b'
  on-secondary-fixed-variant: '#005226'
  tertiary-fixed: '#6dff80'
  tertiary-fixed-dim: '#44e263'
  on-tertiary-fixed: '#002106'
  on-tertiary-fixed-variant: '#005319'
  background: '#f8f9ff'
  on-background: '#121c2a'
  surface-variant: '#d9e3f6'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: 56px
    letterSpacing: -0.03em
  display-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '800'
    lineHeight: 44px
    letterSpacing: -0.025em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 34px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  title-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.005em
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
    letterSpacing: 0em
  body-md:
    fontFamily: Inter
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: 0em
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: 0.005em
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
    letterSpacing: 0.03em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  space-xxs: 0.25rem
  space-xs: 0.5rem
  space-sm: 0.75rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 3rem
  space-3xl: 4rem
  container-padding-mobile: 1rem
  container-padding-desktop: 2rem
  gutter: 1rem
---

## Brand & Style

This design system embodies a modern, campus-oriented micro-logistics platform built to bridge trust, speed, and peer reliability among university students. The aesthetic merges contemporary minimalism with approachable warmth—stripping away institutional friction while retaining the operational clarity required for financial micro-transactions and parcel custody.

### Aesthetic Principles
- **Collegiate Utility:** Clean layouts prioritize scan-and-go actions (requesting deliveries, accepting routes, verifying drops). Interface clutter is eliminated in favor of generous white space and rhythmic structural lines.
- **Vibrant Pragmatism:** Grounded in deep architectural forest greens that convey stability and security, punctuated by high-visibility mint micro-accents tailored for fast-moving Gen-Z mobile environments.
- **Tactile Softness:** Generously rounded corners and subtle ambient depth prevent the utility-driven interface from feeling transactional or sterile, reinforcing a friendly peer-to-peer communal spirit.

## Colors

The palette establishes an authoritative, reliable base through deep forest greens while retaining an energetic, youthful pulse using bright mint highlights.

### Palette Architecture
- **Primary (`#006041`):** Deep Forest Green. Anchors top-level brand expressions, primary navigation active states, high-priority interactive buttons, and primary headline text in hero areas.
- **Secondary (`#357B49`):** Medium Forest Green. Supports secondary actions, filter toggles, route indicators, and active category pills.
- **Tertiary / Accent (`#5BF674`):** Screamin' Green. Reserved exclusively for functional micro-highlights: live driver status dots, verification badges, payout confirmation tags, and subtle progress bars. It must never be paired with low-contrast white text.
- **Neutral Base (`#1F2937`):** Slate Charcoal. Provides maximum contrast and readability for body text, primary metadata, and structural icons without the harshness of pure black.
- **Neutral Muted (`#6B7280`):** Secondary metadata, inactive states, helper text, and secondary icon fills.
- **Borders & Rules (`#E5E7EB`):** Subtle dividers and card outlines providing low-noise structure.
- **Backgrounds:** `#FFFFFF` serves as the primary card and interactive surface backdrop; `#F9F9F7` (Warm Off-White) establishes soft page canvas grounding and visual separation across alternating scrollable sections.

## Typography

The pairing combines **Plus Jakarta Sans** for expressive, confident headers with **Inter** for dense transactional delivery data, order manifests, and micro-copy.

### Usage Guidelines
- **Headlines & Titles:** Plus Jakarta Sans delivers geometric, contemporary charm. Tight tracking (down to `-0.03em`) on larger scale sizes gives headlines visual cohesion and presence on campus landing screens.
- **Body & Data Displays:** Inter is applied across all body copy, forms, and item listings. Its neutral proportion guarantees illegibility drops to zero during rushed on-the-go scanning across outdoor sunlight.
- **Monetary & Timing Metrics:** Digits displaying delivery fee estimates or transit minutes should always adopt `font-variant-numeric: tabular-nums` to maintain horizontal consistency across updating order states.

## Layout & Spacing

A disciplined 8pt-based spacing system governs spatial relationships across mobile views and wide desktop dispatch screens.

### Grid & Layout Rules
- **Mobile First Focus (360px - 480px):** Single-column stack with continuous scroll, anchored by bottom navigation or a sticky bottom CTA deck. Left/right safe padding is locked at `16px` (`space-md`).
- **Tablet (481px - 1024px):** 6-column fluid grid with `20px` gutters and `24px` page margins. Used primarily for dual-pane courier inspection views (open errands list on the left, map route on the right).
- **Desktop (1025px+):** Max layout container bounded at `1200px` within a 12-column layout, centered with generous whitespace to prevent interface stretching.
- **Vertical Rhythm:** Section headers are spaced from preceding content by `space-2xl` (`48px`) on desktop and `space-xl` (`32px`) on mobile. Content blocks within cards observe a strict `16px` inner inset padding.

## Elevation & Depth

Visual hierarchy uses a layered, ambient shadow paradigm coupled with low-contrast structural borders to maintain clarity in high-glare environments.

### Depth Hierarchy
- **Level 0 (Canvas):** The ground layer (`#F9F9F7` or `#FFFFFF`). Flat, no shadow.
- **Level 1 (Cards & Static Containers):** `#FFFFFF` surfaces bounded by a solid `1px` border in `#E5E7EB`, paired with an ultra-soft ambient shadow: `0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02)`.
- **Level 2 (Interactive Floating & Active Cards):** Used for hover states, selected errand tickets, and drop-down menus: `0 4px 12px -2px rgba(0, 96, 65, 0.06), 0 2px 6px -1px rgba(0, 0, 0, 0.04)`.
- **Level 3 (Modals, Overlays & Sticky Sheets):** Floating bottom sheets and order detail modals: `0 12px 32px -4px rgba(31, 41, 55, 0.12), 0 4px 12px -2px rgba(0, 0, 0, 0.04)`.
- **Tinting Technique:** Elevation shadows on primary interactive elements are tinted with a fraction of `#006041` rather than dead charcoal, delivering an organic, unified environmental illumination.

## Shapes

The interface embraces balanced, comfortable curves that soften operational logistics into approachable interactions.

### Shape Tiers
- **Large Containers & Modals (`20px - 24px`):** Top sheet curves, modal dialog boxes, and campus hub hero banners.
- **Cards & Feed Containers (`16px`):** Standard delivery request cards, profile summaries, and order details.
- **Buttons, Form Inputs & Floating Controls (`10px - 12px`):** Ergonomic touch points, search fields, text boxes, and primary CTA bars.
- **Micro Tags & Status Badges (`9999px`):** Pill-shaped indicators for delivery status, price labels, and building tags.

## Components

### Buttons
- **Primary:** Background `#006041`, text `#FFFFFF`, height `48px` (mobile touch-ready), border-radius `12px`. Hover shifts to `#004d34`. Press scale `0.98`. Focus outline: 2px solid `#5BF674` with 2px offset.
- **Secondary:** Background transparent, text `#006041`, border `1.5px solid #006041`, height `48px`, border-radius `12px`. Hover triggers light tint `rgba(0, 96, 65, 0.05)`.
- **Ghost/Tertiary:** Text `#357B49`, transparent background, hover background `#F9F9F7`.

### Chips & Badges
- **Status Pills:** Height `26px`, padding `4px 10px`, border-radius `9999px`.
  - *Active/Available:* Background `rgba(91, 246, 116, 0.15)`, text `#006041`, with a leading 6px pulsing `#5BF674` circle.
  - *Pending/In-Transit:* Background `#FEF3C7`, text `#92400E`.
- **Category Filter Chips:** Surface `#FFFFFF`, border `1px solid #E5E7EB`, text `#1F2937`, border-radius `9999px`. Selected state switches to background `#006041`, border `#006041`, text `#FFFFFF`.

### Cards
- **Errand / Listing Card:** White `#FFFFFF` background, `16px` border-radius, `1px solid #E5E7EB`, padding `16px`. Organizes three vertical zones:
  1. *Header:* Requester mini-avatar, campus building drop zone pill, time-decay tracker (`body-sm`, `#6B7280`).
  2. *Body:* Delivery item description (`headline-sm`, `#1F2937`), reward payout amount (`title-md`, bold `#006041`).
  3. *Footer:* Single action button or route distance estimation.

### Input Fields
- **Text & Search Fields:** Height `48px`, background `#FFFFFF`, border `1px solid #E5E7EB`, radius `10px`, padding `0 16px`, text `#1F2937`, placeholder `#9CA3AF`.
- **Active / Focus:** Border switches to `2px solid #006041`, inner padding compensates to prevent visual jump.

### Checkboxes & Radio Buttons
- **Checkboxes:** `20px x 20px` square, `6px` border radius. Unselected: `1.5px solid #D1D5DB`. Selected: background `#006041`, border `#006041`, with sharp white check glyph.
- **Radio Options:** `20px` circle with matching color logic; selected state presents a concentrated `#006041` center disc encircled by a 3px white halo.

### Additional Campus Logistics Components
- **Campus Route Stepper:** Horizontal micro-progress indicator tracing pickup location (dorm/store) to drop-off locker/room, connected by a `2px` dotted `#E5E7EB` path line transitioning to solid `#357B49` upon stage completion.
- **Peer Verification Pin Block:** 4-digit isolated input slots (`52px x 56px`), rounded `12px`, with heavy bold typography for quick pass-off handshakes between students.