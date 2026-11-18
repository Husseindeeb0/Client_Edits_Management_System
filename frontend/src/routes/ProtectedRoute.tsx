import { Navigate, Outlet, useLocation } from 'react-router';
import FullPageStatus from '../components/LayoutComponents/FullPageStatus';
import { getErrorMessage } from '../errors/apiError';
import { useAuth } from '../hooks/useAuth';

/** Pages for the logged-in admin. Guests go to /login and come back here after logging in. */
export default function ProtectedRoute() {
  const location = useLocation();
  const { user, isLoading, error, retry } = useAuth();

  if (isLoading) {
    return <FullPageStatus />;
  }

  if (!user) {
    // `error` means the check itself failed (e.g. server down), not "logged out"
    return error ? (
      <FullPageStatus error={getErrorMessage(error)} onRetry={retry} />
    ) : (
      <Navigate to="/login" replace state={{ from: location }} />
    );
  }

  return <Outlet />;
}
