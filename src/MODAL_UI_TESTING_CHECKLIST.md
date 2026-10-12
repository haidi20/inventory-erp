# Modal UI Testing Checklist
## Ensure No Content Cutoff: Top, Bottom, Left, Right

### Purpose
Verify all modal forms in the application display properly on all screen sizes without content being cut off at any edges.

---

## 🖥️ Desktop Testing (1920x1080 and above)

### Modal Visibility
- [ ] Modal dialog appears centered on screen
- [ ] Modal header is fully visible at top
- [ ] Modal footer is fully visible at bottom
- [ ] Modal doesn't exceed viewport width
- [ ] Modal doesn't exceed viewport height
- [ ] 20px+ padding from screen edges

### Content Area
- [ ] All form fields visible without horizontal scroll
- [ ] Help section displays with proper scrollbar
- [ ] Keyboard shortcuts section fully visible
- [ ] Form labels not cut off on left
- [ ] Form inputs not cut off on right
- [ ] Modal shadow doesn't overlap content

### Header Elements
- [ ] Title text fully visible
- [ ] Icon (if any) not clipped
- [ ] Close button accessible and visible
- [ ] Info icon (?) visible and clickable

### Footer Elements
- [ ] Submit button fully visible
- [ ] Cancel button fully visible
- [ ] Button text not truncated
- [ ] Footer doesn't overlap body content
- [ ] Button spacing adequate

---

## 📱 Tablet Testing (768x1024)

### Layout Changes
- [ ] Modal grid changes to 1 column (responsive)
- [ ] Help section displays as single column
- [ ] No horizontal overflow
- [ ] Padding adjusted for tablet space

### Content Visibility
- [ ] All form fields fit within modal
- [ ] Help text readable at tablet size
- [ ] Search input properly sized
- [ ] Permission items visible without scroll
- [ ] No text cuts off at edges

### Scrolling
- [ ] Help section scrolls smoothly
- [ ] Body content scrolls if needed
- [ ] Scrollbar visible and functional
- [ ] Scroll doesn't hide headers/footers

---

## 📱 Mobile Testing (375x667 and smaller)

### Viewport Handling
- [ ] Modal takes 98% of screen width
- [ ] 10px+ padding on all sides
- [ ] Modal height respects safe area
- [ ] No horizontal scroll needed
- [ ] No vertical overflow issues

### Orientation Testing

#### Portrait (375x667)
- [ ] Modal readable in portrait mode
- [ ] Content not rotated/flipped
- [ ] Header at top (not upside down)
- [ ] Footer at bottom (not upside down)
- [ ] All buttons tappable (44px+ height)

#### Landscape (667x375)
- [ ] Modal adjusts to landscape height
- [ ] Scrolling works properly
- [ ] No content cut at top/bottom
- [ ] Keyboard doesn't hide content
- [ ] Input fields remain accessible

### Content Truncation
- [ ] No text cut off on left edge
- [ ] No text cut off on right edge
- [ ] Form labels fully visible
- [ ] Button text visible (no "..." truncation)
- [ ] Placeholder text visible

---

## 🔄 Edge Cases

### Browser Window Resizing
- [ ] Resize from 1920px to 320px smoothly
- [ ] Modal adapts at each breakpoint
- [ ] No content jumps or overlaps
- [ ] Scrollbars appear/disappear correctly
- [ ] No flashing or layout shift

### Zoom Levels
- [ ] 75% zoom: All content visible
- [ ] 100% zoom (default): Proper layout
- [ ] 125% zoom: Text readable, no cutoff
- [ ] 150% zoom: Modal still usable
- [ ] 200% zoom: Vertical scroll only

### Different Browsers
- [ ] Chrome/Edge: Proper rendering
- [ ] Firefox: No layout issues
- [ ] Safari: iOS-safe area respected
- [ ] Mobile browsers: Touch-friendly
- [ ] Tablet browsers: Proper scaling

---

## 📊 Modal-Specific Testing

### RoleForm.vue
```
Location: features/Settings/Role/presentations/views/Partials/RoleForm.vue
```

**Header Section**
- [ ] "Edit Role" or "Tambah Role Baru" visible
- [ ] Info icon clickable
- [ ] Close button (X) accessible

**Form Fields**
- [ ] "Label / Nama Role" input fully visible
- [ ] "Role ID (slug/sistem)" input fully visible
- [ ] Both inputs width: 100% within modal
- [ ] Placeholder text visible

**Help Section (F1)**
- [ ] Help section scrollable
- [ ] Two columns visible on desktop
- [ ] Single column on tablet/mobile
- [ ] Keyboard shortcuts list complete
- [ ] No shortcuts hidden
- [ ] Steps section fully readable

**Permission Grid**
- [ ] Search box fully visible (top right)
- [ ] Search input accessible
- [ ] Ctrl+F hint visible
- [ ] Permission checkboxes display properly
- [ ] 3-column grid on desktop
- [ ] 1-column grid on mobile
- [ ] No items cut off left/right
- [ ] Scrollbar visible if needed
- [ ] No items hidden at bottom

