---
name: kui
description: "Build keyboard-first, highly informative UI for Vue 3 + TypeScript + Vite applications. Emphasizes full keyboard accessibility, informative tooltips/info modals (i icon), and complete user guidance. Apply when user requests accessible UI, keyboard shortcuts, or data-intensive forms for klinik/hospital apps."
allowed-tools: read_file, write_file, edit_file, execute_command
---

# kui - Keyboard-first, Informative UI for Web Apps

## Core Philosophy: Desktop-Grade Productivity on Web

**Goal**: Make web apps as efficient as desktop applications. User should be able to complete entire workflows (patient registration, prescription input, billing) using keyboard only, without touching mouse.

**Three Pillars**:
1. **Full Keyboard Support** – Every action has keyboard shortcut
2. **Informative UI** – Users never guess what to do next
3. **Instant Help** – Info icon (ⓘ / ℹ️) with complete documentation

---

## 📚 Pillar 1: Full Keyboard Support

### 1.1 Global Shortcuts (App-level)

| Shortcut | Action | Context |
|----------|--------|---------|
| `Ctrl+N` | Create new record | List pages (patients, invoices, etc.) |
| `Ctrl+S` | Save current form | Form pages/modals |
| `Ctrl+F` | Focus search box | List pages |
| `Delete` | Delete selected item | When item selected |
| `Escape` | Close modal / Cancel | Modal open |
| `F1` | Open help / Keyboard shortcuts guide | Anywhere |
| `Ctrl+K` | Command palette (search actions) | Anywhere |
| `Ctrl+/` | Show all keyboard shortcuts | Anywhere |
| `Alt+1..9` | Navigate to main menu items | Anywhere |

### 1.2 Form Navigation Shortcuts

| Shortcut | Action |
|----------|--------|
| `Tab` / `Shift+Tab` | Move between form fields |
| `Enter` | Submit form (when on last field or button focused) |
| `Ctrl+Enter` | Submit & stay (add another) |
| `Up/Down Arrow` | Navigate dropdown/select options |
| `Space` | Toggle checkbox/radio |
| `Ctrl+Shift+C` | Clear all form fields |

### 1.3 Table/List Shortcuts

| Shortcut | Action |
|----------|--------|
| `Up/Down Arrow` | Navigate rows |
| `Space` | Select/deselect row (for bulk actions) |
| `Ctrl+A` | Select all rows |
| `Enter` | Edit selected row |
| `Ctrl+Shift+D` | Delete selected row (with confirmation) |
| `Ctrl+E` | Export data |

### 1.4 Implementation Rules (For AI Agent)

```typescript
// MUST use: @vueuse/core for keyboard handling
import { useMagicKeys, whenever } from '@vueuse/core'

// MUST provide: Visual feedback when shortcut pressed (e.g., toast or status bar)
// MUST prevent: Default browser behavior for used shortcuts (preventDefault)
// MUST handle: Focus trapping inside modals (Tab doesn't escape to address bar)
```

---

## 💡 Pillar 2: Informative UI (No Guessing)

### 2.1 Contextual Help Everywhere

**Rule**: Every interactive element MUST have:
1. **Tooltip** on hover/focus (what this does)
2. **Keyboard shortcut** displayed (if available)
3. **Helper text** below field for complex inputs

### 2.2 Info Icon (ⓘ / ℹ️) Modal – MANDATORY

**When to use Info Icon**:
- On page header → explains page purpose, workflow, expected data
- On each form section → explains what data goes in this section
- On complex fields (e.g., BPJS number format)
- On action buttons for dangerous operations (explain consequences)

**Info Modal Content** (MUST include):

```markdown
## [Component Name] - Help

### Purpose
[1-2 sentences explaining what this section/field does]

### Steps
1. First do this...
2. Then do that...

### Required Fields
- Field A (required format: XXX-XXX)
- Field B (must be unique)

### Keyboard Shortcuts
- `Ctrl+N`: Create new
- `Ctrl+S`: Save
- `Escape`: Close

### Related Topics
- Link to patient registration
- Link to billing guide

### Still need help?
Contact admin or see documentation link
```

### 2.3 Placement Rules for Info Icon

| Location | Icon Style | Trigger | Content Focus |
|----------|-----------|---------|----------------|
| **Page header** | `ⓘ` inside circle | Click | Page overview, main workflow |
| **Form section** | `ℹ️` | Hover + Click (optional) | Section-specific instructions |
| **Field label** | `(?)` as superscript | Hover | Field format, example, constraints |
| **Action button** | `ⓘ` next to button | Click | What this action does, irreversible? |
| **Sidebar menu** | `ⓘ` on hover | Hover | Menu section explanation |

### 2.4 Visual Design Requirements

```scss
// Info icon styling
.info-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #e2e8f0;
  color: #475569;
  font-size: 11px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: #3b82f6;
    color: white;
  }
}

// Tooltip styling (for hover-only info)
.info-tooltip {
  position: absolute;
  background: #1e293b;
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
  max-width: 280px;
  z-index: 1000;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  
  &::before {
    content: '';
    position: absolute;
    top: -6px;
    left: 12px;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-bottom: 6px solid #1e293b;
  }
}
```

