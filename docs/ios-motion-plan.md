# iOS-Like Motion Plan for Marketing Dashboard

## Summary

This document defines a reusable iOS-like motion system for the current marketing dashboard in `public/marketing-dashboard.html`.

Target feel:

- Smooth and subtle
- Mobile-first
- Reusable across current dashboard components
- No glow, no flashy bounce, no long-duration animation

Implementation should stay within the current stack:

- CSS transitions and keyframes
- Vue `<transition>` wrappers already used in the page
- Existing component classes and state handlers where possible

Default motion constraints:

- Short interactions: `150ms` to `220ms`
- Enter/exit overlays: `180ms` to `320ms`
- Spring-like easing: `cubic-bezier(0.22, 1, 0.36, 1)`
- Press scale: `0.97` to `0.98`
- Reduced motion support is required for all major transitions

## Motion Principles

### 1. Shared motion foundation

Add one reusable motion layer in the main style section of `public/marketing-dashboard.html`:

- Motion tokens for duration, easing, scale, opacity, and translate distance
- Utility-like classes or grouped selectors for:
  - press feedback
  - fade-up reveal
  - scale-in overlays
  - sheet enter/exit
  - popover enter/exit

Required base rules:

- `@media (prefers-reduced-motion: reduce)` must disable non-essential transform and transition effects
- Motion must not change layout measurements during resting state
- Animations should use `transform` and `opacity` first, not `width`, `left`, or expensive shadow animation unless already necessary

### 2. Behavioral consistency

- Same component family must share the same motion behavior
- Two-state segmented controls may use a sliding thumb
- Three-state segmented controls must not fake a two-position slider
- Small overlays must share one popover animation family
- Mobile sheets and desktop dialogs may differ by direction, but must feel related

## Component Inventory and Motion Mapping

### A. Navigation and shell

#### Mobile sidebar

Current surface:

- Mobile sidebar container
- Backdrop
- Sidebar menu group accordions

Motion plan:

- Sidebar enters from left with spring-like slide
- Backdrop fades in independently
- Exit reverses both cleanly
- During transition, sidebar should not jitter when nested groups are opened

Implementation notes:

- Keep current `transform-gpu` pattern
- Use shared overlay/backdrop transition timing
- Preserve existing accordion logic

#### Sidebar accordion groups

Current surface:

- `sidebar-accordion` transitions

Motion plan:

- Expand/collapse with `max-height + opacity + slight translateY`
- Keep motion short and readable
- No bounce on content panels

Implementation notes:

- Current implementation is already close; unify timing tokens with the new system

#### Topbar menu trigger

Current surface:

- Sidebar toggle/menu trigger button

Motion plan:

- Press feedback only
- If morphing states are kept, transition must be crisp and short
- No glow or exaggerated pulse

## B. Switches, tabs, and segmented controls

### Two-state segmented controls

Current surfaces:

- `Board / List`
- `Ganjil / Genap`
- `Konten / Sales`

Motion plan:

- Use iOS-like sliding thumb
- Active label color changes subtly, not dramatically
- Thumb should animate with spring-like horizontal movement
- Press state should apply to both active and inactive items

Implementation notes:

- Use one reusable class family for all two-state controls
- Equal-width layout required for balanced controls where labels should align in two columns

### Three-state segmented controls

Current surfaces:

- `Ide / In Progress / Done`

Motion plan:

- Keep active pill model
- Animate active state with background/color/shadow transition only
- Apply same press feedback as two-state controls

Implementation notes:

- Do not use a two-position slider pattern on three-state controls

## C. Buttons and action controls

### Primary, secondary, utility, and table action buttons

Current surfaces:

- `primary-cta-button`
- `secondary-cta-button`
- `icon-utility-button`
- `table-action-button`

Motion plan:

- Shared press feedback on tap/click
- Hover transitions on desktop remain subtle
- Compact icon buttons should feel identical across sections

Implementation notes:

- Keep size and layout consistent
- Avoid per-section custom animation unless needed for parity

### Mobile card action rows

Current surfaces:

- `mobile-data-card__actions` across modules

Motion plan:

- Action alignment must stay consistent by module type
- Action buttons should not drift apart because of metadata placement
- Primary quick actions and edit/delete controls should preserve visual grouping

