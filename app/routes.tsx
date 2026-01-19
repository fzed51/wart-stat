import { lazy } from 'react';
import type { RouteObject } from 'react-router-dom';

const Home = lazy(() => import('./pages/Home'));
const AddReport = lazy(() => import('./pages/AddReport'));
const ReportsList = lazy(() => import('./pages/ReportsList'));
const NotFound = lazy(() => import('./pages/NotFound'));

export const routes: RouteObject[] = [
  {
    path: '/',
    element: <Home />,
  },
  {
    path: '/reports',
    element: <ReportsList />,
  },
  {
    path: '/reports/add',
    element: <AddReport />,
  },
  {
    path: '*',
    element: <NotFound />,
  },
];
