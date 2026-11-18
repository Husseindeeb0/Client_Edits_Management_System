import type {
  ListParams,
  MessageResponse,
  Paginated,
  Resource,
} from "../types/api";
import type { EditListItem, EditListParams } from "../types/edit";
import type {
  ClientProject,
  CompanyProject,
  CreateProjectPayload,
  Project,
  ProjectDetails,
  ProjectListItem,
  UpdateProjectPayload,
} from "../types/project";
import { toQueryParams } from "../lib/queryParams";
import { baseApi, LIST_ID, listTags } from "./baseApi";

/** Projects feature: /api/projects/* */
export const projectApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    getPaginatedProjects: build.query<
      Paginated<ProjectListItem>,
      ListParams | void
    >({
      query: (params) => ({
        url: "/projects",
        params: toQueryParams(params ?? {}),
      }),
      providesTags: (result) => listTags("Project", result?.data),
    }),

    /** Client details "Projects" tab. */
    getClientProjects: build.query<
      Paginated<ClientProject>,
      ListParams & { clientId: number }
    >({
      query: ({ clientId, ...params }) => ({
        url: `/projects/client/${clientId}`,
        params: toQueryParams(params),
      }),
      providesTags: (result) => listTags("Project", result?.data),
    }),

    /** Company details projects list. */
    getCompanyProjects: build.query<
      Paginated<CompanyProject>,
      ListParams & { companyId: number }
    >({
      query: ({ companyId, ...params }) => ({
        url: `/projects/company/${companyId}`,
        params: toQueryParams(params),
      }),
      providesTags: (result) => listTags("Project", result?.data),
    }),

    getProjectDetails: build.query<ProjectDetails, number>({
      query: (id) => `/projects/${id}`,
      transformResponse: (response: Resource<ProjectDetails>) => response.data,
      providesTags: (_result, _error, id) => [{ type: "Project", id }],
    }),

    /** Project details edits list. The route's project always wins over a `project_id` filter. */
    getProjectEdits: build.query<
      Paginated<EditListItem>,
      Omit<EditListParams, "project_id" | "client_id"> & { projectId: number }
    >({
      query: ({ projectId, ...params }) => ({
        url: `/projects/${projectId}/edits`,
        params: toQueryParams(params),
      }),
      providesTags: (result) => listTags("Edit", result?.data),
    }),

    createProject: build.mutation<Project, CreateProjectPayload>({
      query: (body) => ({ url: "/projects", method: "POST", body }),
      transformResponse: (response: Resource<Project>) => response.data,
      invalidatesTags: [{ type: "Project", id: LIST_ID }, "ClientSummary"],
    }),

    updateProject: build.mutation<
      Project,
      { id: number; changes: UpdateProjectPayload }
    >({
      query: ({ id, changes }) => ({
        url: `/projects/${id}`,
        method: "PATCH",
        body: changes,
      }),
      transformResponse: (response: Resource<Project>) => response.data,
      // Moving a project to another company can change client summaries;
      // the project name is also shown in edit lists
      invalidatesTags: (_result, _error, { id }) => [
        { type: "Project", id },
        { type: "Project", id: LIST_ID },
        "ClientSummary",
        "Edit",
      ],
    }),

    /** Fails with 422 (`errors.project`) while the project still has active edits. */
    deleteProject: build.mutation<MessageResponse, number>({
      query: (id) => ({ url: `/projects/${id}`, method: "DELETE" }),
      // Deleting a project also deletes its edits and attachments
      invalidatesTags: [
        { type: "Project", id: LIST_ID },
        "ClientSummary",
        "Edit",
        "Attachment",
      ],
    }),
  }),
});

export const {
  useGetPaginatedProjectsQuery,
  useGetClientProjectsQuery,
  useGetCompanyProjectsQuery,
  useGetProjectDetailsQuery,
  useGetProjectEditsQuery,
  useCreateProjectMutation,
  useUpdateProjectMutation,
  useDeleteProjectMutation,
} = projectApi;
