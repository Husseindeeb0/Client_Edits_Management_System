import { fetchBaseQuery } from '@reduxjs/toolkit/query/react';
import type { BaseQueryFn, FetchArgs, FetchBaseQueryMeta } from '@reduxjs/toolkit/query/react';
import { toApiError } from '../errors/apiError';
import type { ApiError } from '../types/api';

const API_ORIGIN = import.meta.env.VITE_API_URL;

export const API_BASE_URL = `${API_ORIGIN}/api`;

/** Laravel's readable CSRF cookie (not the session cookie, which JavaScript cannot read). */
function getXsrfToken(): string | undefined {
  const cookie = document.cookie.split('; ').find((row) => row.startsWith('XSRF-TOKEN='));

  return cookie ? decodeURIComponent(cookie.split('=')[1]) : undefined;
}

const fetchQuery = fetchBaseQuery({
  baseUrl: API_BASE_URL,
  credentials: 'include',
  prepareHeaders: (headers) => {
    headers.set('Accept', 'application/json');

    const xsrfToken = getXsrfToken();
    if (xsrfToken) {
      headers.set('X-XSRF-TOKEN', xsrfToken);
    }

    return headers;
  },
});

/**
 * Sanctum rejects a write without a valid X-XSRF-TOKEN header with 419
 * (axios sends it automatically, fetch does not). On 419 get a fresh CSRF
 * cookie and retry once.
 */
export const baseQuery: BaseQueryFn<string | FetchArgs, unknown, ApiError, object, FetchBaseQueryMeta> = async (
  args,
  api,
  extraOptions,
) => {
  let result = await fetchQuery(args, api, extraOptions);

  if (result.error?.status === 419) {
    await fetch(`${API_ORIGIN}/sanctum/csrf-cookie`, { credentials: 'include' });
    result = await fetchQuery(args, api, extraOptions);
  }

  return result.error
    ? { error: toApiError(result.error), meta: result.meta }
    : { data: result.data, meta: result.meta };
};
