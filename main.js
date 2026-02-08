
// Mobile Menu Toggle
const bar = document.getElementById('bar');
const navLinks = document.getElementById('navLinks');

if (bar && navLinks) {
    bar.onclick = () => {
        navLinks.classList.toggle('active');
    };
}

// Detail Popup Logic
const cards = document.querySelectorAll('.card');
const detailModal = document.getElementById('detailModal');
const modalContent = document.getElementById('modalContent');
const closeBtn = document.getElementById('closeBtn');

const buildDetailUrl = (el, imgOverride) => {
    const title = el.getAttribute('data-title') || '';
    const desc = el.getAttribute('data-desc') || '';
    const img = imgOverride || el.getAttribute('data-img') || el.querySelector('img')?.getAttribute('src') || '';
    return `waste_detail.html?title=${encodeURIComponent(title)}&desc=${encodeURIComponent(desc)}&img=${encodeURIComponent(img)}`;
};

const applyLearnMoreLink = (el, imgOverride, force = false) => {
    const learnMore = el.querySelector('.learn-more');
    if (!learnMore) return;
    const existingHref = (learnMore.getAttribute('href') || '').trim();
    const shouldOverride = force || existingHref === '' || existingHref === '#';
    if (shouldOverride) {
        learnMore.href = buildDetailUrl(el, imgOverride);
    }
    learnMore.addEventListener('click', (event) => {
        event.stopPropagation();
    });
};

cards.forEach(card => {
    applyLearnMoreLink(card, null, true);

    card.addEventListener('click', () => {
        const title = card.getAttribute('data-title');
        const desc = card.getAttribute('data-desc');
        const img = card.querySelector('img')?.src || '';

        if (detailModal && modalContent) {
            modalContent.innerHTML = `
                <img src="${img}" style="width:100%; border-radius:15px; margin-bottom:15px;">
                <h2>${title || ''}</h2>
                <p style="margin-top:10px; color:#666;">${desc || ''}</p>
            `;
            detailModal.style.display = 'block';
            return;
        }

        window.location.href = buildDetailUrl(card, img);
    });
});

const serviceCards = document.querySelectorAll('.service-card');
serviceCards.forEach(serviceCard => {
    applyLearnMoreLink(serviceCard);
});

if (closeBtn && detailModal) {
    closeBtn.onclick = () => {
        detailModal.style.display = 'none';
    };
}

// Close modal if clicking outside the box
window.onclick = (event) => {
    if (detailModal && event.target === detailModal) {
        detailModal.style.display = 'none';
    }
};

// ✅ Login button redirect
const loginBtn = document.getElementById('loginBtn');
if (loginBtn) {
  loginBtn.addEventListener('click', function() {
    window.location.href = "login.html";
  });
}





