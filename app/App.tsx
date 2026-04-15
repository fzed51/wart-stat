import { useRoutes } from 'react-router-dom';
import { routes } from './routes';
import { DefaultLayout } from './components/layouts';
import { ErrorBoundary } from './components/common';
import './App.css';


export function App() {
  const element = useRoutes(routes);
  return (
    <ErrorBoundary>
      <DefaultLayout>{element}</DefaultLayout>
    </ErrorBoundary>
  );
}
