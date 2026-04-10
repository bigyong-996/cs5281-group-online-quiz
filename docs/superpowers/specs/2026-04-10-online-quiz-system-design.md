# Online Quiz System Design

Date: 2026-04-10
Topic: CS5281 Online Quiz System
Status: Approved design for implementation planning

## 1. Context

This project is a course assignment for CS5281 Internet Application Development. The goal is to build a web-based Online Quiz System with HTML, CSS, JavaScript, and PHP. The system should feel complete enough for demonstration and grading, while staying intentionally small so it can be finished quickly and reliably.

The assignment values a realistic feature set, correct handling of input, visible use of JavaScript and PHP, and a working end-to-end flow more than business complexity. This design therefore favors a compact scope with one clear quiz workflow and a small set of enhancement features for bonus value.

## 2. Product Summary

The Online Quiz System supports two roles:

- Instructor: imports students, manages groups, creates questions, builds quizzes, assigns quizzes to groups, and reviews results.
- Student: logs in, sees assigned quizzes, takes quizzes, and reviews scores and history.

The product focus is a complete flow:

1. Instructor imports students and organizes groups.
2. Instructor creates MCQ questions and builds a quiz.
3. Instructor assigns the quiz to one or more student groups and publishes it.
4. Student logs in, completes the quiz, and submits answers.
5. The system grades the quiz automatically and stores the submission.
6. Instructor reviews results and exports scores.

## 3. Goals

- Deliver a complete Instructor-to-Student quiz workflow suitable for course demonstration.
- Match the assignment rubric with visible use of HTML, CSS, JavaScript, PHP, sessions, file handling, and CSV processing.
- Keep implementation lightweight enough for fast completion.
- Include at least one clearly demonstrable enhancement feature with AJAX.

## 4. Non-Goals

- Student self-registration
- Short-answer questions or manual grading
- Email notifications
- Complex authorization models
- Database scalability or concurrency optimization
- Advanced anti-cheating features
- Mobile app support

## 5. Scope

### 5.1 Core Scope

#### Authentication and session management

- Instructor and Student login
- Session-based access control
- Role-based landing pages
- Logout

#### Student import and group management

- Instructor imports student accounts through CSV
- Instructor creates, edits, and deletes student groups
- Instructor assigns imported students to groups
- Each student belongs to one group at a time to keep assignment logic simple

#### Question bank management

- Instructor creates, edits, and deletes multiple-choice questions
- Each question includes question text, answer options, and the correct answer
- Optional topic field for simple categorization

#### Quiz creation and publishing

- Instructor creates a quiz with title, description, and time limit
- Instructor selects questions from the question bank
- Instructor assigns a quiz to one or more student groups
- Quiz status is managed as draft, published, or closed
- A published quiz is visible only to assigned groups

#### Student quiz taking

- Student sees only quizzes assigned to their group
- Student enters a quiz and answers MCQ questions
- JavaScript validates incomplete submissions before submit
- Quiz page includes a countdown timer
- MCQ options can be displayed in shuffled order

#### Automatic grading and history

- System grades submissions immediately after submit
- Each student is allowed one submission per quiz
- Each question carries equal weight for simple automatic scoring
- Student sees current score and past quiz history
- Instructor sees submission records and basic performance summaries

### 5.2 Enhancement Scope

These features improve presentation quality and support bonus marks, but should not block the core workflow if time becomes tight.

#### CSV export

- Instructor exports quiz scores and submission data as CSV

#### Basic statistics

- Instructor views average score, submission count, and per-question accuracy

#### AJAX enhancement

At least one of the following must be implemented with AJAX:

- Load quiz statistics without a full page refresh
- Show CSV import results asynchronously
- Publish or close a quiz asynchronously

## 6. Users and Roles

### Instructor

Primary responsibilities:

- Manage students and groups
- Manage questions and quizzes
- View results and export scores

### Student

Primary responsibilities:

- View assigned quizzes
- Complete quizzes within the configured quiz time limit
- Review scores and submission history

## 7. Key User Flows

### 7.1 Instructor workflow

1. Log in as Instructor.
2. View dashboard with quiz and submission summary.
3. Import student accounts from CSV.
4. Create groups and place students into groups.
5. Create or edit MCQ questions in the question bank.
6. Build a quiz from selected questions.
7. Set time limit and assign the quiz to one or more groups.
8. Publish the quiz.
9. Review submissions, scores, and basic statistics.
10. Export results to CSV.

### 7.2 Student workflow

