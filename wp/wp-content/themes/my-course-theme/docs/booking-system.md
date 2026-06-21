# Course Booking System - UI/Feature Spec

## Goal
Build a timetable-based course booking system using WordPress.

---

## Core Concept

- Courses have multiple sessions
- Users book sessions via timetable UI
- Capacity decreases when booked

---

## Page Structure

### 1. /courses
- List of courses

### 2. /course/{slug}
- Course detail

### 3. /booking (MAIN)
- Weekly timetable UI
- Users select available slots

---

## Booking UI (Phase 1)

- Weekly grid (Mon–Fri)
- Time slots (10:00, 11:00, 14:00, 16:00)
- Status:
  - Available
  - Full
  - Selected

---

## Data Model (WordPress)

### course (CPT)
- title
- description

### session (CPT or ACF-based)
- course_id
- date
- time
- capacity

---

## Booking Flow

1. User selects slot
2. Click "Book"
3. capacity -1
4. refresh UI

---

## UI Strategy

Phase 1:
- Static mock data (no DB)

Phase 2:
- WordPress data binding

Phase 3:
- AJAX booking (no refresh)

---

## Milestones

- [x] WordPress setup
- [x] CPT course
- [ ] Booking UI mock
- [ ] Session model
- [ ] Booking logic
