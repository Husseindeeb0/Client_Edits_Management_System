/**
 * Shapes shared by every endpoint of the Laravel API.
 */

/** A single API Resource response: `{ data: T }`. */
export interface Resource<T> {
  data: T;
}

/** A response that only carries a message, e.g. after a delete. */
export interface MessageResponse {
  message: string;
}

/** A resource response with an extra message, e.g. after an upload or login. */
export interface ResourceWithMessage<T> extends Resource<T>, MessageResponse {}

/** Laravel's paginated resource collection. */
export interface Paginated<T> {
  data: T[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
  };
}

/** Query parameters every list endpoint accepts. */
export interface ListParams {
  page?: number;
  per_page?: number;
  search?: string;
}

/** A `{ id, name }` reference, used by breadcrumbs. */
export interface NamedRef {
  id: number;
  name: string;
}

/**
 * Every failed request is turned into this shape by the base query, so pages
 * handle errors one way.
 *
 * - `status` is the HTTP status, or `0` when the server could not be reached.
 * - `errors` holds the field errors of a 422 response.
 */
export interface ApiError {
  status: number;
  message: string;
  errors?: Record<string, string[]>;
}
