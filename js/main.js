// Zvětšení fotek v článku (šipky / Esc na klávesnici)
const zooms = [...document.querySelectorAll('.prose-body .zoom')];
if (zooms.length) {
  const box = document.createElement('dialog');
  box.className = 'lightbox';
  box.innerHTML = `
    <div class="lightbox-inner"><img alt=""><p></p></div>
    <button class="lightbox-close" aria-label="Zavřít">✕</button>
    <button class="lightbox-prev" aria-label="Předchozí fotka">←</button>
    <button class="lightbox-next" aria-label="Další fotka">→</button>`;
  document.body.append(box);
  const img = box.querySelector('img');
  const caption = box.querySelector('p');
  let current = 0;

  const show = (i) => {
    current = (i + zooms.length) % zooms.length;
    const link = zooms[current];
    const thumb = link.querySelector('img');
    img.src = link.href;
    img.alt = thumb.alt;
    caption.textContent = link.parentElement.querySelector('figcaption')?.textContent || '';
    box.querySelector('.lightbox-prev').hidden = zooms.length < 2;
    box.querySelector('.lightbox-next').hidden = zooms.length < 2;
  };

  zooms.forEach((link, i) => link.addEventListener('click', (e) => {
    e.preventDefault();
    show(i);
    box.showModal();
  }));
  box.querySelector('.lightbox-close').addEventListener('click', () => box.close());
  box.querySelector('.lightbox-prev').addEventListener('click', () => show(current - 1));
  box.querySelector('.lightbox-next').addEventListener('click', () => show(current + 1));
  box.addEventListener('click', (e) => { if (e.target.classList.contains('lightbox-inner')) box.close(); });
  box.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') show(current - 1);
    if (e.key === 'ArrowRight') show(current + 1);
  });
}

// Počítadlo návštěvnosti (bez cookies a bez ukládání IP adres), přehled je v /stats/
try {
  const local = ['localhost', '127.0.0.1'].includes(location.hostname);
  if (!local && localStorage.getItem('stats-skip') !== '1') {
    navigator.sendBeacon('stats/hit.php', new URLSearchParams({ p: location.pathname, r: document.referrer }));
  }
} catch (e) {}
