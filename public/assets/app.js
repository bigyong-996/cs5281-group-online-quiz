document.addEventListener('DOMContentLoaded', () => {
  const allowedRichTags = new Set(['P', 'BR', 'STRONG', 'EM', 'UL', 'OL', 'LI']);

  const sanitizeRichContent = (rawHtml) => {
    const parser = new window.DOMParser();
    const documentRoot = parser.parseFromString(`<div>${rawHtml}</div>`, 'text/html');
    const wrapper = documentRoot.body.firstElementChild;
    const output = document.createElement('div');

    const sanitizeNode = (node) => {
      if (node.nodeType === window.Node.TEXT_NODE) {
        return document.createTextNode(node.textContent || '');
      }

      if (node.nodeType !== window.Node.ELEMENT_NODE) {
        return document.createDocumentFragment();
      }

      if (!allowedRichTags.has(node.tagName)) {
        const fragment = document.createDocumentFragment();
        Array.from(node.childNodes).forEach((child) => fragment.appendChild(sanitizeNode(child)));
        return fragment;
      }

      const clean = document.createElement(node.tagName.toLowerCase());
      Array.from(node.childNodes).forEach((child) => clean.appendChild(sanitizeNode(child)));
      return clean;
    };

    Array.from(wrapper.childNodes).forEach((child) => output.appendChild(sanitizeNode(child)));
    return output.innerHTML.trim();
  };

  const previewInput = document.querySelector('[data-rich-content-input]');
  if (previewInput instanceof HTMLTextAreaElement) {
    const previewBody = document.querySelector(previewInput.dataset.previewBody || '');
    const previewImage = document.querySelector(previewInput.dataset.previewImage || '');
    const fileInput = document.querySelector(previewInput.dataset.previewImageInput || '');
    const existingImage = previewInput.dataset.existingImage || '';
    let objectUrl = '';

    const renderPreview = () => {
      if (previewBody) {
        const sanitizedHtml = sanitizeRichContent(previewInput.value.trim());
        previewBody.innerHTML = sanitizedHtml || '<p class="muted">Preview appears here.</p>';
      }

      if (!(previewImage instanceof HTMLImageElement)) {
        return;
      }

      const file = fileInput instanceof HTMLInputElement ? fileInput.files?.[0] : null;
      if (objectUrl) {
        window.URL.revokeObjectURL(objectUrl);
        objectUrl = '';
      }

      if (file) {
        objectUrl = window.URL.createObjectURL(file);
        previewImage.src = objectUrl;
        previewImage.classList.remove('is-hidden');
        return;
      }

      if (existingImage) {
        previewImage.src = existingImage;
        previewImage.classList.remove('is-hidden');
        return;
      }

      previewImage.removeAttribute('src');
      previewImage.classList.add('is-hidden');
    };

    renderPreview();
    previewInput.addEventListener('input', renderPreview);
    if (fileInput instanceof HTMLInputElement) {
      fileInput.addEventListener('change', renderPreview);
    }
  }

  const quizBuilderForm = document.querySelector('[data-quiz-builder-form]');
  const quizSummary = document.querySelector('[data-quiz-summary]');
  const quizSelectedQuestions = document.querySelector('#quiz-selected-questions');

  if (quizBuilderForm && quizSummary) {
    const updateQuizBuilder = () => {
      const selectedGroups = quizBuilderForm.querySelectorAll('input[name="assigned_group_ids[]"]:checked').length;
      const selectedQuestions = Array.from(quizBuilderForm.querySelectorAll('input[name="question_ids[]"]:checked'));
      quizSummary.textContent = `${selectedQuestions.length} questions selected · ${selectedGroups} groups assigned`;

      if (quizSelectedQuestions) {
        quizSelectedQuestions.innerHTML = selectedQuestions
          .map((input) => {
            const label = input.closest('label');
            return `<p>${label ? label.textContent.trim() : 'Selected question'}</p>`;
          })
          .join('') || '<p class="muted">Select questions to build a live summary.</p>';
      }
    };

    quizBuilderForm.addEventListener('change', updateQuizBuilder);
    updateQuizBuilder();
  }

  const timer = document.querySelector('#quiz-timer');
  const quizForm = document.querySelector('[data-quiz-form]');
  const progressNode = document.querySelector('#quiz-progress');

  if (timer && quizForm) {
    let remainingSeconds = Number(timer.dataset.timerMinutes || '0') * 60;
    let autoSubmitting = false;

    const renderTimer = () => {
      const minutes = String(Math.floor(Math.max(remainingSeconds, 0) / 60)).padStart(2, '0');
      const seconds = String(Math.max(remainingSeconds, 0) % 60).padStart(2, '0');
      timer.textContent = `Time remaining: ${minutes}:${seconds}`;
    };

    const updateProgress = () => {
      if (!progressNode) {
        return;
      }

      const answered = quizForm.querySelectorAll('input[type="radio"]:checked').length;
      const total = Number(progressNode.dataset.questionCount || '0');
      progressNode.textContent = `${answered} / ${total} answered`;
    };

    renderTimer();
    updateProgress();

    const intervalId = window.setInterval(() => {
      remainingSeconds -= 1;
      renderTimer();
      if (remainingSeconds <= 0) {
        window.clearInterval(intervalId);
        autoSubmitting = true;
        quizForm.requestSubmit();
      }
    }, 1000);

    quizForm.addEventListener('change', updateProgress);
    quizForm.addEventListener('submit', (event) => {
      const questionSets = quizForm.querySelectorAll('[data-question]');
      const missing = Array.from(questionSets).filter((fieldset) => !fieldset.querySelector('input[type="radio"]:checked'));

      if (!autoSubmitting && missing.length > 0) {
        event.preventDefault();
        window.alert('Please answer every question before submitting.');
        return;
      }

      const confirmMessage = quizForm.dataset.confirmSubmit || '';
      if (!autoSubmitting && confirmMessage !== '' && !window.confirm(confirmMessage)) {
        event.preventDefault();
      }
    });
  }

  const resultsSelect = document.querySelector('#results-quiz-select');
  const resultsSummary = document.querySelector('#results-summary');
  const exportLink = document.querySelector('#results-export-link');

  if (resultsSelect && resultsSummary && exportLink) {
    resultsSelect.addEventListener('change', async () => {
      if (!resultsSelect.value) {
        resultsSummary.innerHTML = '<p class="muted">Select a quiz to load statistics.</p>';
        exportLink.setAttribute('href', '#');
        return;
      }

      resultsSummary.innerHTML = '<p class="muted">Loading statistics...</p>';
      exportLink.setAttribute('href', `/instructor/export_results.php?quiz_id=${resultsSelect.value}`);

      try {
        const response = await fetch(`${resultsSelect.dataset.resultsEndpoint}?quiz_id=${resultsSelect.value}`);
        const data = await response.json();

        if (data.error) {
          resultsSummary.innerHTML = `<p>${data.error}</p>`;
          return;
        }

        const questionItems = Object.values(data.per_question_accuracy)
          .map((detail) => `
            <li class="question-breakdown-item">
              <strong>${detail.question_text}</strong>
              <span>${detail.accuracy}% correct</span>
            </li>
          `)
          .join('');

        resultsSummary.innerHTML = `
          <p class="eyebrow eyebrow-dark">Analytics Summary</p>
          <h2>${data.quiz_title}</h2>
          <div class="stat-grid">
            <article class="stat-card stat-card-warm"><p class="stat-card-value">${data.average_score}</p><p class="stat-card-label">Average Score</p></article>
            <article class="stat-card stat-card-cool"><p class="stat-card-value">${data.submission_count}</p><p class="stat-card-label">Submissions</p></article>
            <article class="stat-card stat-card-accent"><p class="stat-card-value">${data.high_score}</p><p class="stat-card-label">Highest Score</p></article>
          </div>
          <div class="question-breakdown">
            <h3>Per-question accuracy</h3>
            <ul>${questionItems}</ul>
          </div>
        `;
      } catch (error) {
        resultsSummary.innerHTML = '<p>Unable to load statistics.</p>';
      }
    });
  }
});
