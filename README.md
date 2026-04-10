# CS5281 Online Quiz System

A lightweight online quiz system built for the CS5281 Internet Application Development assignment. The project uses PHP for server-side logic, HTML for page structure, CSS for styling, and vanilla JavaScript for client-side validation, countdown timing, and AJAX-based result statistics.

## Features

- Instructor and Student login with PHP sessions
- CSV import for student accounts
- Student group management and group assignment
- MCQ question bank management
- Quiz creation, publishing, and closing
- Group-based quiz access for students
- Automatic grading with one submission per student per quiz
- Student result review and quiz history
- Instructor statistics view with AJAX
- CSV export for quiz results

## Tech Stack

- PHP 8.5+ (built-in development server used locally)
- HTML5
- CSS3
- Vanilla JavaScript
- JSON files for storage
- CSV for import/export

## Project Structure

```text
public/               Browser entry points and static assets
  assets/             CSS and JavaScript
  instructor/         Instructor-facing pages
  student/            Student-facing pages
src/                  Shared PHP logic
data/                 JSON storage and exported CSV files
tests/                PHP CLI smoke-style tests
docs/                 Requirements, design spec, and implementation plan
```

## Prerequisites

1. PHP 8.5 or newer
2. A terminal that can run the PHP built-in server
3. Git, if you want to clone and pull updates

### Install PHP on macOS with Homebrew

```bash
brew install php
php -v
```

## Getting Started

Clone the repository and enter the project directory:

```bash
git clone https://github.com/bigyong-996/cs5281-group-online-quiz.git
cd cs5281-group-online-quiz
```

Start the local development server from the repository root:

```bash
php -S 127.0.0.1:8000 -t public
```

Then open:

```text
http://127.0.0.1:8000
```

## Default Login

The application auto-seeds a default instructor account when `data/users.json` is empty.

- Username: `instructor`
- Password: `instructor123`

The seed happens automatically on first login page load.

## Student CSV Import Format

The instructor import page expects this header exactly:

```csv
username,display_name,initial_password
alice,Alice Chan,alice123
bob,Bob Lee,bob123
```

You can use the sample file at `tests/fixtures/students.csv` as a reference.

## Recommended Demo Flow

1. Log in as the instructor
2. Import students from CSV
3. Create a student group
4. Assign students to the group
5. Add MCQ questions
6. Create and publish a quiz
7. Log in as an imported student
8. Take the quiz and submit it
9. Review student history
10. Return to the instructor account and open the results page
11. Load AJAX statistics and export CSV

## Running Tests

Run all PHP CLI checks:

```bash
for test in tests/*_test.php; do php "$test" || exit 1; done
```

Run syntax checks:

```bash
find src public tests -name '*.php' -print -exec php -l {} \;
```

## Data Storage

This project intentionally uses file-based storage instead of a database to match the assignment scope.

- `data/users.json`
- `data/groups.json`
- `data/questions.json`
- `data/quizzes.json`
- `data/submissions.json`
- `data/export/`

## Notes

- Exported CSV result files are ignored by Git.
- The app uses vanilla PHP pages, so `.php` files contain both server-side logic and rendered HTML.
- JavaScript is used for quiz validation, countdown timing, and AJAX result loading.
