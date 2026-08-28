
const CONTACT_EMAIL = "arzify06@gmail.com";

const progress = document.querySelector(".progress");
window.addEventListener("scroll", () => {
  if (!progress) return;
  const max = document.documentElement.scrollHeight - window.innerHeight;
  progress.style.width = `${max > 0 ? (window.scrollY / max) * 100 : 0}%`;
}, {passive:true});

const menuBtn = document.querySelector(".menu-btn");
const navLinks = document.querySelector(".nav-links");

if (menuBtn && navLinks) {
  menuBtn.addEventListener("click", () => {
    const open = navLinks.classList.toggle("open");
    menuBtn.setAttribute("aria-expanded", String(open));
  });

  const navItems = [...navLinks.querySelectorAll("a")];
  const normalizePath = value => {
    const clean = value.split("#")[0].split("?")[0].replace(/\/+$/, "");
    return clean || "/";
  };
  const currentPath = normalizePath(window.location.pathname);

  navItems.forEach(a => {
    const linkPath = normalizePath(new URL(a.href, window.location.href).pathname);
    const isHome = linkPath.endsWith("/index.html") || linkPath === "/";
    const isCurrent = linkPath === currentPath || (isHome && (currentPath === "/" || currentPath.endsWith("/index.html")));
    if (isCurrent) a.classList.add("active");

    a.addEventListener("click", () => {
      navItems.forEach(item => item.classList.remove("active"));
      a.classList.add("active");
      navLinks.classList.remove("open");
      menuBtn.setAttribute("aria-expanded", "false");
    });
  });

  const dropdownButton = navLinks.querySelector(".nav-drop > button");
  const dropdownLinks = [...navLinks.querySelectorAll(".nav-drop .drop-menu a")];
  if (dropdownButton && dropdownLinks.some(a => a.classList.contains("active"))) {
    dropdownButton.classList.add("active");
  }
}

const glow = document.querySelector(".cursor-glow");
const finePointer = matchMedia("(pointer:fine)").matches;
const reducedMotion = matchMedia("(prefers-reduced-motion: reduce)").matches;

if (glow && finePointer && !reducedMotion) {
  window.addEventListener("pointermove", e => {
    glow.style.left = `${e.clientX}px`;
    glow.style.top = `${e.clientY}px`;
  }, {passive:true});
}

if (finePointer && !reducedMotion) {
  const cursorRing = document.createElement("div");
  const cursorDot = document.createElement("div");
  cursorRing.className = "cursor-ring";
  cursorDot.className = "cursor-dot";
  document.body.append(cursorRing, cursorDot);
  document.body.classList.add("has-custom-cursor");

  let mouseX = -100, mouseY = -100;
  let ringX = -100, ringY = -100;

  window.addEventListener("pointermove", e => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    cursorDot.style.transform = `translate3d(${mouseX}px,${mouseY}px,0)`;
    cursorRing.style.opacity = "1";
    cursorDot.style.opacity = "1";
  }, {passive:true});

  const animateCursor = () => {
    ringX += (mouseX - ringX) * 0.16;
    ringY += (mouseY - ringY) * 0.16;
    cursorRing.style.transform = `translate3d(${ringX}px,${ringY}px,0)`;
    requestAnimationFrame(animateCursor);
  };
  animateCursor();

  const cursorTargets = "a,button,input,textarea,select,.card,.project,.btn,.poster-frame";
  document.addEventListener("pointerover", e => {
    if (e.target.closest(cursorTargets)) cursorRing.classList.add("cursor-hover");
  });
  document.addEventListener("pointerout", e => {
    if (e.target.closest(cursorTargets)) cursorRing.classList.remove("cursor-hover");
  });
  window.addEventListener("blur", () => {
    cursorRing.style.opacity = "0";
    cursorDot.style.opacity = "0";
  });
}

const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add("visible");
      observer.unobserve(entry.target);
    }
  });
}, {threshold:.12});
document.querySelectorAll(".reveal").forEach(el => observer.observe(el));

document.querySelectorAll("[data-year]").forEach(el => el.textContent = new Date().getFullYear());

const form = document.querySelector("#contactForm");
if (form) {
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const data = new FormData(form);
    const name = `${data.get("name") || ""} ${data.get("lastName") || ""}`.trim();
    const email = data.get("email") || "";
    const subject = data.get("subject") || "New Arzify project enquiry";
    const message = data.get("message") || "";
    const source = data.get("source") || "";
    const notice = document.querySelector("#formNotice");

    if (location.protocol !== "file:") {
      try {
        const response = await fetch("contact.php", { method: "POST", body: data });
        const result = await response.json();
        if (response.ok && result.ok) {
          if (notice) {
            notice.style.display = "block";
            notice.textContent = result.message;
          }
          form.reset();
          return;
        }
      } catch (err) {}
    }

    const body = `Name: ${name}\nEmail: ${email}\nHow they found Arzify: ${source}\n\n${message}`;
    const mailto = `mailto:${CONTACT_EMAIL}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    if (notice) {
      notice.style.display = "block";
      notice.textContent = `Opening your email app with the enquiry addressed to ${CONTACT_EMAIL}.`;
    }
    window.location.href = mailto;
  });
}

const year = new Date().getFullYear();
document.title = document.title.replace("{{YEAR}}", year);


// Back to top button
const backToTop = document.querySelector("#backToTop");
if (backToTop) {
  const toggleBackToTop = () => {
    backToTop.classList.toggle("show", window.scrollY > 450);
  };
  toggleBackToTop();
  window.addEventListener("scroll", toggleBackToTop, {passive:true});

  backToTop.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth"
    });
  });
}
