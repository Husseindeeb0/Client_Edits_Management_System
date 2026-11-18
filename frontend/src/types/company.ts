import type { ListParams } from './api';
import type { Client } from './client';

/** CompanyResource */
export interface Company {
  id: number;
  name: string;
  client_id: number;
}

/** CompanyListResource */
export interface CompanyListItem extends Company {
  client_name: string | null;
}

/** CompanyDetailsResource */
export interface CompanyDetails extends Company {
  client?: Client;
}

/** ListCompaniesRequest */
export interface CompanyListParams extends ListParams {
  /** Only this client's companies (client details "Companies" tab). */
  client_id?: number;
}

/** StoreCompanyRequest */
export interface CreateCompanyPayload {
  name: string;
  client_id: number;
}

/** UpdateCompanyRequest: send only the fields that changed. */
export type UpdateCompanyPayload = Partial<CreateCompanyPayload>;