Implementation notes:

- Keep metadata separate from action grouping where spacing becomes unstable
- Reuse the same compact action sizing patterns across cards

## D. Popovers, filters, and dropdowns

### Search select popovers

Current surfaces:

- `search-select-popover`
- Compact search select popovers
- Filter/select trigger buttons

Motion plan:

- Fade + slight scale-in from `0.98` to `1`
- Short opacity transition
- Optional chevron rotation on trigger open state

Implementation notes:

- Use the same motion for status filters, handle filters, and other dropdown triggers
- Keep transform origin visually aligned to trigger when feasible

### Date/filter popovers

Current surfaces:

- Calendar/date filter triggers
- Other toolbar filter trigger buttons

Motion plan:

- Match the same overlay family as search popovers
- No special animation variant unless interaction demands it

## E. Modal, sheet, dialog, and toast

### Mobile sheets

Current surfaces:

- `mobile-sheet` modals

Motion plan:

- Enter from bottom using sheet motion
- Backdrop fades in separately
- Exit returns downwards with matching timing

Implementation notes:

- Footer bar remains sticky, but should visually settle with the sheet
- Mobile sheet animation must not create layout shift in internal forms

### Desktop dialogs and confirm modals

Current surfaces:

- Small dialogs and centered modal variants

Motion plan:

- Fade + scale from `0.98` to `1`
- Keep duration shorter than sheet transitions

### Toast/notification

Current surfaces:

- Notification/toast UI already present in the page logic

Motion plan:

- Slide upward slightly while fading in
- Exit with reverse motion
- Auto-dismiss should respect exit transition

## F. Cards, lists, tables, and loading states

### Mobile cards and card grids

Current surfaces:

- `mobile-data-card`
- Card-based module lists

Motion plan:

- Stagger reveal only on initial render or page/tab change
- No repeated animation on every reactive update
- Card tap may slightly compress and restore

Implementation notes:

- Use small stagger delays like `20ms` to `40ms`
- Keep total perceived animation under control on long lists

### Empty states and skeletons

Current surfaces:

- Loading skeleton panels
- Empty table/card placeholders

Motion plan:

- Crossfade from skeleton to content
- Empty state appearance may use fade-up

### Tables and pagination

Current surfaces:

- Table rows
- Pagination controls

Motion plan:

- Table rows: color transition only on hover
- Pagination buttons: same press feedback as utility buttons
- Page-content swaps may use subtle content fade where feasible, but must not interfere with table layout

## Small Effort, High Impact Roadmap

### Milestone 1: Fast visual payoff

- [x] Milestone 1 complete

Priority:

- Highest

Target:

- Improve the most frequently touched components with the least code churn

Implement:

- [x] Unify press feedback for `primary-cta-button`, `secondary-cta-button`, `icon-utility-button`, and `table-action-button`
- [x] Finish iOS-style motion for all two-state segmented controls
- [x] Add one shared popover open/close animation for `search-select-popover` and filter dropdowns
- [x] Standardize mobile card action alignment in the most visible modules: Story, Order Online, Unit Ditanya, Claim Garansi, Keep Barang

Why this is high impact:

- These are touched constantly in normal usage
- Users will feel the dashboard become more polished immediately
- Changes are mostly CSS and transition-wrapper level, not state-model rewrites

Acceptance:

- Buttons feel consistent everywhere
- Two-state switches no longer feel different by module
- Dropdowns no longer appear abruptly
- Mobile card actions no longer look misaligned between modules

### Milestone 2: Navigation polish

- [x] Milestone 2 complete

Priority:

- High

Target:

- Make app-level navigation feel smoother and more intentional

Implement:

- [x] Unify mobile sidebar open/close motion
- [x] Normalize sidebar accordion timing and easing
- [x] Add subtle chevron rotation/open-state feedback on filter and select triggers

Why this is high impact:

- Navigation motion is seen on every session
- It improves perceived quality without touching business logic

Acceptance:

- Sidebar motion feels stable and smooth
- Accordion sections open consistently
- Trigger states feel alive but not noisy

### Milestone 3: Modal and sheet consistency

- [x] Milestone 3 complete

Priority:

- High

Target:

- Make all overlays feel like one system

Implement:

