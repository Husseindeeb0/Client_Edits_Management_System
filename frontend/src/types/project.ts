import type { NamedRef } from './api';

/** ProjectResource */
export interface Project {
  id: number;
  name: string;
  company_id: number;
}

/** ProjectListResource: GET /projects */
export interface ProjectListItem extends Project {
  company_name: string | null;
  client_id: number | null;
  client_name: string | null;
}

/** ClientProjectResource: GET /projects/client/{client} */
export interface ClientProject extends Project {
  company_name: string | null;
}

/** CompanyProjectResource: GET /projects/company/{company} */
export interface CompanyProject {
  id: number;
  name: string;
}

/** ProjectDetailsResource: includes the client -> company breadcrumb. */
export interface ProjectDetails extends Project {
  company?: NamedRef;
  client?: NamedRef;
}

/** StoreProjectRequest */
export interface CreateProjectPayload {
  name: string;
  company_id: number;
}

/** UpdateProjectRequest: send only the fields that changed. */
export type UpdateProjectPayload = Partial<CreateProjectPayload>;
