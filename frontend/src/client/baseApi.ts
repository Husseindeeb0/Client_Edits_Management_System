import { createApi } from '@reduxjs/toolkit/query/react';
import { baseQuery } from '../lib/baseQuery';
import type { Paginated } from '../types/api';

export const LIST_ID = 'LIST';

// A 401 means the session expired: refetch "me" (it becomes null) so ProtectedRoute redirects to /login
const baseQueryWithLogout: typeof baseQuery = async (args, api, extraOptions) => {
  const result = await baseQuery(args, api, extraOptions);

  if (result.error?.status === 401 && api.endpoint !== 'me') {
    api.dispatch(baseApi.util.invalidateTags(['Auth']));
  }

  return result;
};

/** One API for the app; each feature adds its endpoints in its own file with `injectEndpoints`. */
export const baseApi = createApi({
  reducerPath: 'api',
  baseQuery: baseQueryWithLogout,
  tagTypes: ['Auth', 'Client', 'ClientSummary', 'Company', 'Project', 'Edit', 'Attachment'],
  endpoints: () => ({}),
});

/** For infinite (scroll) lists: the page after the last one loaded, or `undefined` when there are no more. */
export function nextPage({ meta }: Paginated<unknown>) {
  return meta.current_page < meta.last_page ? meta.current_page + 1 : undefined;
}

/** Tags for a list: one per item, plus 'LIST' so creating or deleting an item refreshes the list. */
export function listTags<T extends string>(type: T, items: { id: number }[] = []) {
  return [...items.map(({ id }) => ({ type, id })), { type, id: LIST_ID }];
}
