# Federico Justian Wijono — Portfolio

A responsive developer portfolio built with plain HTML, CSS, and JavaScript.

## Structure

- `index.html` — semantic page structure and portfolio content[cite: 14]
- `styles.css` — visual system, layout, responsive rules, and animations[cite: 14]
- `script.js` — mobile navigation, moving active-section indicator, back-to-top, role typing, cursor glow, interactive DNA canvas, scroll reveal, and future CRUD data hooks[cite: 14]
- `assets/fico-profile.png` — profile photo used in the About section[cite: 14]
- `assets/exp-photo_1.png` to `exp-photo_4.png` — experience landscape photocard images
- `assets/Federico_Justian_Wijono_CV.pdf` — local CV download used by the Home button[cite: 14]
- `assets/E-Sertif_Nvidia-Fundamental_DL.pdf` — NVIDIA Fundamentals of Deep Learning certificate[cite: 14]
- `assets/logo-web.svg` — custom browser favicon

## Run locally

Open `index.html` directly in a browser, or use VS Code Live Server[cite: 14].

## Updating the portfolio

### Domain / brand

The public brand is `federicojw.com`. Replace it with your actual deployed domain if needed[cite: 14].

### Social links

Edit the social/contact anchors in `index.html` if your URLs change[cite: 14].

### CV

Replace `assets/Federico_Justian_Wijono_CV.pdf` with your latest CV while keeping the same filename, or update the `href` on the Download CV button[cite: 14].

### Experience & Photocards

The Experience section uses a continuous vertical timeline paired with custom 16:9 landscape photocard slots (`assets/exp-photo_1.png` through `assets/exp-photo_4.png`). Replace or update these images in the `assets/` folder to match your experience items.

### Credentials

The Credentials section includes the NVIDIA Fundamentals of Deep Learning certificate with a compact **View certificate** button[cite: 14]. Replace the PDF or update the link when adding future credentials[cite: 14].

### Projects

The Projects section uses reusable cards for live and private projects[cite: 14]. Duplicate a `.project-card` and edit its content when adding a project[cite: 14].

## Design direction

Dark developer/editor aesthetic inspired by VS Code, using cyan/blue accents, monospace UI details, subtle grid texture, and restrained motion[cite: 14].

## Interaction layer

- The hero role cycles through Software Engineer, Front-End Developer, Web Developer, and SQL & Database Learner with a type/delete animation[cite: 14].
- Desktop pointer devices get a restrained cursor-following glow[cite: 14].
- The background includes a lightweight DNA-like canvas[cite: 14].
- Smooth scroll progress bar at the top of the viewport and fluid back-to-top navigation.
- Scroll reveals use IntersectionObserver and respect `prefers-reduced-motion`[cite: 14].
- Touch/coarse-pointer devices disable the cursor and DNA canvas effects[cite: 14].

## Future admin / CRUD readiness

Existing project, credential, skill, and experience records have stable `data-entity` / `data-id` hooks, and `script.js` contains data schema descriptions ready for a future admin/CRUD application[cite: 14].
