document.addEventListener("DOMContentLoaded", () => {

  // ----------------- SCROLL ANIMATIONS -----------------
  const scrollElements = document.querySelectorAll(".animate-on-scroll");
  if (scrollElements.length) {
    const elementInView = (el, dividend = 1) => {
      const elementTop = el.getBoundingClientRect().top;
      return (elementTop <= (window.innerHeight || document.documentElement.clientHeight) / dividend);
    };
    const displayScrollElement = (el) => el.classList.add("is-visible");
    const handleScrollAnimation = () => {
      scrollElements.forEach(el => { if (elementInView(el,1.25)) displayScrollElement(el); });
    };
    handleScrollAnimation();
    window.addEventListener("scroll", handleScrollAnimation);
  }

  // ----------------- HEADER SCROLL EFFECT -----------------
  const header = document.getElementById('mainHeader');
  if (header) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
    });
  }

  // ----------------- MOBILE NAV -----------------
  const mobileClose = document.getElementById('mobileClose');
  if (mobileClose) {
    mobileClose.addEventListener('click', () => toggleMobileMenu(false));
  }

  const navLinks = document.querySelectorAll('#mainNav a');
  navLinks.forEach(link => {
    link.addEventListener('click', () => { if (window.innerWidth <= 992) toggleMobileMenu(false); });
  });

  function toggleMobileMenu(forceClose=false) {
    const mainNav = document.getElementById('mainNav');
    const mobileToggle = document.getElementById('mobileToggle');
    if (!mainNav || !mobileToggle) return;
    if(forceClose) {
      mainNav.classList.remove('active');
      mobileToggle.classList.remove('active');
    } else {
      mainNav.classList.toggle('active');
      mobileToggle.classList.toggle('active');
    }
  }

  // ----------------- ACCORDION -----------------
  const accordionItems = document.querySelectorAll(".accordion-item-v4");
  accordionItems.forEach(item => {
    const header = item.querySelector(".accordion-header-v4");
    if(!header) return;
    header.addEventListener("click", () => {
      accordionItems.forEach(other => {
        if(other!==item) {
          other.classList.remove("active");
          const content = other.querySelector(".accordion-content-v4");
          if(content) content.style.maxHeight = null;
        }
      });
      const content = item.querySelector(".accordion-content-v4");
      item.classList.toggle("active");
      if(content) content.style.maxHeight = item.classList.contains("active") ? content.scrollHeight + "px" : null;
    });
  });
});

// ----------------- AUTH MODAL -----------------
function getAuthElements() {
  return {
    authModal: document.getElementById('authModal'),
    loginForm: document.getElementById('loginForm'),
    signupForm: document.getElementById('signupForm')
  };
}

function openLoginModal() {
  const { authModal, loginForm, signupForm } = getAuthElements();
  if(!authModal) return;
  authModal.classList.add('active');
  if(loginForm) loginForm.style.display='block';
  if(signupForm) signupForm.style.display='none';
}

// Helper to ensure modal opens and focuses the first input; safe to call from other pages
function ensureAuthModal() {
  openLoginModal();
  const email = document.getElementById('loginEmailModal');
  if (email) {
    setTimeout(() => email.focus(), 300);
  }
}

function openSignupModal() {
  const { authModal, loginForm, signupForm } = getAuthElements();
  if(!authModal) return;
  authModal.classList.add('active');
  if(loginForm) loginForm.style.display='none';
  if(signupForm) signupForm.style.display='block';
}

function closeAuthModal() {
  const { authModal } = getAuthElements();
  if(!authModal) return;
  authModal.classList.remove('active');
}

function switchToSignup(event){
  event.preventDefault();
  const { loginForm, signupForm } = getAuthElements();
  if(loginForm) loginForm.style.display='none';
  if(signupForm) signupForm.style.display='block';
}

function switchToLogin(event){
  event.preventDefault();
  const { loginForm, signupForm } = getAuthElements();
  if(loginForm) loginForm.style.display='block';
  if(signupForm) signupForm.style.display='none';
}

// Close modal on Escape
window.addEventListener('keydown', (e) => { if(e.key==='Escape') closeAuthModal(); });
