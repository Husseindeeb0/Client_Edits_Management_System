import type { MessageResponse, Resource } from "../types/api";
import type { AuthUser, LoginPayload } from "../types/auth";
import { baseApi } from "./baseApi";

/** Authentication feature: /api/auth/* */
export const authApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    /** The logged-in admin, or `null` when there is no session (401). */
    me: build.query<AuthUser | null, void>({
      async queryFn(_arg, _api, _extraOptions, baseQuery) {
        const result = await baseQuery("/auth/me");

        if (result.error) {
          return result.error.status === 401
            ? { data: null }
            : { error: result.error };
        }

        return { data: (result.data as Resource<AuthUser>).data };
      },
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
  }),
});

export const { useMeQuery, useLoginMutation, useLogoutMutation } = authApi;
