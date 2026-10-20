// Abstimmen ohne Neuladen der Seite. Ohne JavaScript funktionieren die Formulare trotzdem (mit Neuladen).
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!form.classList.contains('vote-form')) {
        return;
    }
    event.preventDefault();

    const data = new FormData(form, event.submitter);   // enthält value=1 oder -1 vom geklickten Pfeil

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: data,
            headers: { Accept: 'application/json' },
        });
        const result = await response.json();

        if (response.status === 401) {
            window.location.href = result.login;
            return;
        }
        if (!response.ok) {
            throw new Error(result.error);
        }

        form.querySelector('.score').textContent = result.score;
        form.classList.toggle('voted-up', result.myVote === 1);
        form.classList.toggle('voted-down', result.myVote === -1);
        const up = form.querySelector('.vote-up');
        const down = form.querySelector('.vote-down');
        up.setAttribute('aria-pressed', result.myVote === 1);
        down.setAttribute('aria-pressed', result.myVote === -1);
        up.title = result.myVote === 1 ? 'Upvote zurücknehmen' : 'Upvote';
        down.title = result.myVote === -1 ? 'Downvote zurücknehmen' : 'Downvote';
    } catch (error) {
        console.error('Abstimmen fehlgeschlagen:', error);
        window.location.reload();
    }
});