- [x] Shared mobile sheet animation for form modals
- [x] Shared desktop dialog scale/fade pattern
- [x] Shared backdrop timing for dialogs, modals, and sheets
- [x] Toast/notification enter-exit animation

Why this is high impact:

- Overlays are central to CRUD workflows
- Consistency here makes the app feel much more finished

Acceptance:

- Opening one modal feels similar to opening any other modal
- Backdrop and surface transitions do not compete visually
- Toasts feel integrated, not bolted on

### Milestone 4: Content reveal polish

- [ ] Milestone 4 complete

Priority:

- Medium

Target:

- Improve perceived smoothness during browsing

Implement:

- [x] Staggered reveal for `mobile-data-card` lists on initial load or page change
- [ ] Crossfade skeleton-to-content transitions
- [ ] Optional subtle content transition for pagination swaps where safe

Why this is medium priority:

- It improves perception, but is less critical than input/overlay consistency
- It should only be added after core interactions are stable

Acceptance:

- Lists enter smoothly without feeling slow
- Skeleton handoff feels cleaner
- No repeated animation spam during frequent reactive updates

### Milestone 5: Final parity sweep

- [ ] Milestone 5 complete

Priority:

- Medium

Target:

- Remove all remaining one-off motion behavior

Implement:

- [ ] Audit every `mobile-data-card__actions` row
- [ ] Audit all toolbar groups and chips sections
- [ ] Normalize durations/easing where earlier changes drifted
- [ ] Add reduced-motion coverage to any missed interaction

Why this is medium priority:

- This is cleanup and parity work
- Valuable after the user-visible wins are already shipped

Acceptance:

- No module feels “special” by accident
- No leftover abrupt transitions remain in high-traffic flows

## Implementation Phases

### Phase 1: Core interaction polish

Apply first:

- Shared motion tokens
- Two-state segmented controls
- Press feedback for main button families
- Popover/dropdown entry and exit
- Mobile sheet and dialog motion

Success criteria:

- Core controls feel consistent across sections
- No motion-related layout breakage

### Phase 2: Navigation and content reveal

Apply next:

- Sidebar motion unification
- Accordion timing cleanup
- Card/list reveal animation
- Toast animation

Success criteria:

- Navigation and overlays feel like one system
- Lists appear smoother without feeling slow

### Phase 3: Final consistency pass

Apply last:

- Audit all mobile card action rows
- Audit all toolbar action groups
- Tune durations/easing for parity across modules
- Remove any one-off animation leftovers

Success criteria:

- No single module feels visually different from the others without intention

## Public Interfaces and Constraints

- No backend or API changes
- No Vue data shape changes
- Existing state handlers should remain intact:
  - tab switching
  - modal open/close
  - popover open/close
  - sidebar toggling
- Existing class names should be extended, not broadly renamed, unless a rename is required for consistency

## Acceptance Criteria

### Visual

- Two-state switches feel the same across all current modules
- Popovers and filters open with one consistent animation family
- Sheets and dialogs feel related, not random
- Button press feedback is consistent across primary, secondary, compact, and utility buttons
- Mobile cards do not have awkward action spacing

### Interaction

- No animation blocks the main interaction
- No stuck states during rapid repeated taps
- Sidebar, popovers, and modals do not visually fight each other

### Performance

- Motion uses `transform` and `opacity` whenever possible
- No visible jank on mobile for list/card entry
- No measurable slowdown from repeated transitions in common flows

### Accessibility

- `prefers-reduced-motion: reduce` removes non-essential motion
- Focus-visible styles remain clear
- Motion does not hide important state changes

## Verification Checklist

- Open/close sidebar on mobile
- Expand/collapse each sidebar group
- Toggle all current segmented controls
- Open and close each major modal:
  - Story
  - Order Online
  - Unit Ditanya
  - Claim Garansi
  - Keep Barang
- Open multiple filters and search-select popovers
- Navigate card-heavy mobile sections and confirm action row consistency
- Run `npm run build`

## Assumptions

- Scope is reusable core for the current dashboard, not a one-off animation patch
- Styling remains inside the current page-level CSS unless later refactoring extracts it
- No animation library will be introduced
- iOS-like means interaction feel and restraint, not exact Apple visual cloning
