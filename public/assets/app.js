document.addEventListener('DOMContentLoaded', () => {
  const timer = document.querySelector('#quiz-timer');
  const quizForm = document.querySelector('[data-quiz-form]');

  if (timer && quizForm) {
    let remainingSeconds = Number(timer.dataset.timerMinutes || '0') * 60;

    const renderTimer = () => {
      const minutes = String(Math.floor(Math.max(remainingSeconds, 0) / 60)).padStart(2, '0');
      const seconds = String(Math.max(remainingSeconds, 0) % 60).padStart(2, '0');
      timer.textContent = `Time remaining: ${minutes}:${seconds}`;
    };

    renderTimer();
    const intervalId = window.setInterval(() => {
      remainingSeconds -= 1;
      renderTimer();
      if (remainingSeconds <= 0) {
        window.clearInterval(intervalId);
        quizForm.submit();
      }
    }, 1000);

    quizForm.addEventListener('submit', (event) => {
      const questionSets = quizForm.querySelectorAll('[data-question]');
      const missing = Array.from(questionSets).filter((fieldset) => !fieldset.querySelector('input[type="radio"]:checked'));
      if (missing.length > 0) {
        event.preventDefault();
        alert('Please answer every question before submitting.');
      }
    });
  }

  const resultsSelect = document.querySelector('#results-quiz-select');
  const resultsSummary = document.querySelector('#results-summary');
  const exportLink = document.querySelector('#results-export-link');

  if (resultsSelect && resultsSummary && exportLink) {
    resultsSelect.addEventListener('change', async () => {
      if (!resultsSelect.value) {
        resultsSummary.innerHTML = '<p>Select a quiz to load statistics.</p>';
        exportLink.setAttribute('href', '#');
        return;
      }

      resultsSummary.innerHTML = '<p>Loading statistics...</p>';
      exportLink.setAttribute('href', `/instructor/export_results.php?quiz_id=${resultsSelect.value}`);

      try {
        const response = await fetch(`${resultsSelect.dataset.resultsEndpoint}?quiz_id=${resultsSelect.value}`);
        const data = await response.json();

        if (data.error) {
          resultsSummary.innerHTML = `<p>${data.error}</p>`;
          return;
        }

        const questionItems = Object.entries(data.per_question_accuracy)
          .map(([, detail]) => `<li>${detail.question_text}: ${detail.accuracy}% correct</li>`)
          .join('');

        resultsSummary.innerHTML = `
          <h2>${data.quiz_title}</h2>
          <p>Average score: ${data.average_score}</p>
          <p>Submission count: ${data.submission_count}</p>
          <h3>Per-question accuracy</h3>
          <ul>${questionItems}</ul>
        `;
      } catch (error) {
        resultsSummary.innerHTML = '<p>Unable to load statistics.</p>';
      }
    });
  }
});
