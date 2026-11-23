import type { MessageResponse, Resource } from "../types/api";
import type { AuthUser, ChangePasswordPayload, LoginPayload, UpdateProfilePayload } from "../types/auth";
import { baseApi } from "./baseApi";

/** Authentication feature: /api/auth/* */
export const authApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    /** The logged-in admin, or `null` when nobody is logged in (a normal 200 answer). */
    me: build.query<AuthUser | null, void>({
      query: () => "/auth/me",
      transformResponse: (response: Resource<AuthUser | null>) => response.data,
      providesTags: ["Auth"],
    }),

    // Both refetch "me": after login it returns the admin, after logout null
    login: build.mutation<MessageResponse, LoginPayload>({
      query: (body) => ({ url: "/auth/login", method: "POST", body }),
      invalidatesTags: ["Auth"],
    }),

    logout: build.mutation<MessageResponse, void>({
      query: () => ({ url: "/auth/logout", method: "POST" }),
      invalidatesTags: ["Auth"],
    }),

    updateProfile: build.mutation<AuthUser, UpdateProfilePayload>({
      query: (body) => ({ url: "/auth/profile", method: "PATCH", body }),
      transformResponse: (response: Resource<AuthUser>) => response.data,
      // Refetch "me" so the new name shows in the sidebar
      invalidatesTags: ["Auth"],
    }),

    /** Fails with 422 (`errors.current_password`) when the current password is wrong. */
    changePassword: build.mutation<MessageResponse, ChangePasswordPayload>({
      query: (body) => ({ url: "/auth/password", method: "PUT", body }),
    }),
  }),
});

export const {
  useMeQuery,
  useLoginMutation,
  useLogoutMutation,
  useUpdateProfileMutation,
  useChangePasswordMutation,
} = authApi;
