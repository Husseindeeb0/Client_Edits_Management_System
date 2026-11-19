import { Navigate, useLocation } from 'react-router';
import type { Location } from 'react-router';
import LoginForm from '../../components/AuthComponents/LoginForm';
import FullPageStatus from '../../components/LayoutComponents/FullPageStatus';
import { useAuth } from '../../hooks/useAuth';

export default function LoginPage() {
  const location = useLocation();
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return <FullPageStatus />;
  }

  // Already logged in, or just logged in: go back to the page that was asked for
  if (isAuthenticated) {
    const from = (location.state as { from?: Location } | null)?.from;

    return <Navigate to={from ? `${from.pathname}${from.search}` : '/'} replace />;
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-950 px-4">
      <div className="w-full max-w-sm rounded-lg border border-slate-800 bg-slate-900 p-6 shadow-sm">
        <h1 className="text-xl font-semibold text-slate-100">Client Edits</h1>
        <p className="mt-1 mb-6 text-sm text-slate-400">Log in to manage clients and their edits.</p>
        <LoginForm />
      </div>
    </div>
  );
}
