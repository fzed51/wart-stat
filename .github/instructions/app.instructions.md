---
name: app-instructions
description: "Use when: working on frontend code in ./app/ directory, creating React components, implementing pages, managing state with Zustand, or handling routing"
applyTo: "app/**"
---

# Frontend (React/TypeScript): Instructions Techniques

## Aperçu technologique

Le frontend est une **application React 19** construite avec:
- **Framework de build**: Vite v7.2.4 avec Hot Module Replacement (HMR)
- **Langage**: TypeScript 5.9 pour la sécurité des types
- **Routage**: React Router v7.11.0
- **Gestion d'état**: Zustand v4.5.2
- **Compilateur**: React Compiler pour les optimisations (activé par défaut)
- **Linting**: ESLint avec configurations TypeScript

## Structure du projet

```
./app/
├── main.tsx               # Point d'entrée React
├── App.tsx                # Composant racine
├── routes.tsx             # Configuration des routes
├── index.css              # Styles globaux
├── App.css                # Styles du composant App
├── components/            # Composants réutilisables
├── pages/                 # Pages de l'application
├── hooks/                 # Hooks personnalisés
├── stores/                # Stores Zustand (gestion d'état)
└── assets/                # Images, icons, polices
```

## Principes de développement

### 1. Composants fonctionnels avec hooks
- Tous les composants doivent être des **composants fonctionnels**
- Utilise les hooks React (`useState`, `useEffect`, `useContext`, etc.)
- **Évite** les composants classe

### 2. Gestion d'état avec Zustand
- Crée des stores Zustand pour la gestion d'état global
- Un store par domaine métier (ex: `reportStore.ts`, `authStore.ts`)
- Structure dans `./app/stores/`

```tsx
import { create } from 'zustand'

interface ReportStore {
  reports: Report[]
  addReport: (report: Report) => void
  removeReport: (id: number) => void
}

export const useReportStore = create<ReportStore>((set) => ({
  reports: [],
  addReport: (report) => set((state) => ({ reports: [...state.reports, report] })),
  removeReport: (id) => set((state) => ({ reports: state.reports.filter(r => r.id !== id) })),
}))
```

### 3. Routage avec React Router
- Routes définies dans `routes.tsx`
- Utilise `<BrowserRouter>` et `<Routes>` pour la structure
- Pages stockées dans `./app/pages/`

```tsx
// routes.tsx
import { Routes, Route } from 'react-router-dom'

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/" element={<HomePage />} />
      <Route path="/reports" element={<ReportsPage />} />
      <Route path="/reports/:id" element={<ReportDetailPage />} />
    </Routes>
  )
}
```

### 4. Composants réutilisables
- Chaque composant a une **responsabilité unique**
- Utilise les props pour la configuration
- Exporte les types TypeScript pour les props

```tsx
interface ButtonProps {
  onClick: () => void
  children: React.ReactNode
  variant?: 'primary' | 'secondary'
}

export function Button({ onClick, children, variant = 'primary' }: ButtonProps) {
  return (
    <button onClick={onClick} className={`btn btn-${variant}`}>
      {children}
    </button>
  )
}
```

### 5. Hooks personnalisés
- Encapsule la logique réutilisable dans des hooks
- Nommage: `use*` (ex: `useFetchReports`, `useLocalStorage`)
- Permet le partage de logique entre composants

```tsx
export function useFetchReports() {
  const [reports, setReports] = useState<Report[]>([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    setLoading(true)
    fetch('/api/reports')
      .then(res => res.json())
      .then(data => setReports(data))
      .finally(() => setLoading(false))
  }, [])

  return { reports, loading }
}
```

## Conventions de code

### TypeScript strict
- **Pas de `any`** - toujours typer explicitement
- Utilise les types interfaces pour les objets
- Utilise les types union pour les alternatives

```tsx
interface Report {
  id: number
  title: string
  status: 'pending' | 'completed' | 'failed'
  createdAt: Date
}

type ReportStatus = 'pending' | 'completed' | 'failed'
```