---

## 🧠 Pillar 3: Complete User Guidance

### 3.1 Progressive Disclosure (Don't Overwhelm)

| User Level | What They See |
|------------|---------------|
| **New user** | All help visible: helper texts, tooltips, info icons |
| **Regular user** | Can toggle "compact mode" to hide helper texts |
| **Power user** | Can disable tooltips, rely on keyboard shortcuts |

### 3.2 Keyboard Shortcuts Cheat Sheet

**MUST have**: `Ctrl+/` or `F1` to open modal showing ALL keyboard shortcuts

**Modal Content**:
- Grouped by context (Global, Forms, Tables, Modals)
- Visual key representation (actual key icons)
- Searchable

### 3.3 Status Bar (Bottom of Screen)

Show contextual information:
- Current page/context
- Active shortcuts available
- Last action result (e.g., "Patient saved")
- Keyboard shortcut hints (e.g., "Press Ctrl+S to save")

### 3.4 First-Time User Onboarding

When user first opens a complex page:
1. Show a subtle highlight on main elements
2. Display "Did you know?" tooltip with 1-2 shortcuts
3. Offer to show full tutorial (can be skipped)

---

## ✅ TDD Checklist for kui Components

Before marking any UI component complete:

### Keyboard Testing
- [ ] All interactive elements reachable via `Tab`
- [ ] Visible focus indicator on all elements
- [ ] No keyboard traps (can't tab into an element and can't tab out)
- [ ] `Enter` / `Space` activates buttons and links
- [ ] Arrow keys work on selects, dropdowns, radio groups
- [ ] Shortcuts don't conflict with browser defaults
- [ ] `Escape` closes modals, dropdowns, popups
- [ ] Focus returns to trigger element after modal closes

### Info Icon Testing
- [ ] Every page has info icon in header
- [ ] Every form section has info icon
- [ ] Every complex field has help (tooltip or icon)
- [ ] Info modal contains: purpose, steps, required fields, shortcuts
- [ ] Info modal keyboard accessible (Tab, Enter, Escape)

### Guidance Testing
- [ ] `Ctrl+/` opens shortcuts cheat sheet
- [ ] Status bar shows relevant info
- [ ] Helper texts don't overlap on mobile
- [ ] Tooltips don't overflow viewport
- [ ] All help text is clear, not technical jargon

---

## 🎨 Example: Patient Registration Form

### Required Elements

```text
┌─────────────────────────────────────────────────────────────┐
│ Register New Patient                              ⓘ        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ ┌─ Personal Information ──────────────────────────────┐    │
│ │ Name *                                    (?)        │    │
│ │ [________________________]                           │    │
│ │ Helper: Full name as on ID card                      │    │
│ │                                                      │    │
│ │ NIK (ID Number) *                           (?)      │    │
│ │ [________________________]                           │    │
│ │ Helper: 16 digits, no spaces                         │    │
│ └──────────────────────────────────────────────────────┘    │
│                                                             │
│ [Save - Ctrl+S]  [Cancel - Esc]  [Save & Add Another]     │
│                                                             │
│ Status: Ready to save                                      │
└─────────────────────────────────────────────────────────────┘
```

### Info Modal Content (When ⓘ clicked)

```markdown
## Register New Patient - Help

### Purpose
Create a new patient record in the system. Patient data is required for registration, consultation, and billing.

### Steps
1. Fill all required fields (*)
2. NIK must be valid 16-digit number
3. Click Save (Ctrl+S) or Save & Add Another

### Required Fields
- Name (min 3 characters, max 100)
- NIK (16 digits, must be unique)

### Keyboard Shortcuts
- `Ctrl+S`: Save and close
- `Ctrl+Shift+S`: Save and add another
- `Escape`: Cancel and close
- `Tab`: Move to next field

### Related Topics
- How to search existing patient
- Import patients from Excel
```

---

## 🚀 Quick Implementation Commands

For AI Agent when user requests "kui":

```bash
# 1. Install required dependencies
npm install @vueuse/core

# 2. Create keyboard shortcuts composable
# File: composables/useKeyboardShortcuts.ts

# 3. Create InfoIcon component
# File: components/common/InfoIcon.vue

# 4. Create ShortcutsHelp component (for Ctrl+/ modal)
# File: components/common/ShortcutsHelp.vue

# 5. Apply to existing components:
# - Add info icons to page headers
# - Add helper texts to forms
# - Implement keyboard shortcuts
# - Add status bar
```

---

## 📋 Trigger Phrases for AI Agent

When user says any of these, apply `kui` skill:

- "Make this form keyboard-friendly"
- "Add keyboard shortcuts"
- "Make UI informative with help icons"
- "Add (i) info modal"
- "Make this as efficient as desktop app"
- "Add tooltips and helper texts"
- "Complete user guidance"

**Example user request**: "Create patient registration form with full keyboard support and help icons"

**AI Agent response**: "I'll build this using kui skill. I'll add: (1) keyboard shortcuts (Ctrl+S, Escape), (2) info icon modal with complete instructions, (3) helper texts for each field, (4) status bar, (5) focus management."
