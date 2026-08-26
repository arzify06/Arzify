# Arzify — Video Editing Studio Website

A responsive, multi-page static website built around the supplied Arzify logo and promotional poster.

## Pages

- Home
- About
- Services
  - Video Editing
  - Post Production
  - Ghost Editing
  - Mentoring
  - 30 Seconds Of…
- Portfolio
- Process
- Testimonials
- Blog
- Contact
- Privacy Policy
- Terms & Conditions

## Included

- Arzify logo used in navigation, footer and favicon
- Supplied Arzify promotional poster used as the main visual asset
- Dark black + rose/pink/purple visual system
- Responsive desktop/tablet/mobile navigation
- Services dropdown
- Animated technology/tool marquee
- Scroll reveal animations
- Scroll progress indicator
- Desktop cursor glow
- Hover interactions
- Reduced-motion support
- Responsive contact form
- Instagram, WhatsApp, phone and email links
- PHP `contact.php` endpoint for PHP hosting
- Browser `mailto:` fallback in `script.js`
- No build process required

## Run locally

Open `index.html` directly in a browser, or use VS Code Live Server.

## Contact email

The site is configured for:

`arzify06@gmail.com`

The static form in `script.js` uses `mailto:` so it opens the visitor's configured email application.

For automatic server-side delivery, host the site on PHP hosting and connect the form to `contact.php`. The PHP endpoint uses `mail()` and sends to `arzify06@gmail.com`. Your hosting provider must have PHP mail configured.

## Social links

Current placeholders/configured links:

- Instagram: https://www.instagram.com/arzifyy
- WhatsApp: https://wa.me/919321020031
- Phone: tel:+919321020031

Update these in the HTML if the final accounts differ.

## Replacing images

- `assets/arzify-logo.jpg` — logo
- `assets/arzify-poster.jpg` — supplied promotional poster

Replace project images in `portfolio.html` with your real portfolio thumbnails when available.

## Important publishing note

The Privacy Policy and Terms pages are starter website copy, not legal advice. Review them against your actual business practices before publishing.

### Visual & interaction updates
- Arzify wordmark uses a rose-gold/pink metallic gradient inspired by the supplied poster.
- Browser scrollbar is customized to match the black, rose-gold and pink theme.
- Navigation highlights the current page and animates the selected menu item.
- Dropdown service links receive animated active/hover states.
- Desktop cursor has a smooth rose-gold ring, glowing dot, and interactive hover expansion.
- Reduced-motion accessibility remains supported.
