import type { ListParams, NamedRef } from './api';
import type { Attachment } from './attachment';

/** Mirrors the backend EditStatus enum. */
export const EDIT_STATUSES = ['Pending', 'Confirmed', 'In Progress', 'Done', 'Cancelled'] as const;
export type EditStatus = (typeof EDIT_STATUSES)[number];

/** Mirrors EditStatus::active(): edits that still block deleting a client, company or project. */
export const ACTIVE_EDIT_STATUSES: readonly EditStatus[] = ['Pending', 'Confirmed', 'In Progress'];

/** Mirrors the backend EditPriority enum. */
export const EDIT_PRIORITIES = ['High', 'Medium', 'Low'] as const;
export type EditPriority = (typeof EDIT_PRIORITIES)[number];

/** EditResource */
export interface Edit {
  id: number;
  project_id: number;
  title: string;
  description: string | null;
  priority: EditPriority;
  status: EditStatus;
  price: number | null;
  is_paid: boolean;
  /** `YYYY-MM-DD` */
  due_date: string | null;
}

/** EditListResource */
export interface EditListItem extends Omit<Edit, 'description'> {
  project_name: string | null;
}

/** EditDetailsResource: includes the client -> company -> project breadcrumb. */
export interface EditDetails extends Edit {
  project?: NamedRef;
  company?: NamedRef | null;
  client?: NamedRef | null;
  attachments?: Attachment[];
}

/** ListEditsRequest */
export interface EditListParams extends ListParams {
  status?: EditStatus;
  priority?: EditPriority;
  is_paid?: boolean;
  project_id?: number;
  /** Only this client's edits. */
  client_id?: number;
  /** Only Pending, Confirmed and In Progress edits. */
  active?: boolean;
}

/** StoreEditRequest */
export interface CreateEditPayload {
  project_id: number;
  title: string;
  description?: string | null;
  priority: EditPriority;
  status: EditStatus;
  price?: number | null;
  is_paid?: boolean;
  due_date?: string | null;
}

/** UpdateEditRequest: send only the fields that changed. */
export type UpdateEditPayload = Partial<CreateEditPayload>;
