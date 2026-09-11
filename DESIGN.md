# UTE Parts POS - Design System

> Modern minimalist design system for UTE Parts POS. Clean, professional, focused on usability for retail/wholesale operations.

## Color System

### Primary Palette

| Token | Hex | Usage |
|-------|-----|-------|
| `primary-50` | `#f0f4ff` | Hover backgrounds, light accents |
| `primary-100` | `#e0eaff` | Selected states, badges |
| `primary-200` | `#c7d7fe` | Borders, dividers |
| `primary-300` | `#a4bcfd` | Disabled text |
| `primary-400` | `#7c9afb` | Secondary buttons |
| `primary-500` | `#5c73f8` | Primary actions, links |
| `primary-600` | `#4f55eb` | Primary button hover |
| `primary-700` | `#4040d3` | Primary button active |
| `primary-800` | `#3535ab` | Dark text on primary |
| `primary-900` | `#2f3187` | Headings |

### Neutral Palette (Surface)

| Token | Hex | Usage |
|-------|-----|-------|
| `surface-0` | `#ffffff` | Card backgrounds, inputs |
| `surface-50` | `#f8f9fc` | Page background |
| `surface-100` | `#f1f3f8` | Subtle backgrounds, hover |
| `surface-200` | `#e5e8f0` | Borders, dividers |
| `surface-300` | `#d2d6e2` | Disabled elements |
| `surface-400` | `#9ba3b5` | Placeholder text |
| `surface-500` | `#6b7385` | Secondary text |
| `surface-600` | `#4b5569` | Body text |
| `surface-700` | `#374151` | Headings |
| `surface-800` | `#1f2937` | Primary text |
| `surface-900` | `#111827` | Black text |

### Status Colors

| Status | Hex | Usage |
|--------|-----|-------|
| `success` | `#10b981` | Success messages, completed |
| `warning` | `#f59e0b` | Warnings, pending states |
| `danger` | `#ef4444` | Errors, destructive actions |
| `info` | `#3b82f6` | Information, links |

---

## Typography

### Font Family

```css
font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
```

### Type Scale

| Token | Size | Line Height | Usage |
|-------|------|-------------|-------|
| `text-xs` | `0.75rem` (12px) | `1rem` | Captions, labels |
| `text-sm` | `0.8125rem` (13px) | `1.125rem` | Body text, table cells |
| `text-base` | `0.875rem` (14px) | `1.25rem` | Default body |
| `text-lg` | `1rem` (16px) | `1.5rem` | Large body, emphasis |
| `text-xl` | `1.125rem` (18px) | `1.75rem` | Section headings |
| `text-2xl` | `1.25rem` (20px) | `1.75rem` | Page titles |
| `text-3xl` | `1.5rem` (24px) | `2rem` | Dashboard numbers |
| `text-4xl` | `1.875rem` (30px) | `2.25rem` | Hero numbers |

### Font Weights

| Weight | Usage |
|--------|-------|
| `font-normal` (400) | Body text |
| `font-medium` (500) | Labels, emphasis |
| `font-semibold` (600) | Headings, buttons |
| `font-bold` (700) | Page titles |

---

## Spacing System

Based on 4px grid:

| Token | Value | Usage |
|-------|-------|-------|
| `space-1` | `0.25rem` (4px) | Tight spacing |
| `space-2` | `0.5rem` (8px) | Small gaps |
| `space-3` | `0.75rem` (12px) | Medium gaps |
| `space-4` | `1rem` (16px) | Standard spacing |
| `space-5` | `1.25rem` (20px) | Section spacing |
| `space-6` | `1.5rem` (24px) | Card padding |
| `space-8` | `2rem` (32px) | Large spacing |

### Layout Spacing

| Element | Spacing |
|---------|---------|
| Card padding | `p-6` (24px) |
| Card gap | `gap-4` (16px) |
| Section margin | `mb-6` (24px) |
| Page padding | `p-4` to `p-6` |
| Table cell padding | `px-4 py-3` |

---

## Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `rounded-sm` | `0.375rem` (6px) | Badges, small elements |
| `rounded-md` | `0.5rem` (8px) | Inputs, buttons |
| `rounded-lg` | `0.625rem` (10px) | Cards, modals |
| `rounded-xl` | `0.75rem` (12px) | Featured cards |
| `rounded-full` | `9999px` | Avatars, pills |

**Default radius**: `rounded-lg` (10px) for cards and containers.

---

## Shadows

| Token | Value | Usage |
|-------|-------|-------|
| `shadow-xs` | `0 1px 2px rgba(0,0,0,0.03)` | Subtle elevation |
| `shadow-sm` | `0 1px 3px rgba(0,0,0,0.04)` | Light cards |
| `shadow-base` | `0 1px 3px rgba(0,0,0,0.05)` | Standard cards |
| `shadow-md` | `0 4px 6px rgba(0,0,0,0.05)` | Dropdowns, modals |
| `shadow-lg` | `0 10px 15px rgba(0,0,0,0.06)` | Elevated elements |

**Minimal shadows**: Use `shadow-sm` or `shadow-md` only.

---

## Component Patterns

### Cards

```html
<div class="bg-white rounded-lg shadow-sm border border-surface-200 p-6">
  <!-- Card content -->
</div>
```

### Buttons

```html
<!-- Primary -->
<button class="bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-md px-4 py-2 transition-colors">
  Save changes
</button>

<!-- Secondary -->
<button class="bg-surface-100 hover:bg-surface-200 text-surface-700 font-medium rounded-md px-4 py-2 transition-colors">
  Cancel
</button>

<!-- Danger -->
<button class="bg-danger-500 hover:bg-danger-600 text-white font-medium rounded-md px-4 py-2 transition-colors">
  Delete
</button>
```

