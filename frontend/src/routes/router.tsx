import { createBrowserRouter, Navigate } from 'react-router';
import MainLayout from '../components/LayoutComponents/MainLayout';
import AttachmentPage from '../pages/AttachmentsDashboard/attachmentPage';
import LoginPage from '../pages/Auth/loginPage';
import ClientDetailsPage from '../pages/ClientsDashboard/clientDetailsPage';
import ClientPage from '../pages/ClientsDashboard/clientPage';
import CompanyDetailsPage from '../pages/CompaniesDashboard/companyDetailsPage';
import CompanyPage from '../pages/CompaniesDashboard/companyPage';
import EditDetailsPage from '../pages/EditsDashboard/editDetailsPage';
import EditPage from '../pages/EditsDashboard/editPage';
import NotFoundPage from '../pages/notFoundPage';
import ProjectDetailsPage from '../pages/ProjectsDashboard/projectDetailsPage';
import ProjectPage from '../pages/ProjectsDashboard/projectPage';
import ProtectedRoute from './ProtectedRoute';

export const router = createBrowserRouter([
  { path: '/login', element: <LoginPage /> },
  {
    path: '/',
    element: <ProtectedRoute />,
    children: [
      {
        element: <MainLayout />,
        children: [
          { index: true, element: <Navigate to="/clients" replace /> },
          { path: 'clients', element: <ClientPage /> },
          { path: 'clients/:clientId', element: <ClientDetailsPage /> },
          { path: 'companies', element: <CompanyPage /> },
          { path: 'companies/:companyId', element: <CompanyDetailsPage /> },
          { path: 'projects', element: <ProjectPage /> },
          { path: 'projects/:projectId', element: <ProjectDetailsPage /> },
          { path: 'edits', element: <EditPage /> },
          { path: 'edits/:editId', element: <EditDetailsPage /> },
          { path: 'attachments', element: <AttachmentPage /> },
          { path: '*', element: <NotFoundPage /> },
        ],
      },
    ],
  },
]);
