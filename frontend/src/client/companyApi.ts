import type { MessageResponse, Paginated, Resource } from "../types/api";
import type {
  Company,
  CompanyDetails,
  CompanyListItem,
  CompanyListParams,
  CreateCompanyPayload,
  UpdateCompanyPayload,
} from "../types/company";
import { toQueryParams } from "../lib/queryParams";
import { baseApi, LIST_ID, listTags } from "./baseApi";

/** Companies feature: /api/companies/* */
export const companyApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    /** Pass `client_id` for the client details "Companies" tab. */
    getPaginatedCompanies: build.query<
      Paginated<CompanyListItem>,
      CompanyListParams | void
    >({
      query: (params) => ({
        url: "/companies",
        params: toQueryParams(params ?? {}),
      }),
      providesTags: (result) => listTags("Company", result?.data),
    }),

    getCompanyDetails: build.query<CompanyDetails, number>({
      query: (id) => `/companies/${id}`,
      transformResponse: (response: Resource<CompanyDetails>) => response.data,
      providesTags: (_result, _error, id) => [{ type: "Company", id }],
    }),

    createCompany: build.mutation<Company, CreateCompanyPayload>({
      query: (body) => ({ url: "/companies", method: "POST", body }),
      transformResponse: (response: Resource<Company>) => response.data,
      invalidatesTags: (_result, _error, { client_id }) => [
        { type: "Company", id: LIST_ID },
        { type: "ClientSummary", id: client_id },
      ],
    }),

    updateCompany: build.mutation<
      Company,
      { id: number; changes: UpdateCompanyPayload }
    >({
      query: ({ id, changes }) => ({
        url: `/companies/${id}`,
        method: "PATCH",
        body: changes,
      }),
      transformResponse: (response: Resource<Company>) => response.data,
      // Moving a company to another client changes both clients' summaries;
      // the company name is also shown on project and edit pages
      invalidatesTags: (_result, _error, { id }) => [
        { type: "Company", id },
        { type: "Company", id: LIST_ID },
        "ClientSummary",
        "Project",
        "Edit",
      ],
    }),

    /** Fails with 422 (`errors.company`) while the company still has active edits. */
    deleteCompany: build.mutation<MessageResponse, number>({
      query: (id) => ({ url: `/companies/${id}`, method: "DELETE" }),
      // Deleting a company also deletes its projects, edits and attachments
      invalidatesTags: [
        { type: "Company", id: LIST_ID },
        "ClientSummary",
        "Project",
        "Edit",
        "Attachment",
      ],
    }),
  }),
});

export const {
  useGetPaginatedCompaniesQuery,
  useGetCompanyDetailsQuery,
  useCreateCompanyMutation,
  useUpdateCompanyMutation,
  useDeleteCompanyMutation,
} = companyApi;
