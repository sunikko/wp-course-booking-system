# WordPress Course Booking System

A custom, backend-focused WordPress development project featuring a dynamic, timetable-based booking system. This project was built to demonstrate advanced WordPress development skills, including Custom Post Types (CPT), AJAX data handling, and backend logic design beyond basic theme customization.

## Core Features
- Interactive Weekly Timetable: Users can browse available courses and book sessions directly from a weekly grid UI.
- Asynchronous Processing: Booking and cancellation actions are handled via AJAX, providing a seamless experience without page reloads.
- Smart Availability & Conflict Detection:
  - Real-time capacity calculation: Decreases capacity upon booking and updates UI to FULL when slots run out.
  - Conflict prevention: Automatically blocks overlapping timeslots and prevents duplicate bookings on the same date.
- User Dashboard (My Bookings): Users can view confirmed bookings and cancel them. Cancellations use a soft-delete approach (status: cancelled) to maintain booking history and automatically restore course capacity.

## Tech Stack
- Backend: PHP, WordPress (Custom Theme, CPT, ACF)
- Frontend: HTML, CSS, Vanilla JavaScript (AJAX)
- Database: MySQL
- Infrastructure: Docker / Docker Compose

## Data Model & System Design

### 1. Custom Post Types (CPT)
- Course (course): Contains course details, base capacity, schedule, and pricing.
- Booking (booking): Acts as a relational mapping between the User and the Course. Stores booking_date, status (confirmed/cancelled), and booked_at timestamp.

### 2. Booking Flow
1. User selects a slot from the timetable (/booking).
2. Validates user session, duplicate bookings, and remaining course capacity.
3. System creates a booking post and decreases the course's capacity by 1.
4. UI syncs and updates the status to BOOKED or FULL.

## Milestones & Future Roadmap

Current Progress
- [x] Phase 1: WordPress local environment setup (Docker) & Define Data Models (CPT/ACF).
- [x] Phase 2: Implement static timetable UI mockups and data binding with WP_Query.
- [x] Phase 3: Complete backend booking logic (capacity handling, duplicate checks).
- [x] Phase 4: Apply AJAX for asynchronous booking/cancellations and build the user dashboard.

Future Expansions
- [ ] Phase 5: Implement automated email notifications for booking confirmations and cancellations.
- [ ] Phase 6: Build custom REST API endpoints to expose course and booking data.
- [ ] Phase 7: Migrate the frontend UI to a decoupled React application utilizing WordPress as a Headless CMS.
- [ ] Phase 8: Develop a waitlist system for fully booked courses and integrate payment gateways (Stripe/PayPal) for premium classes.