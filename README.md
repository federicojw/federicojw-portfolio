# Federico Justian Wijono — Portfolio

A responsive developer portfolio built with plain HTML, CSS, and JavaScript.

## Structure

- `index.html` — semantic page structure and portfolio content
- `projects-portofolio.html` — dedicated project detail page for the portfolio website
- `projects-percava.html` — dedicated project detail page for the Percava web platform
- `projects-attendance.html` — dedicated project detail page for the attendance tracking system
- `projects-template.html` — reusable template file for generating new project detail pages
- `styles.css` — visual system, layout, responsive rules, animations, continuous gear spin, and theme toggling
- `script.js` — mobile navigation, moving active-section indicator, back-to-top, role typing, cursor glow, interactive DNA canvas, scroll reveal, and theme persistence
- `assets/fico-profile.png` — profile photo used in the About section
- `assets/exp-photo_1.png` to `assets/exp-photo_4.png` — experience landscape photocard images
- `assets/Federico_Justian_Wijono_CV.pdf` — local CV download used by the Home button
- `assets/E-Sertif_Nvidia-Fundamental_DL.pdf` — NVIDIA Fundamentals of Deep Learning certificate
- `assets/logo-web.svg` — custom browser favicon with versioning (`?v=2`)

## Run locally

Open `index.html` directly in a browser, or use VS Code Live Server.

## Updating the portfolio

### Domain / brand

The public brand is `federicojw.my.id`. Replace it with your actual deployed domain if needed.

### Social links

Edit the social/contact anchors in `index.html` if your URLs change.

### CV

Replace `assets/Federico_Justian_Wijono_CV.pdf` with your latest CV while keeping the same filename, or update the `href` on the Download CV button.

### Experience & Photocards

The Experience section uses a continuous vertical timeline paired with custom 16:9 landscape photocard slots (`assets/exp-photo_1.png` through `assets/exp-photo_4.png`). Replace or update these images in the `assets/` folder to match your experience items.

### Credentials

The Credentials section includes professional certificates with compact **View certificate** buttons. Replace the PDFs or update the links when adding future credentials.

### Projects & Reusable Template

The Projects section includes live and system projects. To add a new project detail page, duplicate `projects-template.html`, rename it, update the content, and link it from `index.html`.

## Design direction

Dark developer/editor aesthetic inspired by VS Code, using cyan/blue accents, monospace UI details, subtle grid texture, continuous gear rotation, and restrained motion.

## Interaction layer

- The hero role cycles through Software Engineer, Front-End Developer, Web Developer, and SQL & Database Learner with a type/delete animation.
- Desktop pointer devices get a restrained cursor-following glow.
- The background includes a lightweight DNA-like canvas.
- Smooth scroll progress bar at the top of the viewport and fluid back-to-top navigation.
- Scroll reveals use IntersectionObserver and respect `prefers-reduced-motion`.
- Touch/coarse-pointer devices disable the cursor and DNA canvas effects.

## Future admin / CRUD readiness

Existing project, credential, skill, and experience records have stable `data-entity` / `data-id` hooks, and `script.js` contains data schema descriptions ready for a future admin/CRUD application.