**Footer**
- [ ] Cancel button visible
- [ ] Save button visible
- [ ] Selected count visible ("X izin dipilih")
- [ ] Both buttons clickable
- [ ] Layout adjusts on mobile

---

## ✅ Visual Regression Tests

### Automated Checks (Playwright)
```javascript
// Test modal fits in viewport
expect(modal).toBeInViewport();

// Test header stays at top
expect(header).toBeInViewport();
expect(header).toHaveCSS('position', 'relative');

// Test footer stays at bottom
expect(footer).toBeInViewport();

// Test no overflow
expect(modalCard).toHaveCSS('overflow-y', 'auto');
expect(modalBody).toHaveCSS('overflow-y', 'auto');

// Test max-height constraints
expect(modalCard).toHaveCSS('max-height', '90vh');
expect(helpSection).toHaveCSS('max-height', '65vh');
```

### Manual Visual Inspection
- [ ] No red cutoff lines at edges
- [ ] No content reversed/flipped
- [ ] No upside-down text
- [ ] Natural reading order (top→bottom)
- [ ] Proper text direction (LTR for English)

---

## 🐛 Known Issues & Workarounds

### Issue: Content Cut at Bottom
**Solution**: Check modal has `max-height: 90vh` and body has `overflow-y: auto`
**Test**: Resize window and verify scrollbar appears

### Issue: Help Section Overlapping
**Solution**: Wrap help content in `div class="help-section"`
**Test**: Open help and scroll through all sections

### Issue: Buttons Cut Off on Mobile
**Solution**: Footer buttons should be `display: flex; flex-wrap: wrap-reverse;`
**Test**: Test on actual iPhone/Android devices

### Issue: Keyboard Hiding Content (Mobile)
**Solution**: Virtual keyboard handled by browser auto-scroll
**Test**: Click form input on mobile and verify form stays visible

---

## 📋 Sign-Off Checklist

Before deploying:

### Developer Review
- [ ] All tests in this checklist pass
- [ ] No visual regressions on key resolutions
- [ ] Tested on multiple browsers
- [ ] Responsive design working correctly

### QA Review
- [ ] Manual testing on real devices
- [ ] Cross-browser testing complete
- [ ] Mobile testing on iOS and Android
- [ ] Edge cases tested

### Product Review
- [ ] Modals look polished
- [ ] User experience is smooth
- [ ] No frustrating cutoffs
- [ ] Ready for production

---

## 📱 Testing Screen Resolutions

### Desktop
- [ ] 1920x1080 (Full HD)
- [ ] 1366x768 (Common laptop)
- [ ] 1024x768 (Small desktop)

### Tablet
- [ ] 768x1024 (iPad Portrait)
- [ ] 1024x768 (iPad Landscape)
- [ ] 768x1024 (Android Tablet)

### Mobile
- [ ] 375x667 (iPhone 6/7/8)
- [ ] 390x844 (iPhone 12/13)
- [ ] 414x896 (iPhone Max)
- [ ] 320x568 (iPhone SE)
- [ ] 412x915 (Android Common)

---

## 🔍 Tools for Testing

### Chrome DevTools
1. Open DevTools (F12)
2. Toggle Device Toolbar (Ctrl+Shift+M)
3. Select preset devices
4. Rotate between portrait/landscape
5. Test zoom levels

### Firefox Responsive Design Mode
1. Press Ctrl+Shift+M
2. Select device from dropdown
3. Test custom resolutions
4. Test scroll behavior

### Mobile Device Testing
1. Use BrowserStack for real devices
2. Test on actual iPhone/iPad
3. Test on actual Android devices
4. Test various network speeds

### Visual Regression
1. Use Percy.io for automated testing
2. Create baseline screenshots
3. Compare new versions
4. Catch unintended changes

---

## 📝 Test Report Template

```
Modal: [Modal Name]
Date: [YYYY-MM-DD]
Tester: [Name]
Browser: [Browser + Version]
Resolution: [WxH]

Results:
- Header: ✅ PASS / ❌ FAIL
- Body: ✅ PASS / ❌ FAIL
- Footer: ✅ PASS / ❌ FAIL
- Scrolling: ✅ PASS / ❌ FAIL
- Responsive: ✅ PASS / ❌ FAIL

Issues Found:
[List any cutoffs, overlaps, or visual issues]

Notes:
[Additional observations]
```

---

## ✨ Final Checklist

- [ ] All modals tested on desktop (1920px+)
- [ ] All modals tested on tablet (768px)
- [ ] All modals tested on mobile (375px)
- [ ] No content cuts off at top
- [ ] No content cuts off at bottom
- [ ] No content cuts off at left
- [ ] No content cuts off at right
- [ ] Help sections scroll properly
- [ ] Keyboard shortcuts visible
- [ ] All buttons clickable
- [ ] Responsive design working
- [ ] TypeScript compilation passes
- [ ] Tests passing (21 files, 68 tests)
- [ ] Ready for production

---

**Last Updated**: 2026-04-23
**Status**: ✅ Testing Framework Ready
