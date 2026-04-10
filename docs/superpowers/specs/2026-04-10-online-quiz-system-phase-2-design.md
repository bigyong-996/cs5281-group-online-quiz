# Online Quiz System Phase 2 Design

Date: 2026-04-10
Topic: CS5281 Online Quiz System Phase 2
Status: Approved design for implementation planning

## 1. Context

Phase 1 delivered a complete file-based online quiz workflow with two roles, CSV student import, student groups, MCQ quiz publishing, automatic grading, result history, and AJAX-based result statistics. The system is functional and suitable for demonstration, but it still looks visually basic and several quiz interactions remain close to a minimal CRUD implementation.

Phase 2 is intended as a high-score enhancement release. Its purpose is not to replace the Phase 1 architecture, but to upgrade the product into a more polished and more convincing course project. The emphasis is on stronger presentation quality and richer quiz behavior without introducing a new platform architecture.

## 2. Product Summary

Phase 2 upgrades the existing Online Quiz System in two directions:

- Visual and usability improvements across the major pages
- Deeper quiz authoring, quiz presentation, and result analysis features

The most visible Phase 2 signature feature is support for richer question content, including basic formatted text and question images. Around that core, Phase 2 improves the teacher workflow, the student answering experience, and the analytics presentation.

## 3. Goals

- Make the application feel like a more complete and intentionally designed web system
- Improve the quality of the quiz experience for both Instructor and Student users
- Add lightweight rich-content support for questions, especially image-based questions
- Strengthen the course-demo impact without changing the existing PHP + JSON architecture
- Preserve Phase 1 core workflows while improving presentation and interaction quality

## 4. Non-Goals

- Replacing file storage with a database
- Adopting a frontend framework or SPA architecture
- Adding a heavy WYSIWYG editor
- Building a full examination engine with advanced attempt rules
- Supporting collaborative authoring
- Introducing complex access policies or approval workflows
- Building an admin CMS outside the existing quiz workflows

## 5. Scope

### 5.1 Phase 2 Must-Have Scope

#### Visual system upgrade

- Redesign the visual style of the application
- Improve the consistency of layout, spacing, navigation, forms, tables, and feedback states
- Upgrade the most important pages so the difference from Phase 1 is clearly visible

#### Rich question content

- Questions support lightweight formatted content
- Questions support an uploaded image at the question level
- Instructor question preview displays formatted text and images correctly
- Student quiz pages render the same content correctly

#### Instructor quiz authoring upgrade

- Quiz creation and quiz management become easier to understand visually
- Instructor can preview or review quiz content more clearly before publishing
- Quiz state and composition are presented more clearly in the management flow

#### Student answering experience upgrade

- Student quiz pages show clearer progress and orientation
- Timer display becomes more prominent and easier to read
- Submission flow includes clearer confirmation feedback
- The answering experience feels more structured than a plain long form

#### Results and analytics upgrade

- Instructor results page becomes more dashboard-like
- Summary information is easier to scan
- Per-question analysis is presented more clearly
- Existing AJAX behavior is retained and improved

### 5.2 Phase 2 Enhancement Scope

These items are valuable but should not block the main Phase 2 delivery if time becomes limited.

- Support images in answer options
- Add richer quiz draft summaries and filters
- Add deeper statistics views or more visual analysis layouts
- Add stronger student performance overview on the history page
- Add more dynamic preview behaviors during authoring

## 6. Functional Modules

### 6.1 Visual System Upgrade

Phase 2 introduces a unified visual system for the application. This includes improved color usage, typography, card hierarchy, table presentation, form styling, and dashboard composition. The UI should still remain lightweight and practical for a PHP course project, but it should no longer feel like a plain scaffolded interface.

Priority pages for redesign:

- Login page
- Instructor dashboard
- Question bank page
- Quiz management page
- Student quiz page
- Instructor results page

### 6.2 Rich Question Content

Question content moves from plain text toward lightweight rich content. This includes:

- formatted paragraph content
- line breaks
- emphasis styles such as bold or italic
- simple lists
- one uploaded question image

This module should improve both content quality and the demo value of the system, especially for quizzes involving diagrams, screenshots, or illustrative figures.

### 6.3 Instructor Quiz Authoring Upgrade

The teacher workflow becomes more intentional and less form-like. Instead of only selecting questions and saving a quiz, the system should make it easier to understand:

- what a quiz contains
- which groups will receive it
- how the quiz will appear when published
- what state the quiz is currently in

The goal is to make the authoring flow look more like quiz management and less like a raw configuration form.

### 6.4 Student Answering Experience Upgrade

The student experience should feel more focused and less like filling a generic administrative page. The page should make quiz progress, timer pressure, and submission status clear. Students should be able to orient themselves more easily while answering, especially on longer quizzes or image-based questions.

### 6.5 Results and Analytics Upgrade

The Phase 1 results page already computes statistics, but the presentation is minimal. Phase 2 should make result interpretation more meaningful through clearer summaries, better grouping of information, and stronger visual hierarchy. This is meant to improve both usability and the impression of analytical depth during demo.

## 7. Key Page Upgrades

### 7.1 Login and dashboards

