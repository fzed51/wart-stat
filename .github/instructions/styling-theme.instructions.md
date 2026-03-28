---
name: styling-theme-instructions
description: "Use when: styling components, working with CSS, creating new pages, or maintaining visual consistency with the hacker/matrix theme"
applyTo: "app/**/*.{css,tsx}"
---

# Design System & Styling: Hacker Theme

## Identité visuelle

**Wart-Stat** utilise un **thème hacker rétro** inspiré de l'esthétique Matrix et des terminaux vintage. L'objectif est de créer une interface futuriste, brute et immersive.

### Palette de couleurs

#### Couleurs principales
```css
:root {
  /* Backgrounds */
  --color-bg-primary: #0a0a0a;           /* Noir quasi total */
  --color-bg-secondary: #0d1a0d;         /* Vert très sombre */
  --color-bg-tertiary: #121f12;          /* Vert foncé */
  --color-bg-card: rgba(0, 30, 0, 0.6);  /* Vert semi-transparent */
  --color-bg-input: rgba(0, 20, 0, 0.8); /* Input dark vert */
  
  /* Verts (couleur signature) */
  --color-green-primary: #00ff00;     /* Vert néon brillant */
  --color-green-light: #33ff33;       /* Vert clair */
  --color-green-glow: #00ff0080;      /* Glow (transparency 50%) */
  --color-green-dim: #00aa00;         /* Vert moyen */
  --color-green-muted: #008800;       /* Vert éteint */
  
  /* Texte */
  --color-text-primary: #00ff00;      /* Texte principal vert néon */
  --color-text-secondary: #00cc00;    /* Texte secondaire */
  --color-text-muted: #008800;        /* Texte discret */
  --color-text-dim: #006600;          /* Texte très discret */
  
  /* Bordures */
  --color-border: #00ff0040;          /* Bordures vert semi-transparent */
  --color-border-hover: #00ff00;      /* Bordures au survol */
  --color-border-focus: #33ff33;      /* Bordures au focus */
  
  /* États */
  --color-error: #ff3333;             /* Erreurs rouge */
  --color-error-bg: rgba(255, 0, 0, 0.1);
  --color-success: #00ff00;           /* Succès vert */
  --color-warning: #ffff00;           /* Avertissements jaune */
}
```

### Typographie

- **Police**: Monospace uniquement
  - Primoraire: `Fira Code`
  - Fallbacks: `SF Mono`, `Monaco`, `Inconsolata`, `Roboto Mono`, `Courier New`
  
```css
font-family: 'Fira Code', 'SF Mono', 'Monaco', 'Inconsolata', 
             'Roboto Mono', 'Source Code Pro', 'Courier New', monospace;
```

- **Tailles**: Petit et compact (14px base)
- **Poids**: 400 régulier, 600 pour les titres
- **Interligne**: 1.6

### Effets visuels signature

#### 1. Text Glow (sur les titres)
```css
h1, h2, h3 {
  text-shadow: 0 0 10px var(--color-green-glow), 0 0 20px var(--color-green-glow);
}
```

#### 2. Scanlines (effet écran CRT)
```css
body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: repeating-linear-gradient(
    0deg,
    rgba(0, 0, 0, 0.15),
    rgba(0, 0, 0, 0.15) 1px,
    transparent 1px,
    transparent 2px
  );
  pointer-events: none;
  z-index: 9999;
}
```

#### 3. Bordures & Box-shadows
```css
/* Bordures discrètes par défaut */
border: 1px solid var(--color-border);

/* Brillantes au survol/focus */
border-color: var(--color-border-hover);
box-shadow: 0 0 8px var(--color-green-glow);
```

#### 4. Dégradés (arrière-plans)
```css
body {
  background: 
    radial-gradient(ellipse at top, var(--color-bg-secondary) 0%, transparent 50%),
    radial-gradient(ellipse at bottom, var(--color-bg-tertiary) 0%, transparent 50%),
    var(--color-bg-primary);
  background-attachment: fixed;
}
```

## Composants & classes CSS

### Navigation principale
```css
.main-nav {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid var(--color-border);
}

.main-nav::before {
  content: 'WART-STAT://';           /* Préfixe "terminal" */
  color: var(--color-text-muted);
  font-size: 0.75rem;
  letter-spacing: 0.1em;
}
```

### En-têtes de page
```css
.page-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--color-border);
}

.page-header h1 {
  color: var(--color-text-primary);
  text-shadow: 0 0 10px var(--color-green-glow);
}
```

### Accueil (Hero section)
```css
.home-page {
  text-align: center;
  padding-top: 4rem;
}

.home-subtitle {
  color: var(--color-text-muted);
  font-size: 0.9rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

.home-subtitle::before {
  content: '// ';                   /* Commentaire de code */
}
```

