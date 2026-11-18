import type { MessageResponse, Paginated, Resource } from "../types/api";
import type {
  CreateEditPayload,
  Edit,
  EditDetails,
  EditListItem,
  EditListParams,
  UpdateEditPayload,
} from "../types/edit";
import { toQueryParams } from "../lib/queryParams";
import { baseApi, LIST_ID, listTags } from "./baseApi";

/** Edits feature: /api/edits/* */
export const editApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    /** `{ client_id, active: true }` gives the client details "Active edits" tab. */
    getPaginatedEdits: build.query<
      Paginated<EditListItem>,
      EditListParams | void
    >({
      query: (params) => ({
        url: "/edits",
        params: toQueryParams(params ?? {}),
      }),
      providesTags: (result) => listTags("Edit", result?.data),
    }),

    /** Includes the client -> company -> project breadcrumb and the attachments. */
    getEditDetails: build.query<EditDetails, number>({
      query: (id) => `/edits/${id}`,
      transformResponse: (response: Resource<EditDetails>) => response.data,
      providesTags: (_result, _error, id) => [{ type: "Edit", id }],
    }),

    createEdit: build.mutation<Edit, CreateEditPayload>({
      query: (body) => ({ url: "/edits", method: "POST", body }),
      transformResponse: (response: Resource<Edit>) => response.data,
      invalidatesTags: [{ type: "Edit", id: LIST_ID }, "ClientSummary"],
    }),

    updateEdit: build.mutation<
      Edit,
      { id: number; changes: UpdateEditPayload }
    >({
      query: ({ id, changes }) => ({
        url: `/edits/${id}`,
        method: "PATCH",
        body: changes,
      }),
      transformResponse: (response: Resource<Edit>) => response.data,
      // Status, price and paid change the summary; the title is shown in the attachments list
      invalidatesTags: (_result, _error, { id }) => [
        { type: "Edit", id },
        { type: "Edit", id: LIST_ID },
        "ClientSummary",
        { type: "Attachment", id: LIST_ID },
      ],
    }),

    deleteEdit: build.mutation<MessageResponse, number>({
      query: (id) => ({ url: `/edits/${id}`, method: "DELETE" }),
      // Deleting an edit also deletes its attachments
      invalidatesTags: [
        { type: "Edit", id: LIST_ID },
        "ClientSummary",
        "Attachment",
      ],
    }),
  }),
});

export const {
  useGetPaginatedEditsQuery,
  useGetEditDetailsQuery,
  useCreateEditMutation,
  useUpdateEditMutation,
  useDeleteEditMutation,
} = editApi;