### Inputs

```html
<input type="text" 
       class="w-full bg-white border border-surface-200 rounded-md px-3 py-2 text-surface-800 placeholder:text-surface-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
```

### Tables

```html
<table class="w-full text-sm">
  <thead class="bg-surface-50 border-b border-surface-200">
    <tr>
      <th class="text-left px-4 py-3 font-medium text-surface-600">Header</th>
    </tr>
  </thead>
  <tbody class="divide-y divide-surface-200">
    <tr class="hover:bg-surface-50 transition-colors">
      <td class="px-4 py-3 text-surface-800">Cell</td>
    </tr>
  </tbody>
</table>
```

### Modals

```html
<div class="fixed inset-0 z-50 flex items-center justify-center">
  <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
  <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
    <h3 class="text-lg font-semibold text-surface-800 mb-4">Title</h3>
    <p class="text-surface-600 mb-6">Content</p>
    <div class="flex justify-end gap-3">
      <button class="...">Cancel</button>
      <button class="...">Confirm</button>
    </div>
  </div>
</div>
```

### Badges

```html
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700">
  Completed
</span>
```

### Form Groups

```html
<div class="space-y-1">
  <label class="block text-sm font-medium text-surface-700">Label</label>
  <input type="text" class="...">
  <p class="text-xs text-surface-500">Helper text</p>
</div>
```

---

## Animations

Keep minimal. Only animate:
- **Hover states**: `transition-colors duration-150`
- **Focus rings**: `transition-shadow duration-150`
- **Modals**: `transition-opacity duration-200`
- **Toasts**: `transition-all duration-300`

**No decorative animations**: No fade-in on page load, no hover scale.

---

## Livewire/Alpine Patterns

### Modal with Alpine

```html
<div x-data="{ open: false }">
  <button @click="open = true">Open</button>
  
  <div x-show="open" x-transition.opacity class="fixed inset-0 z-50">
    <!-- Modal content -->
  </div>
</div>
```

### Tab Navigation

```html
<div x-data="{ tab: 'overview' }">
  <nav class="flex gap-4 border-b border-surface-200 mb-6">
    <button @click="tab = 'overview'" 
            :class="tab === 'overview' ? 'border-primary-500 text-primary-600' : 'border-transparent text-surface-500'"
            class="pb-3 border-b-2 font-medium transition-colors">
      Overview
    </button>
  </nav>
  
  <div x-show="tab === 'overview'">Content</div>
</div>
```

### Dropdown Menu

```html
<div x-data="{ open: false }" class="relative">
  <button @click="open = !open">Menu</button>
  <div x-show="open" @click.outside="open = false" 
       class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-surface-200 py-1 z-50">
    <a href="#" class="block px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">Action</a>
  </div>
</div>
```

---

## Page Templates

### Standard Page Layout

```html
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">Page Title</h4>
      <div class="text-muted">Page description</div>
    </div>
    <button class="bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-md px-4 py-2">
      Action
    </button>
  </div>

  <!-- Content -->
  <div class="card">
    <div class="card-body">
      <!-- Table or form here -->
    </div>
  </div>
</div>
```

### Stats Card Grid

```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-lg shadow-sm border border-surface-200 p-4">
    <div class="text-sm text-surface-500">Label</div>
    <div class="text-2xl font-semibold text-surface-800 mt-1">123</div>
  </div>
</div>
```

### Data Table Pattern

```html
<div class="bg-white rounded-lg shadow-sm border border-surface-200">
  <div class="p-4 border-b border-surface-200">
    <div class="flex items-center justify-between">
      <h5 class="font-medium">Table Title</h5>
      <input type="text" placeholder="Search..." class="...">
    </div>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">...</table>
  </div>
  <div class="p-4 border-t border-surface-200">
    <!-- Pagination -->
  </div>
</div>
```

---

## Quick Reference Classes

### Layout

| Class | Purpose |
|-------|---------|
| `bg-surface-50` | Page background |
| `bg-white` | Card/input background |
| `rounded-lg` | Default border radius |
| `shadow-sm` | Subtle elevation |
| `border border-surface-200` | Subtle border |

### Text

| Class | Purpose |
|-------|---------|
| `text-surface-800` | Primary text |
| `text-surface-600` | Secondary text |
| `text-surface-400` | Placeholder text |
| `text-primary-500` | Links/interactive |

### Interactive

| Class | Purpose |
|-------|---------|
| `hover:bg-surface-50` | Row hover |
| `focus:ring-2 focus:ring-primary-500` | Focus state |
| `transition-colors duration-150` | Smooth transitions |

---

## Anti-Patterns to Avoid

1. **No heavy shadows** - Use `shadow-sm` or `shadow-md` only
2. **No gradients on buttons** - Solid colors only
3. **No ALL CAPS labels** - Use sentence case
4. **No decorative icons** - Icons only for functional meaning
5. **No skeleton loading** - Use simple spinners if needed
6. **No animation on page load** - Only user-triggered animations
7. **No rounded-full on cards** - Use `rounded-lg`
8. **No glassmorphism** - Keep it flat and clean
9. **No multi-color gradients** - Single primary color only
10. **No heavy borders** - Use subtle `border-surface-200`

---

## Consistency Checklist

Before implementing any UI:

- [ ] Colors match the palette (primary-500 for actions)
- [ ] Border radius is `rounded-lg` (10px) for cards
- [ ] Shadows are minimal (`shadow-sm` or `shadow-md`)
- [ ] Text uses sentence case (not ALL CAPS)
- [ ] Buttons use action verbs ("Save", "Delete")
- [ ] Empty states explain what to do
- [ ] Error messages are helpful
- [ ] Spacing follows the 4px grid
- [ ] No decorative animations
- [ ] Dark mode classes included where needed