### Conteneurs "terminal"
```css
.home-terminal {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  padding: 2rem;
  margin: 3rem auto;
}

.home-terminal::before {
  content: '┌──[ SYSTEM STATUS ]';  /* Bordure ASCII art */
  display: block;
  color: var(--color-text-muted);
}
```

### Formulaires
```css
input, textarea, select {
  background: var(--color-bg-input);
  border: 1px solid var(--color-border);
  color: var(--color-text-primary);
  font-family: inherit;
  padding: 0.75rem;
}

input:focus, textarea:focus, select:focus {
  outline: none;
  border-color: var(--color-border-focus);
  box-shadow: 0 0 10px var(--color-green-glow);
}
```

### Messages d'erreur
```css
.error-message {
  background: var(--color-error-bg);
  border: 1px solid var(--color-error);
  color: var(--color-error);
  padding: 1rem;
  margin-bottom: 1rem;
}
```

## Convenctions de styling

### 1. Hiérarchie des couleurs de texte
```
Primary (#00ff00)     → Contenu principal, titres
Secondary (#00cc00)   → Texte secondaire, items importants
Muted (#008800)       → Métadonnées, petits textes
Dim (#006600)         → Pseudo-contenus (::before, ::after)
```

### 2. États interactifs
```css
/* Hover = bordure vive + glow */
element:hover {
  border-color: var(--color-border-hover);
  box-shadow: 0 0 8px var(--color-green-glow);
}

/* Focus = bordure claire + glow intense */
element:focus {
  border-color: var(--color-border-focus);
  box-shadow: 0 0 12px var(--color-green-glow);
}

/* Active = sans changement majeur */
element:active {
  opacity: 0.8;
}
```

### 3. Utiliser les variables CSS
```css
/* ✅ OUI - Cohérent et maintenable */
color: var(--color-text-primary);
background: var(--color-bg-card);
border: 1px solid var(--color-border);

/* ❌ NON - Hardcoder les couleurs */
color: #00ff00;
background: rgba(0, 30, 0, 0.6);
```

### 4. ASCII Art & pseudo-contenus
L'app utilise des `::before` et `::after` pour ajouter du caractère terminal:

```css
/* Exemplaires existants */
.main-nav::before       { content: 'WART-STAT://'; }
.home-subtitle::before  { content: '// '; }
.home-terminal::before  { content: '┌──[ SYSTEM STATUS ]'; }

/* Pour les nouveaux éléments */
.card::before           { content: '[●●●]'; }  /* Indicateurs */
.section-title::before  { content: '>>> '; }   /* Prompteurs */
```

### 5. Spacing & Layout
- **Padding/Margin**: Multiples de 0.5rem
- **Gaps (flex)**: 1rem pour l'espacement normal
- **Padding conteneurs**: 1-2rem selon le contexte
- **Max-width**: 1000px pour le contenu (#root)

## Exemple de composant stylisé

```tsx
// MyComponent.tsx
export function MyComponent() {
  return (
    <div className="card">
      <h2>Component Title</h2>
      <p>Description text goes here...</p>
    </div>
  )
}
```

```css
/* MyComponent.css */
.card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.card:hover {
  border-color: var(--color-border-hover);
  box-shadow: 0 0 8px var(--color-green-glow);
}

.card h2 {
  color: var(--color-text-primary);
  text-shadow: 0 0 10px var(--color-green-glow);
  margin-top: 0;
}

.card p {
  color: var(--color-text-secondary);
}

.card::before {
  content: '[●]';
  display: inline-block;
  margin-right: 0.5rem;
  color: var(--color-text-muted);
}
```

## Points clés à retenir

✓ **Monospace partout** - Police de caractères unique et cohérente  
✓ **Vert néon** - C'est la couleur signature (#00ff00)  
✓ **Glow sur les titres** - Effet text-shadow pour immersion  
✓ **Scanlines toujours visibles** - Effet CRT fixe sur la page  
✓ **ASCII art discret** - Caractères spéciaux dans les `::before/::after`  
✓ **Variables CSS** - Jamais hardcoder les couleurs  
✓ **Transitions subtiles** - Hover/Focus doivent accentuer le glow  
✓ **Accessibilité** - Assurer un contraste suffisant même avec le thème sombre

## Ressources

- [CSS Variables (Custom Properties)](https://developer.mozilla.org/en-US/docs/Web/CSS/--*)
- [Box Shadow & Glow Effects](https://developer.mozilla.org/en-US/docs/Web/CSS/box-shadow)
- [Text Shadow](https://developer.mozilla.org/en-US/docs/Web/CSS/text-shadow)
- [Pseudo-elements ::before & ::after](https://developer.mozilla.org/en-US/docs/Web/CSS/::before)
