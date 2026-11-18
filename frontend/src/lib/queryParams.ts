/** Drop empty filters so they are not sent as `?search=` or `?status=null`. */
export function toQueryParams(params: object = {}): Record<string, string | number | boolean> {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => value !== undefined && value !== null && value !== ''),
  );
}