### Formatage et linting
```bash
yarn lint          # Valide avec ESLint
yarn build         # Compile et vérifie les types
```

## Communication avec le backend

### Appels API
- Centralise les appels API dans des services ou hooks
- Utilise `fetch` ou une bibliothèque HTTP

```tsx
async function fetchReport(id: number): Promise<Report> {
  const response = await fetch(`/api/reports/${id}`)
  if (!response.ok) {
    throw new Error(`Failed to fetch report: ${response.statusText}`)
  }
  return response.json()
}
```

### Gestion des erreurs
- Affiche toujours les erreurs à l'utilisateur
- Utilise un composant d'erreur centralisé si possible
- Journalise les erreurs pour le débogage

## Styling & Design System

L'application utilise un **thème hacker rétro** (Matrix/terminal).

> **📖 Voir**: [styling-theme.instructions.md](.github/instructions/styling-theme.instructions.md) pour la documentation complète sur le design system

### Principes clés
- **Palette**: Vert néon (#00ff00) sur fond noir (#0a0a0a)
- **Typographie**: Monospace uniquement (Fira Code, etc.)
- **Effets**: Glow sur titres, scanlines, bordures ASCII
- **Variables CSS**: Toujours utiliser les variables de couleur

### Couleurs principales
```css
--color-text-primary: #00ff00;      /* Vert néon */
--color-text-secondary: #00cc00;    /* Vert clair */
--color-text-muted: #008800;        /* Vert dim */
--color-bg-primary: #0a0a0a;        /* Noir */
--color-bg-card: rgba(0, 30, 0, 0.6); /* Vert transparent */
```

### Exemple de composant stylisé
```tsx
// ✅ Utilise les variables CSS
<div className="my-card" style={{ color: 'var(--color-text-primary)' }}>
  Contenu vert néon
</div>

// ❌ Ne pas hardcoder les couleurs
<div style={{ color: '#00ff00' }}>...</div>
```

## Développement

### Démarrage
```bash
yarn dev           # Démarre Vite en mode développement
yarn build         # Compile l'application
yarn build:w       # Compile et surveille les changements
yarn preview       # Prévisualise la build
```

### React Compiler
- **Activé par défaut** - améliore les performances automatiquement
- Peut impacter les performances de dev/build
- Plus d'info: [React Compiler docs](https://react.dev/learn/react-compiler)

## Optimisations

### Performance
- Utilise `React.memo()` pour mémoriser les composants si nécessaire
- Lazy loading des routes avec `React.lazy()` et `Suspense`
- Code splitting automatique avec Vite

```tsx
const ReportsPage = React.lazy(() => import('./pages/ReportsPage'))

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/reports" element={
        <Suspense fallback={<Loading />}>
          <ReportsPage />
        </Suspense>
      } />
    </Routes>
  )
}
```

## Dépannage courant

| Problème | Solution |
|----------|----------|
| Erreur TypeScript `cannot find module` | Vérifie les imports et les paths dans `tsconfig.json` |
| HMR ne fonctionne pas | Redémarre `yarn dev` |
| Erreur CORS | Configure les headers CORS côté backend |
| Zustand state non mis à jour | Vérifie que le store est utilisé avec `useStore()` |
| Build plus lent que d'habitude | Désactive temporairement React Compiler dans `vite.config.ts` |

## Points d'entrée clés

- **`main.tsx`**: Point d'entrée React
- **`App.tsx`**: Composant racine
- **`routes.tsx`**: Configuration des routes
- **`stores/`**: Gestion d'état Zustand
- **`components/`**: Composants réutilisables
- **`pages/`**: Pages de l'application

## Ressources utiles

- [React Documentation](https://react.dev/)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [React Router Documentation](https://reactrouter.com/)
- [Zustand Documentation](https://github.com/pmndrs/zustand)
- [Vite Documentation](https://vitejs.dev/)
- [ESLint Configuration](https://eslint.org/docs/rules/)
