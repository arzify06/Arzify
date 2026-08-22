# Vipul Shekhva — Developer Portfolio

Modern responsive portfolio built with **HTML, CSS and JavaScript**.

## Included features

- Floating profile card using `assets/profile.jpg`
- Orbit animations around the profile
- Animated technology marquee
- Scroll-reveal effects
- Skills / toolkit cards
- TRUEBANK project section
- DESIRE TRAVELS project section
- Bus Travellers project
- Portfolio project
- Education journey: **BCA 2023–2026 → MCA → Future: Developer**
- Responsive mobile navigation
- Scroll progress bar
- Cursor glow
- Hover animations
- Responsive contact form
- LinkedIn / Instagram / WhatsApp / Phone placeholders
- Modern responsive navigation
- Reduced-motion accessibility support

## Folder structure

```text
portfolio/
├── index.html
├── style.css
├── script.js
├── README.md
└── assets/
    └── profile.jpg
```

## Profile photo

Create an `assets` folder beside `index.html` and place your photo at:

`assets/profile.jpg`

The portfolio already points to that exact path.

## Contact email setup

The contact form currently uses the browser's `mailto:` functionality.

Open `script.js` and replace:

`const CONTACT_EMAIL="YOUR_EMAIL@example.com";`

with the email address where you want to receive messages.

Example:

`const CONTACT_EMAIL="yourname@gmail.com";`

### Important

A pure HTML/CSS/JS website cannot securely send email directly from a browser without an email service or backend. The current `mailto:` setup opens the visitor's configured email application.

For automatic server-side delivery without opening an email app, connect the form to a service such as Formspree, Web3Forms, EmailJS, or your own PHP mail endpoint.

## Social links

Update the placeholder links in `index.html`:

- LinkedIn: `https://www.linkedin.com/`
- Instagram: `https://www.instagram.com/`
- WhatsApp: `https://wa.me/`
- Phone: `tel:+910000000000`

Replace them with your real profile/phone links.

## Run locally

1. Put all files in one project folder.
2. Add `assets/profile.jpg`.
3. Set `CONTACT_EMAIL` in `script.js`.
4. Open `index.html` in a browser.

For a better local development experience, use VS Code + Live Server.

## Free static hosting

This project can be hosted as a static site on GitHub Pages, Netlify, or Cloudflare Pages.

If you later add PHP, a database, or a server-side email endpoint, use PHP-compatible hosting instead.

## Customization

Main visual settings are at the top of `style.css`:

- `--accent` — neon green
- `--cyan` — cyan highlight
- `--bg` — page background
- `--card` — card background
- `--display` — heading font
- `--mono` — body/code font

Project text, social URLs and education details are in `index.html`.


## Latest customizations

- Global `h1`–`h6` typography uses `var(--display)` with 700 weight and 1.1 line-height.
- Hero slide includes a downloadable resume button and LinkedIn, WhatsApp, Email, Instagram and Phone icons.
- Contact details appear immediately before the contact form with Lucide map-pin, phone and mail icons.
- Contact social cards use the supplied LinkedIn, Instagram, WhatsApp and phone destinations.
- Contact form recipient is configured as `jigneshrathod1102@gmail.com` in `script.js`.
- Resume button points to `https://jigneshrathod.vercel.app/jignesh_rathod_flutter_developer_resume-2.pdf`.


## Resume

The uploaded resume is included in `assets/resume.pdf`. The hero **Download Resume** button points to this local file.

## Social icon styling

Hero social icons are white by default, have visible spacing between each icon, and turn accent-colored on hover.