1. Log in as Student.
2. View available quizzes assigned to the student's group.
3. Open a quiz and answer questions within the time limit.
4. Submit answers after JavaScript validation.
5. View the score and correct or incorrect breakdown.
6. Review past quiz records in the history page.

## 8. Page Structure

### Shared pages

- Login page
- Logout action

### Instructor pages

- Dashboard
- Student Import page
- Group Management page
- Question Bank page
- Quiz Management page
- Quiz Results and Statistics page

### Student pages

- Dashboard or Available Quizzes page
- Quiz Taking page
- Result Detail page
- Quiz History page

## 9. Data Design

The system will use file-based storage instead of a database. This matches the assignment guidance and reduces setup and implementation complexity.

### Core entities

#### users

- id
- username
- display_name
- password
- role
- group_id

#### groups

- id
- group_name

#### questions

- id
- question_text
- options
- correct_answer
- topic

#### quizzes

- id
- title
- description
- time_limit
- assigned_group_ids
- question_ids
- status

#### submissions

- id
- quiz_id
- student_id
- answers
- score
- submitted_at

### Suggested storage files

- `data/users.json`
- `data/groups.json`
- `data/questions.json`
- `data/quizzes.json`
- `data/submissions.json`
- `data/import/*.csv`
- `data/export/*.csv`

### CSV import format

The initial student import file should contain the following columns:

- username
- display_name
- initial_password

Group assignment is handled inside the system after import rather than through the CSV file.

## 10. Technical Design

### Frontend responsibilities

- HTML provides page structure and forms
- CSS provides layout, readability, and consistent navigation
- JavaScript handles form validation, timer behavior, unanswered-question checks, and AJAX enhancement

### Backend responsibilities

- PHP handles authentication, session checks, role checks, file reading and writing, grading, CSV import and export, and response generation

### Data flow

1. The browser sends form data to PHP endpoints.
2. PHP validates the request and the user session.
3. PHP reads or updates JSON and CSV files.
4. PHP returns the next page or a JSON response for AJAX features.
5. JavaScript updates the page when AJAX is used.

## 11. Validation, Error Handling, and Security

### Client-side validation

- Required login fields must not be empty
- Required quiz answers are checked before submit
- CSV upload form checks file presence and basic type

### Server-side validation

- All protected pages require a valid session
- Instructor-only operations check role before execution
- Quiz submission verifies student identity and quiz access
- CSV import validates row format before data is written
- File operations check that referenced records exist

### Error handling

- Invalid login shows a clear error message
- Invalid CSV rows are reported in an import summary
- Empty or malformed form submissions return readable validation messages
- Missing quiz access returns an error page or redirect instead of a PHP warning

### Basic security requirements

- Passwords should be stored safely using PHP password hashing
- Inputs should be sanitized before file storage or page rendering
- Users must not access another role's protected pages directly

## 12. Browser and UI Requirements

- The system should work in Chrome, Safari, and Firefox
- Page layouts should remain readable on common laptop and desktop widths
- Forms and tables should be easy to scan during live demonstration
- Styling should be simple, consistent, and avoid obvious usability issues

## 13. Testing and Acceptance

### Functional acceptance criteria

1. Instructor can log in and reach the dashboard.
2. Instructor can import student accounts with CSV.
3. Instructor can create and manage student groups.
4. Instructor can create, edit, and delete MCQ questions.
5. Instructor can create a quiz from question bank items.
6. Instructor can assign a quiz to one or more groups and publish it.
7. Student can log in and see only quizzes assigned to the student's group.
8. Student can take a quiz with a visible timer and submit answers.
9. The system grades the quiz automatically and stores the submission.
10. Student can view the latest score and past history.
11. Instructor can view quiz results and basic statistics.
12. Instructor can export scores as CSV.
13. At least one AJAX feature works during demonstration.

### Quality acceptance criteria

- Invalid inputs produce user-facing validation messages instead of raw errors
- Protected pages are not accessible without login
- Cross-role access is blocked
- The core workflow can be demonstrated within a short course demo video

## 14. Delivery Strategy

Implementation should be prioritized in this order:

1. Authentication and role routing
2. Student import and group management
3. Question bank management
4. Quiz creation and publishing
5. Student quiz taking and automatic grading
6. Result history and CSV export
7. AJAX enhancement and statistics polish

If time becomes limited, enhancement work may be reduced, but the core Instructor-to-Student quiz workflow must remain complete.

## 15. Rationale

This design is intentionally conservative. It matches the assignment topic closely, covers the rubric with visible use of the required technologies, and avoids features that create large implementation cost without helping grading. The result should look like a complete online quiz system rather than a toy demo, while still being small enough to finish quickly.
