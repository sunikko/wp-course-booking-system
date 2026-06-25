# WordPress Course Booking System

A custom, backend-focused WordPress development project featuring a dynamic, timetable-based booking system. This project was built to demonstrate advanced WordPress development skills, including Custom Post Types (CPT), AJAX data handling, and backend logic design beyond basic theme customization.

## Core Features
- Interactive Weekly Timetable: Users can browse available courses and book sessions directly from a weekly grid UI.
- Asynchronous Processing: Booking and cancellation actions are handled via AJAX, providing a seamless experience without page reloads.
- Smart Availability & Conflict Detection:
  - Real-time capacity calculation: Decreases capacity upon booking and updates UI to FULL when slots run out.
  - Conflict prevention: Automatically blocks overlapping timeslots and prevents duplicate bookings on the same date.
- User Dashboard (My Bookings): Users can view confirmed bookings and cancel them. Cancellations use a soft-delete approach (status: cancelled) to maintain booking history and automatically restore course capacity.

### Key Technical Achievements
- **Infrastructure & DevOps:** Deployed on an AWS Ubuntu server using Docker, managed with Nginx as a reverse proxy, and secured with SSL/TLS (HTTPS).
- **Backend Customization:** Built a custom WordPress theme with bespoke functionality using PHP and WordPress Hooks (`init`, `enqueue_scripts`, `after_setup_theme`).
- **Data Modeling:** Designed custom data structures using Custom Post Types (CPT) and Advanced Custom Fields (ACF) to manage course information dynamically.
- **Security & UX:** Implemented a custom frontend registration flow, enforced user permission hierarchies (hiding the WordPress admin bar for subscribers), and optimized the authentication lifecycle.
- **Version Control:** Managed the entire development lifecycle using Git, ensuring clean, modular, and maintainable code.

## Tech Stack
- Backend: PHP, WordPress (Custom Theme, CPT, ACF)
- Frontend: HTML, CSS, Vanilla JavaScript (AJAX)
- Database: MySQL
- Infrastructure: Docker / Docker Compose, AWS-EC2

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

---

## 🛠️ Access & Test Credentials
You can explore the live platform here: **[https://edubook.duckdns.org](https://edubook.duckdns.org)**

If you would like to test the authenticated features (such as personalized dashboards or booking flows), you are welcome to use the following test account:

- **Username:** `Test77`
- **Email:** `test@example.com`
- **Password:** `Test77Password`

> **Note:** Alternatively, feel free to create your own account at our [Registration Page](https://edubook.duckdns.org/register) to experience the full sign-up flow.