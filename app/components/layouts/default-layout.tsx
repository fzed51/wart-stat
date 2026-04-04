import { Suspense, type PropsWithChildren } from "react";
import { Link } from "react-router-dom";

export function DefaultLayout({children}: PropsWithChildren) {
  return (
    <>
      <nav className="main-nav">
        <Link to="/">Accueil</Link>
        <Link to="/reports">Rapports</Link>
        <Link to="/components">Composants</Link>
      </nav>
      <main>
        <Suspense fallback={<div className="page"><div className="loading">Chargement...</div></div>}>
          {children}
        </Suspense>
      </main>
    </>
  );
}