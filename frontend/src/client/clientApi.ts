import type {
  ListParams,
  MessageResponse,
  Paginated,
  Resource,
} from "../types/api";
import type {
  Client,
  ClientSummary,
  CreateClientPayload,
  UpdateClientPayload,
} from "../types/client";
import { toQueryParams } from "../lib/queryParams";
import { baseApi, LIST_ID, listTags, nextPage } from "./baseApi";

/** Clients feature: /api/clients/* */
export const clientApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    getPaginatedClients: build.query<Paginated<Client>, ListParams | void>({
      query: (params) => ({
        url: "/clients",
        params: toQueryParams(params ?? {}),
      }),
      providesTags: (result) => listTags("Client", result?.data),
    }),

    /** The same list, loaded page after page as it is scrolled (ClientSelect). The argument is the search term. */
    getInfiniteClients: build.infiniteQuery<Paginated<Client>, string, number>({
      infiniteQueryOptions: { initialPageParam: 1, getNextPageParam: nextPage },
      query: ({ queryArg, pageParam }) => ({
        url: "/clients",
        params: toQueryParams({ search: queryArg, page: pageParam }),
      }),
      providesTags: (result) => listTags("Client", result?.pages.flatMap((page) => page.data)),
    }),

    getClientDetails: build.query<Client, number>({
      query: (id) => `/clients/${id}`,
      transformResponse: (response: Resource<Client>) => response.data,
      providesTags: (_result, _error, id) => [{ type: "Client", id }],
    }),

    /** Numbers for the client details summary cards. */
    getClientSummary: build.query<ClientSummary, number>({
      query: (id) => `/clients/${id}/summary`,
      transformResponse: (response: Resource<ClientSummary>) => response.data,
      providesTags: (_result, _error, id) => [{ type: "ClientSummary", id }],
    }),

    createClient: build.mutation<Client, CreateClientPayload>({
      query: (body) => ({ url: "/clients", method: "POST", body }),
      transformResponse: (response: Resource<Client>) => response.data,
      invalidatesTags: [{ type: "Client", id: LIST_ID }],
    }),

    updateClient: build.mutation<
      Client,
      { id: number; changes: UpdateClientPayload }
    >({
      query: ({ id, changes }) => ({
        url: `/clients/${id}`,
        method: "PATCH",
        body: changes,
      }),
      transformResponse: (response: Resource<Client>) => response.data,
      // The client name is also shown on company, project and edit pages
      invalidatesTags: (_result, _error, { id }) => [
        { type: "Client", id },
        { type: "Client", id: LIST_ID },
        "Company",
        "Project",
        "Edit",
      ],
    }),

    /** Fails with 422 (`errors.client`) while the client still has active edits. */
    deleteClient: build.mutation<MessageResponse, number>({
      query: (id) => ({ url: `/clients/${id}`, method: "DELETE" }),
      // Deleting a client also deletes its companies, projects, edits and attachments
      invalidatesTags: [
        { type: "Client", id: LIST_ID },
        "ClientSummary",
        "Company",
        "Project",
        "Edit",
        "Attachment",
      ],
    }),
  }),
});

export const {
  useGetPaginatedClientsQuery,
  useGetInfiniteClientsInfiniteQuery,
  useGetClientDetailsQuery,
  useGetClientSummaryQuery,
  useCreateClientMutation,
  useUpdateClientMutation,
  useDeleteClientMutation,
} = clientApi;
