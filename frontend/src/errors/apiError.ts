import type { SerializedError } from '@reduxjs/toolkit';
import type { FetchBaseQueryError } from '@reduxjs/toolkit/query';
import type { ApiError } from '../types/api';

const NETWORK_ERROR = 'Could not reach the server. Check your connection and try again.';
const SERVER_ERROR = 'Something went wrong on the server. Please try again.';

/** The `error` an RTK Query hook returns. */
export type QueryError = ApiError | SerializedError | undefined;

/** Turn a failed request into `{ status, message, errors }`, so every page handles errors the same way. */
export function toApiError(error: FetchBaseQueryError): ApiError {
  // No HTTP response (server down), or a response that was not JSON
  if (typeof error.status !== 'number') {
    return error.status === 'PARSING_ERROR'
      ? { status: error.originalStatus, message: SERVER_ERROR }
      : { status: 0, message: NETWORK_ERROR };
  }

  const { status } = error;
  const body = (error.data ?? {}) as { message?: string; errors?: Record<string, string[]> };

  // Never show server exception text to the user
  if (status >= 500) {
    return { status, message: SERVER_ERROR };
  }

  // Laravel's 404 message names the model class, which is not meant for users
  if (status === 404) {
    return { status, message: 'The requested item was not found.' };
  }

  return { status, message: body.message ?? `Request failed (${status}).`, errors: body.errors };
}

export function isApiError(error: QueryError): error is ApiError {
  return error !== undefined && 'status' in error;
}

export function getErrorMessage(error: QueryError): string {
  return error?.message ?? 'Something went wrong. Please try again.';
}

/** The first message of each field from a 422 response, e.g. `{ email: 'The email field is required.' }`. */
export function getFieldErrors(error: QueryError): Record<string, string> {
  if (!isApiError(error) || !error.errors) {
    return {};
  }

  return Object.fromEntries(Object.entries(error.errors).map(([field, messages]) => [field, messages[0]]));
}
