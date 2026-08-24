---
name: Ibadurrahman Institutional System
colors:
  surface: '#f4faff'
  surface-dim: '#c0dfee'
  surface-bright: '#f4faff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#e6f6ff'
  surface-container: '#d9f2ff'
  surface-container-high: '#ceedfd'
  surface-container-highest: '#c9e7f7'
  on-surface: '#001f2a'
  on-surface-variant: '#40493d'
  inverse-surface: '#163440'
  inverse-on-surface: '#e0f4ff'
  outline: '#707a6c'
  outline-variant: '#bfcaba'
  surface-tint: '#1b6d24'
  primary: '#0d631b'
  on-primary: '#ffffff'
  primary-container: '#2e7d32'
  on-primary-container: '#cbffc2'
  inverse-primary: '#88d982'
  secondary: '#006e1c'
  on-secondary: '#ffffff'
  secondary-container: '#98f994'
  on-secondary-container: '#0c7521'
  tertiary: '#00631e'
  on-tertiary: '#ffffff'
  tertiary-container: '#277d34'
  on-tertiary-container: '#c7ffc3'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#a3f69c'
  primary-fixed-dim: '#88d982'
  on-primary-fixed: '#002204'
  on-primary-fixed-variant: '#005312'
  secondary-fixed: '#98f994'
  secondary-fixed-dim: '#7ddc7a'
  on-secondary-fixed: '#002204'
  on-secondary-fixed-variant: '#005313'
  tertiary-fixed: '#9ff79f'
  tertiary-fixed-dim: '#83da85'
  on-tertiary-fixed: '#002105'
  on-tertiary-fixed-variant: '#005318'
  background: '#f4faff'
  on-background: '#001f2a'
  surface-variant: '#c9e7f7'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 60px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 4px
  container-padding: 24px
  gutter: 20px
  card-gap: 24px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 32px
---

## Brand & Style
The design system is a premium, modern SaaS-inspired framework tailored for institutional efficiency. It blends the professional reliability of an educational entity with the sleek, high-performance aesthetics of tools like Linear and Google Workspace.

The visual direction is **Corporate / Modern**, characterized by exceptional clarity, generous whitespace, and a sophisticated green-centric palette that symbolizes growth, discipline, and stability. The interface evokes a sense of calm authority, ensuring that administrative tasks feel effortless rather than burdensome. Emphasis is placed on structural hierarchy, crisp borders, and a refined "tactile-digital" feel.

## Colors
This design system utilizes a monolithic green scale to represent the PKBM identity, supported by a sophisticated slate-grey for information architecture.

- **Primary & Deep Accents:** Use `#2E7D32` for primary actions and `#1B5E20` for deep hover states or high-contrast headers.
- **Surface Strategy:** The primary background is a soft mint-grey (`#F7FAF7`). Use the Card Surface (`#FFFFFF`) to create distinct content areas.
- **Semantic Feedback:** Success, Warning, and Danger colors are calibrated to maintain high legibility against the light green background tints.
- **Accents:** Use `#E8F5E9` for subtle row highlights, hover backgrounds, and soft badge containers.

## Typography
The system uses **Plus Jakarta Sans** (an optimized modern alternative to Poppins) to achieve a contemporary, approachable, yet professional SaaS feel.

- **Scale:** Use tight line-heights for headlines to maintain a "dense" professional look, while keeping body text airy (1.5x) for readability in data-heavy views.
- **Hierarchy:** Reserve Bold (700) weights for page titles and Section headers. Semi-bold (600) is used for interactive labels, buttons, and sub-headings.
- **Letter Spacing:** Apply slight negative tracking to large display text for a more "designed" appearance.

## Layout & Spacing
The design system employs a **Fluid Grid** with fixed maximum widths for desktop content to maintain legibility.

- **Grid:** Use a 12-column grid for desktop views with 20px gutters. 
- **Margins:** Standard page margins are 24px on mobile and 40px on desktop.
- **Rhythm:** An 8px base unit drives all spacing. Elements are grouped using 8px (related), 16px (standard), or 32px (sections) vertical stacks.
- **Sidebar:** A fixed-width left navigation (280px) is utilized for desktop, collapsing into a bottom-bar or drawer for mobile attendance workflows.

## Elevation & Depth
This system uses **Tonal Layers** combined with **Ambient Shadows** to create a structured hierarchy.

- **Level 0 (Background):** `#F7FAF7` - The canvas.
- **Level 1 (Cards/Sidebar):** White surface with a `1px` solid border in `#E6ECE7`. No shadow, or a extremely faint 2px blur.
- **Level 2 (Interactive/Floating):** White surface with a soft shadow: `0px 4px 20px rgba(38, 50, 56, 0.06)`. Used for dropdowns and active cards.
- **Level 3 (Modals):** High-diffusion shadow: `0px 12px 40px rgba(38, 50, 56, 0.12)`.
- **Focus States:** A 3px outer glow using the primary color at 20% opacity.

## Shapes
The shape language is friendly yet structured, using significantly rounded corners to differentiate from traditional legacy institutional software.

- **Containers & Cards:** Use a 16px (`rounded-xl`) radius to create a soft, modern container feel.
- **Buttons & Inputs:** Use a 12px (`rounded-lg`) radius. This provides a clear distinction between structural elements and interactive elements.
- **Badges/Chips:** Use full pill-shaped rounding (999px) for status indicators.
- **Icon Containers:** Use 8px radius for consistency within the sidebar and data tables.

## Components

### Buttons
- **Primary:** Solid `#2E7D32` background with white text. 12px radius. 
- **Secondary:** `#E8F5E9` background with `#2E7D32` text. Used for less prominent actions.
- **Ghost:** No background, `#607D8B` text. Changes to a light grey tint on hover.

### Sidebars
- **Active State:** The active link should feature a vertical 4px bar on the left and a background tint of `#E8F5E9`.
- **Icons:** Heroicons (Outline) at 20px size, using `#607D8B` for inactive and `#2E7D32` for active states.

### Cards
- Always use white backgrounds with a `#E6ECE7` border. 
- Padding should be 24px consistently.
- Use "Header" sections within cards separated by a light border for complex data views.

### Data Tables
- **Header:** Light grey (`#F7FAF7`) or white with a bold bottom border.
- **Rows:** Alternating subtle tints are discouraged; use clean white rows with `1px` bottom borders.
- **Badges:** Success (Present), Warning (Late), Danger (Absent) using low-opacity background tints with high-contrast text.

### Form Elements
- **Inputs:** 12px radius, `#E6ECE7` border. On focus, border transitions to `#2E7D32`.
- **Labels:** Use `label-md` typography, placed 8px above the input field.