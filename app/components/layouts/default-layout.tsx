import { Suspense, type PropsWithChildren } from "react";
import { Link } from "react-router-dom";

export function DefaultLayout({children}: PropsWithChildren) {
  return (
    <>
      <nav className="main-nav">
        <Link to="/">Accueil</Link>
      </nav>
      <main className="page">
        <Suspense fallback={<div className="loading">Chargement</div>}>
          {children}
        </Suspense>
      </main>
    </>
  );
}