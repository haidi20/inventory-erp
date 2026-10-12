# Modal Forms & Help Sections - Style Guide

## Overview
This guide ensures all modal forms maintain consistent UX/UI with proper layout, no overlapping content, and smooth scrolling.

## Global Styles
- **File**: `resources/css/modal-forms.css`
- **Status**: ✅ Created and ready to use
- **Coverage**: All modal overlays, forms, and help sections

## Key Improvements

### 1. **No Content Cutoff**
- Modal card has `max-height: 90vh`
- Body section has `flex: 1` with `overflow-y: auto`
- Help sections have internal scrolling with `max-height: 65vh`

### 2. **Fixed Header & Footer**
- Header: `flex-shrink: 0` - always visible at top
- Footer: `flex-shrink: 0` - always visible at bottom
- Content: scrolls between them

### 3. **Help Section Layout**
```
.help-section {
    max-height: 65vh;
    overflow-y: auto;
    padding-right: 8px;  /* Scrollbar space */
}

.help-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;  /* Two columns */
    gap: 16px;  /* No overlap */
}
```

### 4. **Responsive Breakpoints**
- **≤960px**: Help grid → 1 column
- **≤640px**: Mobile optimized - full width, smaller padding

## How to Apply

### For Existing Modal Forms

#### Step 1: Import CSS
```vue
<style scoped>
@import '@/css/modal-forms.css';
</style>
```

#### Step 2: Update HTML Structure
```vue
<Teleport to="body">
    <div class="modal-overlay" @click.self="close">
        <div class="modal-card">
            <!-- Header -->
            <div class="modal-header">
                <h4>Form Title</h4>
                <button type="button" class="modal-close" @click="close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Help Section (if needed) -->
            <HelpSection :show="showHelp" @close="showHelp = false">
                <div class="help-section">
                    <div class="help-grid">
                        <div><!-- Content --></div>
                        <div><!-- Content --></div>
                    </div>
                </div>
            </HelpSection>

            <!-- Form Body -->
            <form @submit.prevent="submit">
                <div class="modal-body">
                    <!-- Form fields here -->
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" @click="close">
                        Batal (Esc)
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Simpan (Ctrl+S)
                    </button>
                </div>
            </form>
        </div>
    </div>
</Teleport>
```

#### Step 3: Update Keyboard Shortcuts Display
Use `<kbd>` tags with proper styling:
```vue
<kbd style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;">
    Ctrl + S
</kbd>
```

## Files Already Updated

### ✅ RoleForm.vue
- Help section with proper scrolling
- Keyboard shortcuts with styled kbd tags
- Two-column layout for steps + shortcuts
- No content cuts off

## Files to Update

Search for and update these files:
```bash
# Find all modal forms
find src/resources/js -name "*.vue" -exec grep -l "modal-overlay\|Teleport.*body" {} \;
```

## Testing Checklist

- [ ] Help section scrolls smoothly
- [ ] Keyboard shortcuts all visible
- [ ] No content cut off at top/bottom
- [ ] No content flipped/overlapped
- [ ] Responsive on mobile (≤640px)
- [ ] All buttons clickable
- [ ] Ctrl+S, Esc, F1 shortcuts work

## Examples

### RoleForm.vue
- Location: `features/Settings/Role/presentations/views/Partials/RoleForm.vue`
- Status: ✅ Fully implemented
- Features: Help section with 2 columns, scrollable, no overlap

## Common Issues & Solutions

### Issue: Content Cut Off at Bottom
**Solution**: 
- Check `.modal-card` has `max-height: 90vh`
- Check `.modal-body` has `overflow-y: auto`
- Ensure footer is outside scrollable area

### Issue: Help Section Overlapping
**Solution**:
- Wrap help content in div with class `help-section`
- Add `max-height: 65vh; overflow-y: auto;`
- Use `.help-grid` for multi-column layout

### Issue: Scrollbar Appearance
**Solution**: Use the provided scrollbar styling:
```css
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
```

## Best Practices

1. **Always use flexbox** for modal-card layout
2. **Fixed header/footer** with `flex-shrink: 0`
3. **Scrollable body** with `flex: 1` and `overflow-y: auto`
4. **Help sections** with internal scrolling
5. **Responsive design** for mobile (<640px)
6. **Keyboard shortcuts** with proper styling

## Quick Reference

| Element | CSS Class | Purpose |
|---------|-----------|---------|
| Overlay | `.modal-overlay` | Background + centering |
| Card | `.modal-card` | Container with max-height |
| Header | `.modal-header` | Fixed top, no shrink |
| Body | `.modal-body` | Scrollable content area |
| Footer | `.modal-footer` | Fixed bottom, no shrink |
| Help | `.help-section` | Internal scrolling |
| Grid | `.help-grid` | 2-column responsive layout |

## Next Steps

1. ✅ Global CSS created (`modal-forms.css`)
2. ✅ RoleForm.vue updated
3. ⏳ Apply to other modal forms in the project
4. ⏳ Test all modals for proper layout
5. ⏳ Deploy to production