- Login page becomes visually stronger and clearer as a project entry point
- Instructor dashboard becomes a real overview page with stronger information hierarchy
- Student dashboard distinguishes available quizzes, completed work, and history more clearly

### 7.2 Question bank page

- Question creation supports formatted content and question image upload
- Question preview is available in the authoring flow
- Question list entries display richer summaries than only raw text

### 7.3 Quiz management page

- Quiz creation is visually segmented into meaningful sections
- Selected questions and assigned groups are easier to review
- Quiz preview or clearer quiz summary is available before publishing
- Quiz list shows draft, published, and closed states more effectively

### 7.4 Student quiz page

- Question content is more readable
- Images display cleanly and consistently
- Progress visibility is improved
- Submission becomes a more explicit and intentional action

### 7.5 Student result and history pages

- Result pages clearly distinguish summary and detailed answer review
- History pages are easier to scan and interpret
- Performance review feels more like feedback than raw record listing

### 7.6 Instructor results page

- Results become more dashboard-like
- Summary cards and analysis sections are visually separated
- AJAX-loaded updates feel integrated into the page rather than appended text

## 8. Data Design Upgrade

Phase 2 keeps the existing JSON file storage approach. No database migration is required.

### Current question model

Phase 1 questions are centered around:

- question_text
- options
- correct_answer
- topic

### Phase 2 question model

Questions should be extended to support richer content:

- question_text or question_title
- question_content_html
- question_image_path
- options
- correct_answer
- topic

The implementation should support backward compatibility for existing Phase 1 question records. If a question does not include rich content fields, the system should fall back to the old text-based rendering logic.

## 9. Rich Content Strategy

Phase 2 should use a lightweight rich-content model rather than a full editor platform.

### Allowed content scope

Recommended supported HTML-like content:

- paragraph blocks
- line breaks
- strong emphasis
- italic emphasis
- unordered lists
- ordered lists
- list items
- image rendering for the uploaded question image

Rich content support is limited to the question body. It does not include arbitrary embedded scripts, custom widgets, or full-page HTML structures.

### Content handling strategy

- Instructor enters lightweight formatted content
- The server sanitizes content using a constrained allowlist model
- The browser renders sanitized content for preview and quiz display
- The content model stays small and predictable

This approach keeps the system manageable while still making the questions noticeably richer.

## 10. Image Support Strategy

Question image handling should remain local and simple.

### Recommended approach

- Uploaded images are stored under a local public directory such as `public/uploads/questions/`
- The question record stores the relative path to the image
- Instructor preview and Student quiz pages both render the same image source

### Constraints

- Only one image per question is required in Phase 2 core scope
- Option images are enhancement scope only
- Large media management, image editing, and external storage are out of scope

## 11. UI and Interaction Architecture

Phase 2 keeps the existing architecture pattern:

- PHP renders pages and handles server-side logic
- CSS defines the improved visual system
- JavaScript handles progressive enhancements and AJAX interactions

No frontend framework is introduced. Instead, Phase 2 adds a more deliberate structure to the existing page-based model.

### Interaction focus areas

- richer preview behavior in authoring flows
- cleaner feedback on publish and review actions
- stronger student submission feedback
- more polished AJAX result loading behavior

## 12. Compatibility Strategy

Phase 2 must preserve Phase 1 workflows.

Compatibility requirements:

- Existing users, groups, quizzes, and submissions remain readable
- Existing questions without rich fields still render correctly
- Existing statistics and export features continue to work
- Core login, group assignment, quiz publishing, and submission workflows are not broken by the redesign

This is essential because Phase 2 is an enhancement release, not a restart.

## 13. Acceptance Criteria

### Phase 2 must-have acceptance criteria

1. The application has a visibly upgraded UI across the main pages.
2. At least one question can include a question image and display it correctly in both Instructor and Student flows.
3. Question content supports lightweight formatting and displays correctly.
4. Instructor quiz authoring presents quiz composition more clearly than the Phase 1 version.
5. Student quiz pages provide clearer progress, timer visibility, or submission flow than Phase 1.
6. Instructor results presentation is visually and structurally clearer than the Phase 1 page.
7. At least one improved AJAX interaction remains part of the final system.
8. Existing Phase 1 quiz workflows still work after the Phase 2 upgrade.
9. Existing Phase 1 question records still display even without new rich-content fields.

### Phase 2 enhancement acceptance criteria

1. Option images are supported.
2. Quiz authoring includes richer dynamic preview behavior.
3. Results pages include deeper visual analysis or filtering.
4. Student performance summaries become more informative over time.

## 14. Delivery Priority

Implementation should be prioritized in this order:

1. Visual system redesign foundation
2. Rich question content model
3. Question image upload and rendering
4. Question bank and quiz authoring page upgrade
5. Student quiz page upgrade
6. Results and analytics presentation upgrade
7. Enhancement-only items such as option images or deeper statistics

This order ensures that the most visible and highest-value improvements land first.

## 15. Rationale

Phase 2 is intentionally balanced. It upgrades what users see and what the quiz system can express, without forcing a new architecture, new storage model, or a full rewrite. That makes it appropriate for a course assignment: it increases polish, increases apparent system maturity, and improves the demo impact, while still staying implementable on top of the Phase 1 foundation.
