/** ClientResource / ClientListResource / ClientDetailsResource (same fields). */
export interface Client {
  id: number;
  name: string;
  phone_number: string;
  email: string | null;
  /** `YYYY-MM-DD` */
  last_follow_up_date: string | null;
}

/** ClientSummaryResource: the numbers on the client details cards. */
export interface ClientSummary {
  companies_count: number;
  projects_count: number;
  active_edits_count: number;
  unpaid_total: number;
}

/** StoreClientRequest */
export interface CreateClientPayload {
  name: string;
  phone_number: string;
  email?: string | null;
  last_follow_up_date?: string | null;
}

/** UpdateClientRequest: send only the fields that changed. */
export type UpdateClientPayload = Partial<CreateClientPayload>;
