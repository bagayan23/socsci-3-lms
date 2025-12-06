# Testing Guide for SOCSCI-3 LMS

## How to Test the Improvements

### 1. Visual Design Testing

#### Header
- ✅ Check if header has gradient background
- ✅ Verify text has gradient color effect
- ✅ Confirm sticky positioning works when scrolling

#### Flashcards
- ✅ Cards should automatically rotate every 4 seconds
- ✅ Click indicators at bottom to manually switch cards
- ✅ Hover over flashcard area - rotation should pause
- ✅ Check smooth 3D rotation animations
- ✅ Verify placeholder images load if originals are missing

#### Login Form
- ✅ Form should have rounded corners and shadow
- ✅ Hover over the card - should lift slightly
- ✅ Click "Don't have an account?" - should smoothly switch to signup

#### Buttons
- ✅ Hover over buttons - should see shine animation and lift effect
- ✅ Click buttons - should see press effect
- ✅ Icons should display correctly

### 2. Form Validation Testing

#### Login Form
1. **Email Validation**
   - Try entering "notanemail" → Should show error
   - Enter "test@email.com" → Should show valid state
   
2. **Password Validation**
   - Leave empty and blur → Should show "required" error
   - Enter "12345" → Should show "minimum 6 characters" error
   - Enter "123456" → Should show valid state

3. **Password Toggle**
   - Click eye icon → Password should become visible
   - Eye icon should change color
   - Click again → Password should hide

#### Signup Form
1. **Role Selection**
   - Select "Student" → Student fields should appear
   - Select "Teacher" → Student fields should hide
   
2. **Text Validation**
   - In First Name, try typing "John123" → Numbers should be removed automatically
   - Should only accept letters and spaces

3. **Number Validation**
   - In Contact Number, try typing "abc" → Should not allow letters
   - Should only accept numbers

4. **Student ID Format**
   - Try "123456" → Should show error
   - Try "12-3456" → Should be valid
   
5. **Address Selection**
   - Select Region → Province should disable
   - Clear Region → Province should enable
   - Select Province → Region should disable
   - City and Barangay should populate based on selection

6. **Form Submission**
   - Leave required fields empty → Should show errors
   - Fill all fields → Should submit successfully

### 3. File Preview Testing

To test file preview, you'll need to call the `previewFile()` function:

#### Test Different File Types

**Images:**
```javascript
previewFile('path/to/image.jpg', 'test-image.jpg');
```
- Should display image in modal
- Should show filename in header
- Download button should work
- Close button should close modal
- ESC key should close modal

**Videos:**
```javascript
previewFile('path/to/video.mp4', 'test-video.mp4');
```
- Should show video player with controls
- Play/pause should work
- Volume control should work

**Audio:**
```javascript
previewFile('path/to/audio.mp3', 'test-audio.mp3');
```
- Should show audio player with music icon
- Play controls should work

**PDFs:**
```javascript
previewFile('path/to/document.pdf', 'test.pdf');
```
- Should display PDF with native browser viewer
- Zoom and navigation should work

**Office Documents:**
```javascript
previewFile('path/to/document.docx', 'test.docx');
```
- Should attempt to load with Office Web Viewer
- If fails, should show download option

**Text Files:**
```javascript
previewFile('path/to/file.txt', 'test.txt');
```
- Should display text content
- Should preserve formatting
- Long files should show truncation message

**Archives:**
```javascript
previewFile('path/to/archive.zip', 'test.zip');
```
- Should show archive icon
- Should display download button
- Should explain extraction is needed

**Unknown Types:**
```javascript
previewFile('path/to/file.xyz', 'test.xyz');
```
- Should show generic file icon
- Should display file extension
- Should offer download option

### 4. Responsive Design Testing

#### Desktop (1920px+)
- ✅ Flashcards and auth section side by side
- ✅ Signup form in 3 columns
- ✅ Footer in 3 columns

#### Tablet (768px - 1919px)
- ✅ Flashcards and auth section side by side
- ✅ Signup form in 3 columns
- ✅ Footer in 3 columns

#### Mobile (480px - 767px)
- ✅ Flashcards stack on top
- ✅ Auth section below
- ✅ Signup form in 1 column
- ✅ Footer in 1 column
- ✅ Buttons are touch-friendly
- ✅ Text is readable

#### Small Mobile (<480px)
- ✅ All elements fit properly
- ✅ No horizontal scrolling
- ✅ Forms are usable
- ✅ Buttons are accessible

### 5. Accessibility Testing

#### Keyboard Navigation
- ✅ Tab through form fields
- ✅ Enter to submit form
- ✅ ESC to close modal
- ✅ Focus indicators visible

#### Screen Reader
- ✅ Form labels are readable
- ✅ Error messages are announced
- ✅ Button purposes are clear

### 6. Browser Compatibility Testing

Test in:
- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)

### 7. Performance Testing

#### Load Time
- ✅ Page should load in <2 seconds
- ✅ Animations should be smooth (60fps)
- ✅ No lag when typing

#### Memory
- ✅ No memory leaks when switching forms
- ✅ Modal content clears properly

### 8. Error Handling Testing

#### Network Issues
- ✅ Missing images should show placeholders
- ✅ Failed file loads should show error messages
- ✅ PSGC API failure should handle gracefully

#### Invalid Data
- ✅ Forms should prevent submission of invalid data
- ✅ Server errors should display friendly messages

## Quick Test Checklist

Use this checklist to verify all improvements:

### Design
- [ ] Modern color scheme applied
- [ ] Gradient backgrounds visible
- [ ] Shadows and rounded corners present
- [ ] Hover effects work
- [ ] Animations are smooth

### Functionality
- [ ] Login form works
- [ ] Signup form works
- [ ] Password toggle works
- [ ] Role selection works
- [ ] Form validation works
- [ ] Flashcard rotation works
- [ ] Indicators work
- [ ] File preview works for all types

### Responsive
- [ ] Desktop layout correct
- [ ] Tablet layout correct
- [ ] Mobile layout correct
- [ ] No horizontal scroll

### Accessibility
- [ ] Keyboard navigation works
- [ ] Focus indicators visible
- [ ] Error messages clear
- [ ] Color contrast sufficient

## Common Issues and Solutions

### Issue: Flashcards not rotating
**Solution**: Check JavaScript console for errors, ensure script.js is loaded

### Issue: Validation not working
**Solution**: Ensure novalidate attribute is on forms, check browser console

### Issue: File preview modal not appearing
**Solution**: Check if modal HTML is injected, verify previewFile() is called correctly

### Issue: Styles not applying
**Solution**: Clear browser cache, check CSS file paths, verify CSS is loaded

### Issue: Address dropdowns not populating
**Solution**: Check internet connection (PSGC API requires internet), check console for API errors

## Testing Tips

1. **Use Browser DevTools**
   - Inspect elements to verify styles
   - Check Console for JavaScript errors
   - Use Network tab for API calls
   - Use Application tab for storage

2. **Test Edge Cases**
   - Very long text in fields
   - Special characters in inputs
   - Multiple rapid clicks
   - Slow internet connection

3. **Test User Flows**
   - Complete signup process
   - Switch between forms multiple times
   - Fill form, refresh, try again
   - Submit with errors, fix, resubmit

4. **Test Across Devices**
   - Real mobile device
   - Tablet
   - Desktop with different resolutions
   - Different orientations (portrait/landscape)

## Automated Testing (Future)

Consider implementing:
- Unit tests for JavaScript functions
- Integration tests for form flows
- Visual regression tests
- Performance monitoring
- Accessibility audits

---

**Last Updated**: December 6, 2025
**Version**: 1.0
