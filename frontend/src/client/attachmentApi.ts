import type {
  ListParams,
  MessageResponse,
  Paginated,
  Resource,
  ResourceWithMessage,
} from "../types/api";
import type {
  Attachment,
  AttachmentListItem,
  UploadAttachmentsPayload,
} from "../types/attachment";
import { API_BASE_URL } from "../lib/baseQuery";
import { toQueryParams } from "../lib/queryParams";
import { baseApi, LIST_ID, listTags } from "./baseApi";

/** Attachments feature: /api/attachments/* and /api/edits/{edit}/attachments */
export const attachmentApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    /** Global attachments page, searchable by file name. */
    getPaginatedAttachments: build.query<
      Paginated<AttachmentListItem>,
      ListParams | void
    >({
      query: (params) => ({
        url: "/attachments",
        params: toQueryParams(params ?? {}),
      }),
      providesTags: (result) => listTags("Attachment", result?.data),
    }),

    getAttachmentsByEdit: build.query<Attachment[], number>({
      query: (editId) => `/edits/${editId}/attachments`,
      transformResponse: (response: Resource<Attachment[]>) => response.data,
      providesTags: (result) => listTags("Attachment", result),
    }),

    uploadAttachments: build.mutation<Attachment[], UploadAttachmentsPayload>({
      query: ({ editId, files, description }) => {
        const body = new FormData();
        files.forEach((file) => body.append("files[]", file));
        if (description) {
          body.append("description", description);
        }

        // No Content-Type header: the browser sets the multipart boundary itself
        return { url: `/edits/${editId}/attachments`, method: "POST", body };
      },
      transformResponse: (response: ResourceWithMessage<Attachment[]>) =>
        response.data,
      // The edit details response embeds its attachments
      invalidatesTags: (_result, _error, { editId }) => [
        { type: "Attachment", id: LIST_ID },
        { type: "Edit", id: editId },
      ],
    }),

    /** `editId` is only used to refresh that edit's details afterwards. */
    deleteAttachment: build.mutation<
      MessageResponse,
      { id: number; editId: number }
    >({
      query: ({ id }) => ({ url: `/attachments/${id}`, method: "DELETE" }),
      invalidatesTags: (_result, _error, { editId }) => [
        { type: "Attachment", id: LIST_ID },
        { type: "Edit", id: editId },
      ],
    }),
  }),
});

/**
 * Downloads go through a normal link (`<a href={...}>`) instead of RTK Query,
 * so the browser streams the file. The session cookie is sent automatically.
 */
export function getAttachmentDownloadUrl(id: number): string {
  return `${API_BASE_URL}/attachments/${id}/download`;
}

export const {
  useGetPaginatedAttachmentsQuery,
  useGetAttachmentsByEditQuery,
  useUploadAttachmentsMutation,
  useDeleteAttachmentMutation,
} = attachmentApi;
