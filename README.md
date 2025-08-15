# ClassroomBookings (Enhanced Timetable Generation)

**Note:** This is an enhanced fork of the original open-source project by Craig A. Rodway. This version introduces a powerful automated timetable generation system and other significant features.

## About This Project

The original project provided an excellent system for booking school rooms. This fork transforms it into a full-fledged academic scheduler by adding a core module for automatically generating complex, conflict-free timetables for an entire session.

---

## My Key Contributions & Features

### 1. Automated Timetable Generation
I engineered a new system capable of generating a complete academic timetable automatically. This core feature considers all specified constraints to create an optimal schedule, drastically reducing the manual effort required by school administrators.

### 2. Sophisticated Conflict Resolution
At the heart of the generator is a custom-built algorithm that prevents all common scheduling conflicts. The system ensures that:
- A lecturer is never assigned to two different venues at the same time.
- A student group is never scheduled for two different classes simultaneously.
- A single room is not booked for two different classes at the same time.

### 3. Comprehensive Resource Management
To power the generator, I built the back-end systems to manage all necessary academic resources, including:
- **Courses:** Adding and managing the list of available courses.
- **Student Groups:** Defining student groups to assign them to specific classes.
- **Rooms & Venues:** Managing a list of available rooms with their capacities and features.

### So much more ...
