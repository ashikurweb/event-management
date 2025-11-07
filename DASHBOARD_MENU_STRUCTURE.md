# Dashboard Sidebar Menu Structure
## Event Management System - Menu Items

### 📊 MAIN MENU ITEMS

#### 1. **Dashboard** (Home/Overview)
- **Icon**: Dashboard/Home
- **Route**: `/dashboard`
- **Sub-items**: None
- **Description**: Overview with stats, charts, recent activities

---

#### 2. **Events** 
- **Icon**: Calendar/Event
- **Route**: `/dashboard/events`
- **Sub-items**:
  - All Events (`/dashboard/events`)
  - Create Event (`/dashboard/events/create`)
  - Draft Events (`/dashboard/events?status=draft`)
  - Published Events (`/dashboard/events?status=published`)
  - Cancelled Events (`/dashboard/events?status=cancelled`)
  - Event Tags (`/dashboard/events/tags`)
- **Description**: Manage all events, create, edit, view

---

#### 3. **Tickets & Orders**
- **Icon**: Ticket/Tag
- **Route**: `/dashboard/tickets`
- **Sub-items**:
  - All Orders (`/dashboard/orders`)
  - Pending Orders (`/dashboard/orders?status=pending`)
  - Completed Orders (`/dashboard/orders?status=completed`)
  - Tickets (`/dashboard/tickets`)
  - Ticket Types (`/dashboard/tickets/types`)
  - Check-in (`/dashboard/tickets/check-in`)
  - Waitlist (`/dashboard/tickets/waitlist`)
- **Description**: Manage ticket sales, orders, check-ins

---

#### 4. **Payments**
- **Icon**: Dollar/Credit Card
- **Route**: `/dashboard/payments`
- **Sub-items**:
  - All Payments (`/dashboard/payments`)
  - Pending Payments (`/dashboard/payments?status=pending`)
  - Completed Payments (`/dashboard/payments?status=completed`)
  - Refunds (`/dashboard/payments/refunds`)
  - Payment Methods (`/dashboard/payments/methods`)
- **Description**: Payment transactions, refunds management

---

#### 5. **Users & Roles**
- **Icon**: Users/Team
- **Route**: `/dashboard/users`
- **Sub-items**:
  - All Users (`/dashboard/users`)
  - Organizers (`/dashboard/users?role=organizer`)
  - Attendees (`/dashboard/users?role=attendee`)
  - Vendors (`/dashboard/users?role=vendor`)
  - Speakers (`/dashboard/users?role=speaker`)
  - Roles & Permissions (`/dashboard/users/roles`)
  - User Activity Logs (`/dashboard/users/activity-logs`)
- **Description**: User management, roles, permissions

---

#### 6. **Categories**
- **Icon**: Folder/Grid
- **Route**: `/dashboard/categories`
- **Sub-items**: None
- **Description**: Event categories management

---

#### 7. **Speakers**
- **Icon**: User/Person
- **Route**: `/dashboard/speakers`
- **Sub-items**:
  - All Speakers (`/dashboard/speakers`)
  - Featured Speakers (`/dashboard/speakers?featured=true`)
  - Add Speaker (`/dashboard/speakers/create`)
- **Description**: Manage event speakers

---

#### 8. **Sponsors**
- **Icon**: Star/Award
- **Route**: `/dashboard/sponsors`
- **Sub-items**:
  - All Sponsors (`/dashboard/sponsors`)
  - By Tier (Platinum, Gold, Silver, Bronze)
  - Add Sponsor (`/dashboard/sponsors/create`)
- **Description**: Sponsor management

---

#### 9. **Venues**
- **Icon**: Map Pin/Location
- **Route**: `/dashboard/venues`
- **Sub-items**:
  - All Venues (`/dashboard/venues`)
  - Add Venue (`/dashboard/venues/create`)
  - Verified Venues (`/dashboard/venues?verified=true`)
- **Description**: Venue management

---

#### 10. **Vendors**
- **Icon**: Shop/Store
- **Route**: `/dashboard/vendors`
- **Sub-items**:
  - All Vendors (`/dashboard/vendors`)
  - By Category
  - Add Vendor (`/dashboard/vendors/create`)
  - Event Vendors (`/dashboard/vendors/event-vendors`)
- **Description**: Vendor/exhibitor management

---

#### 11. **Teams**
- **Icon**: Team/Users
- **Route**: `/dashboard/teams`
- **Sub-items**:
  - My Teams (`/dashboard/teams`)
  - Team Members (`/dashboard/teams/members`)
  - Team Invitations (`/dashboard/teams/invitations`)
  - Create Team (`/dashboard/teams/create`)
- **Description**: Team/organizer team management

---

#### 12. **Promo Codes**
- **Icon**: Tag/Discount
- **Route**: `/dashboard/promo-codes`
- **Sub-items**:
  - All Promo Codes (`/dashboard/promo-codes`)
  - Active Promo Codes (`/dashboard/promo-codes?active=true`)
  - Create Promo Code (`/dashboard/promo-codes/create`)
  - Usage History (`/dashboard/promo-codes/usage`)
- **Description**: Discount codes management

---

