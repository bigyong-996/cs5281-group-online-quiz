# Online Quiz System — Design Description

**Institution:** City University of Hong Kong (CityU)

## Finalized topic

### Online Quiz System

A web-based application built with **HTML**, **CSS**, and **JavaScript** on the client side and **PHP** on the server side. It supports instructors in **creating, managing, and delivering quizzes**, and enables students to **take assessments** and **review results**.

## Team members

| ID | Name |
| --- | --- |
| 60052196 | ZHANG Yiqiu |
| 59992979 | LI Xinjie |
| 59833028 | WANG Hongping |
| 59844290 | ZHANG Jinwei |
| 60098451 | SHAO You |
| 60083369 | LIU Chuan |
| 59889189 | HUANG Qingyang |
| 60101707 | Li Jinming |

## Core features list

### Instructor features

#### Question / answer management

- Create multiple-choice (MC) questions with **shuffled options** and short-answer questions.
- Edit or delete existing questions; categorize by topic and/or difficulty.

#### Quiz setup

- Build quizzes by selecting questions, setting time limits, and assigning quizzes to student groups.

#### Student group control

- Create and modify student groups; control which quizzes each group can access.

#### Data import / export (CSV)

- Bulk-import student accounts and quiz questions via CSV.
- Export quiz results, student scores, and question statistics to CSV for analysis.

#### Result statistics and history

- View class-level performance (average score, pass rate, per-question accuracy).
- Review individual students’ submission history and detailed answer breakdowns.

### Student features

#### Quiz taking

- Access assigned quizzes; MC options are **shuffled** to reduce cheating.
- **Client-side validation** with JavaScript (e.g. flag unanswered questions) before submit.

#### Result review

- After grading, view scores, correct answers, and feedback.
- Access personal quiz history to track performance over time.

## Interface draft

### Login page

- Form with **username** and **password**.
- JavaScript validation for empty fields and password format.
- **Role selection** (Instructor / Student) to route users to the correct dashboard.

### Instructor dashboard

- **Sidebar:** Questions, Quizzes, Groups, Results, Import/Export.
- **Main area:**
  - **Quick stats:** total quizzes, active students, recent submissions.
  - **Actions:** Create New Quiz, Import Questions.

### Quiz creation interface

1. **Step 1:** Name the quiz, set time limit, choose target student group.
2. **Step 2:** Add questions (filter by topic/difficulty); preview shuffled MC options.
3. **Step 3:** Publish the quiz so students can access it.

### Student quiz interface

- Clear layout: question number, text, and options (MC options shuffled).
- **Timer** and **Submit** button; JavaScript prevents invalid early submit or missing answers.
- After submit: immediate score for auto-graded MC, plus link to full results.

### Results and statistics page

- **Instructor view:** bar chart of class scores, per-question accuracy, CSV export.
- **Student view:** personal score breakdown, correct/incorrect highlighting, quiz history list.

---

**Thank you!**
