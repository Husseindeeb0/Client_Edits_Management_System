import { useMeQuery } from '../client/authApi';

/** The logged-in admin (`null` when logged out), from GET /auth/me. */
export function useAuth() {
  const { data: user = null, isLoading, error, refetch } = useMeQuery();

  return { user, isAuthenticated: user !== null && !error, isLoading, error, retry: refetch };
}