#### 13. **Reviews & Ratings**
- **Icon**: Star/Review
- **Route**: `/dashboard/reviews`
- **Sub-items**:
  - All Reviews (`/dashboard/reviews`)
  - Pending Reviews (`/dashboard/reviews?status=pending`)
  - Approved Reviews (`/dashboard/reviews?status=approved`)
  - Review Replies (`/dashboard/reviews/replies`)
- **Description**: Event reviews moderation

---

#### 14. **Analytics & Reports**
- **Icon**: Bar Chart/Analytics
- **Route**: `/dashboard/analytics`
- **Sub-items**:
  - Event Analytics (`/dashboard/analytics/events`)
  - Sales Reports (`/dashboard/analytics/sales`)
  - User Reports (`/dashboard/analytics/users`)
  - Revenue Reports (`/dashboard/analytics/revenue`)
  - Custom Reports (`/dashboard/analytics/custom`)
- **Description**: Analytics, statistics, reports

---

#### 15. **Notifications**
- **Icon**: Bell/Notification
- **Route**: `/dashboard/notifications`
- **Sub-items**:
  - All Notifications (`/dashboard/notifications`)
  - Notification Preferences (`/dashboard/notifications/preferences`)
  - Custom Notifications (`/dashboard/notifications/custom`)
- **Description**: Notification management

---

#### 16. **Email & Communication**
- **Icon**: Mail/Envelope
- **Route**: `/dashboard/email`
- **Sub-items**:
  - Email Templates (`/dashboard/email/templates`)
  - Email Logs (`/dashboard/email/logs`)
  - Campaigns (`/dashboard/email/campaigns`)
  - Newsletter Subscribers (`/dashboard/email/subscribers`)
- **Description**: Email management, templates, campaigns

---

#### 17. **Surveys**
- **Icon**: Form/Clipboard
- **Route**: `/dashboard/surveys`
- **Sub-items**:
  - All Surveys (`/dashboard/surveys`)
  - Survey Responses (`/dashboard/surveys/responses`)
  - Create Survey (`/dashboard/surveys/create`)
- **Description**: Event surveys and feedback

---

#### 18. **Certificates**
- **Icon**: Certificate/Award
- **Route**: `/dashboard/certificates`
- **Sub-items**:
  - All Certificates (`/dashboard/certificates`)
  - Certificate Templates (`/dashboard/certificates/templates`)
  - Generate Certificates (`/dashboard/certificates/generate`)
- **Description**: Event certificates management

---

#### 19. **Content Management**
- **Icon**: File/Page
- **Route**: `/dashboard/pages`
- **Sub-items**:
  - All Pages (`/dashboard/pages`)
  - Create Page (`/dashboard/pages/create`)
  - Event Posts (`/dashboard/pages/posts`)
  - Post Comments (`/dashboard/pages/comments`)
- **Description**: CMS, static pages, event posts

---

#### 20. **Reports & Moderation**
- **Icon**: Flag/Alert
- **Route**: `/dashboard/reports`
- **Sub-items**:
  - All Reports (`/dashboard/reports`)
  - Pending Reports (`/dashboard/reports?status=pending`)
  - Resolved Reports (`/dashboard/reports?status=resolved`)
- **Description**: User reports, content moderation

---

#### 21. **Settings**
- **Icon**: Settings/Gear
- **Route**: `/dashboard/settings`
- **Sub-items**:
  - General Settings (`/dashboard/settings/general`)
  - Payment Settings (`/dashboard/settings/payment`)
  - Email Settings (`/dashboard/settings/email`)
  - System Settings (`/dashboard/settings/system`)
  - Profile Settings (`/dashboard/settings/profile`)
- **Description**: System configuration

---

### 📱 QUICK ACTIONS (Optional - Top Bar)
- Create Event (Quick)
- View Calendar
- Notifications (Bell Icon)
- User Profile Dropdown

---

### 🎯 MENU GROUPING SUGGESTION

**Group 1: Core**
- Dashboard
- Events
- Tickets & Orders
- Payments

**Group 2: People**
- Users & Roles
- Speakers
- Teams

**Group 3: Resources**
- Categories
- Venues
- Sponsors
- Vendors

**Group 4: Marketing**
- Promo Codes
- Email & Communication
- Campaigns

**Group 5: Engagement**
- Reviews & Ratings
- Surveys
- Certificates
- Notifications

**Group 6: Analytics**
- Analytics & Reports

**Group 7: Content**
- Content Management (Pages, Posts)

**Group 8: System**
- Reports & Moderation
- Settings

---

### 🔐 ROLE-BASED MENU VISIBILITY

**Super Admin**: All menu items
**Admin**: All except some system settings
**Organizer**: Events, Tickets, Orders, Payments, Speakers, Teams, Analytics (own events)
**Attendee**: Limited - My Tickets, My Events, Profile
**Vendor**: Limited - My Vendors, My Events
**Speaker**: Limited - My Profile, My Events

---

### 📝 NOTES
- Use Ant Design Vue Menu component
- Icons from @ant-design/icons-vue
- Collapsible sub-menus
- Active state highlighting
- Badge for notifications count
- Responsive sidebar (collapsible on mobile)

