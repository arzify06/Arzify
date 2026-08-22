document.addEventListener("DOMContentLoaded",()=>{
  const navbar=document.getElementById("navbar"), progress=document.getElementById("scroll-progress");
  const mobileToggle=document.getElementById("mobile-toggle"), mobileNav=document.getElementById("mobile-nav");
  const toast=document.getElementById("toast"), glow=document.getElementById("cursor-glow");

  // 1) Scroll progress + navbar state
  const updateScroll=()=>{
    navbar.classList.toggle("scrolled",window.scrollY>40);
    const max=document.documentElement.scrollHeight-document.documentElement.clientHeight;
    progress.style.width=(max?window.scrollY/max*100:0)+"%";
  };
  window.addEventListener("scroll",updateScroll,{passive:true}); updateScroll();

  // 2) Responsive mobile navigation
  mobileToggle.addEventListener("click",()=>{
    const open=mobileNav.classList.toggle("active");
    mobileToggle.textContent=open?"✕":"☰"; mobileToggle.setAttribute("aria-expanded",open);
  });
  document.querySelectorAll(".mobile-link").forEach(link=>link.addEventListener("click",()=>{
    mobileNav.classList.remove("active"); mobileToggle.textContent="☰"; mobileToggle.setAttribute("aria-expanded","false");
  }));

  // 3) Scroll reveal
  const revealObserver=new IntersectionObserver(entries=>entries.forEach(entry=>{
    if(entry.isIntersecting){entry.target.classList.add("visible");revealObserver.unobserve(entry.target);}
  }),{threshold:.12});
  document.querySelectorAll(".reveal-on-scroll").forEach(el=>revealObserver.observe(el));

  // 4) Smooth anchor navigation
  document.querySelectorAll('a[href^="#"]').forEach(anchor=>anchor.addEventListener("click",e=>{
    const target=document.querySelector(anchor.getAttribute("href"));
    if(target){e.preventDefault();target.scrollIntoView({behavior:"smooth",block:"start"});}
  }));

  // 5) Cursor glow
  window.addEventListener("pointermove",e=>{
    if(glow){glow.style.left=e.clientX+"px";glow.style.top=e.clientY+"px";}
  },{passive:true});

  // 6) Active navigation link
  const sections=[...document.querySelectorAll("main section[id]")];
  const navLinks=[...document.querySelectorAll(".nav-links a")];
  const navObserver=new IntersectionObserver(entries=>entries.forEach(entry=>{
    if(entry.isIntersecting){
      navLinks.forEach(a=>a.classList.toggle("active",a.getAttribute("href")==="#"+entry.target.id));
    }
  }),{rootMargin:"-35% 0px -55% 0px"});
  sections.forEach(s=>navObserver.observe(s));

  // 7) Contact form: simple no-backend email delivery using mailto.
  // Portfolio contact recipient.
  const CONTACT_EMAIL="shekhvavipul21@gmail.com";
  const form=document.getElementById("contactForm");
  form.addEventListener("submit",e=>{
    e.preventDefault();
    const name=form.fullName.value.trim(), email=form.email.value.trim();
    const subject=form.subject.value.trim()||"Portfolio Contact";
    const message=form.message.value.trim();
    if(!name||!email||!message){showToast("Please fill all required fields.");return;}
        const body=`Name: ${name}\nEmail: ${email}\n\n${message}`;
    window.location.href=`mailto:${CONTACT_EMAIL}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    form.reset(); showToast("Opening your email app…");
  });
  function showToast(text){toast.textContent=text;toast.classList.add("show");setTimeout(()=>toast.classList.remove("show"),3500);}
});